<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wpdb extends DataSource {
	/**
	 * @var wpdb|null
	 */
	protected $wpdb;

	public function __construct( $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function resolve( Request $request ) {
		$queries = $this->collect_queries();

		if ( count( $queries ) > 0 ) {
			$request->databaseQueries = $this->collect_queries();
		}

		return $request;
	}

	protected function capitalize_keywords( $query ) {
		// Adapted from Clockwork\EloquentDataSource::createRunnableQuery().
		$keywords = [
			'select',
			'insert',
			'update',
			'delete',
			'where',
			'from',
			'limit',
			'is',
			'null',
			'having',
			'group by',
			'order by',
			'asc',
			'desc',
		];

		return preg_replace_callback(
			'/\b' . implode( '\b|\b', $keywords ) . '\b/i',
			function ( $match ) {
				return strtoupper( $match[0] );
			},
			$query
		);
	}

	protected function collect_queries() {
		if (
			! is_object( $this->wpdb )
			|| ! property_exists( $this->wpdb, 'queries' )
			|| empty( $this->wpdb->queries )
		) {
			return [];
		}

		$queries = array_map(
			/**
			 * @psalm-param array{0: string, 1: float, 2: string}  $query
			 * @psalm-return array{query: string, duration: float}|false
			 */
			function( $query ) {
				if ( ! is_array( $query ) ) {
					return false;
				}

				$q = isset( $query[0] ) ? $query[0] : '';
				$d = isset( $query[1] ) ? $query[1] : 0;

				return [
					'query' => $this->capitalize_keywords( $q ),
					'duration' => $d * 1000,
					'model' => $this->guess_model( $q ),
					// @todo Probably not able to record this data without configuring a db dropin.
					// 'file' => '',
					// 'line' => '',
				];
			},
			$this->wpdb->queries
		);

		return array_filter( $queries );
	}

	protected function guess_model( $query ) {
		// This is really rough... Also - is it even necessary to include this?
		// @todo Fails on inserts and updates - also with table names in backticks.
		if ( 1 === preg_match( '/from\s+([^\s]+)/i', $query, $matches ) ) {
			$table = $matches[1];

			// @todo Should we include "old tables"?
			foreach ( [
				'/blog(?:_version)?s$/' => 'BLOG',
				'/comment(?:s|meta)$/' => 'COMMENT',
				'/links$/' => 'LINK',
				'/options$/' => 'OPTION',
				'/post(?:s|meta)$/' => 'POST',
				'/registration_log$/' => 'REGISTRATION',
				'/signups$/' => 'SIGNUP',
				'/site(?:categories|meta)?$/' => 'SITE',
				'/term(?:s|_relationships|_taxonomy|meta)$/' => 'TERM',
				'/user(?:s|meta)$/' => 'USER',
			] as $pattern => $model ) {
				if ( 1 === preg_match( $pattern, $table ) ) {
					return $model;
				}
			}
		}

		return '(unkown)';
	}
}

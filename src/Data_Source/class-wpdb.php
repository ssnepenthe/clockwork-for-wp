<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;

use function Clockwork_For_Wp\prepare_wpdb_query;

class Wpdb extends DataSource implements Subscriber {
	protected $queries = [];

	public function get_subscribed_events() : array {
		return [
			'cfw_pre_resolve' => function( \wpdb $wpdb ) {
				if ( ! is_array( $wpdb->queries ) || count( $wpdb->queries ) < 1 ) {
					return;
				}

				foreach ( $wpdb->queries as $query_array ) {
					$query = prepare_wpdb_query( $query_array );

					$this->add_query( $query[0], $query[1] );
				}
			},
		];
	}

	public function resolve( Request $request ) {
		foreach ( $this->queries as $query ) {
			// @todo
			$request->addDatabaseQuery( $query['query'], [], $query['duration'], [
				'model' => $query['model'],
			] );
		}

		return $request;
	}

	public function set_queries( array $queries ) {
		$this->queries = [];

		foreach ( $queries as $query_array ) {
			$this->add_query( ...$query_array );
		}

		return $this;
	}

	public function add_query( $query, $duration ) {
		$this->queries[] = [
			'query' => $this->capitalize_keywords( $query ),
			'duration' => $duration,
			'model' => $this->guess_model( $query ),
		];

		return $this;
	}

	protected function guess_model( $query ) {
		// @todo Allow registration of custom pattern/model pairs
		$pattern = '/(?:from|into|update)\s+(`)?(?<table>[^\s`]+)(?(1)`)/i';

		if ( 1 === preg_match( $pattern, $query, $matches ) ) {
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
				if ( 1 === preg_match( $pattern, $matches['table'] ) ) {
					return $model;
				}
			}
		}

		return '(unknown)';
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
}

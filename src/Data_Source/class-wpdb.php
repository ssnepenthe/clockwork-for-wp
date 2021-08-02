<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Log;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Subscriber;

use function Clockwork_For_Wp\prepare_wpdb_query;

class Wpdb extends DataSource implements Subscriber {
	protected $duplicates = [];
	protected $queries = [];

	protected $detect_duplicate_queries;
	protected $slow_threshold;

	public function __construct(
		bool $detect_duplicate_queries,
		bool $slow_only,
		float $slow_threshold
	) {
		$this->detect_duplicate_queries = $detect_duplicate_queries;
		$this->slow_threshold = $slow_threshold;

		if ( $slow_only ) {
			$this->addFilter( function( $duration ) {
				return $duration > $this->slow_threshold;
			} );
		}
	}

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
		if ( $this->detect_duplicate_queries ) {
			$this->append_duplicate_queries_warnings( $request );
		}

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
		if ( $this->detect_duplicate_queries ) {
			$normalized = $this->normalize_query( $query );

			if ( ! isset( $this->duplicates[ $normalized ] ) ) {
				$this->duplicates[ $normalized ] = 0;
			}

			$this->duplicates[ $normalized ]++;
		}

		if ( $this->passesFilters( [ $duration ] ) ) {
			$this->queries[] = [
				'query' => $this->capitalize_keywords( $query ),
				'duration' => $duration,
				'model' => $this->guess_model( $query ),
			];
		}

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

	protected function normalize_query( $query ) {
		// Yoinked from query monitor.

		// newline to space.
		$query = str_replace( [ "\r\n", "\r", "\n" ], ' ', $query );

		// remove tab and backtick.
		$query = str_replace( [ "\t", '`' ], '', $query );

		// collapse whitespace.
		$query = preg_replace( '/[[:space:]]+/', ' ', $query );

		// trim.
		$query = trim( $query );

		// remove trailing semicolon.
		$query = rtrim( $query, ';' );

		return $query;
	}

	protected function append_duplicate_queries_warnings( $request ) {
		$log = new Log;

		foreach ( $this->duplicates as $sql => $count ) {
			if ( $count <= 1 ) {
				continue;
			}

			$log->warning( "Duplicate query: \"{$sql}\" run {$count} times" );
		}

		$request->log()->merge( $log );
	}
}

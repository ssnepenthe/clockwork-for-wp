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

	protected $custom_model_identifiers = [];
	protected $detect_duplicate_queries;
	protected $pattern_model_map;
	protected $slow_threshold;

	public function __construct(
		bool $detect_duplicate_queries,
		bool $slow_only,
		float $slow_threshold,
		array $pattern_model_map
	) {
		$this->detect_duplicate_queries = $detect_duplicate_queries;
		$this->slow_threshold = $slow_threshold;
		$this->pattern_model_map = $pattern_model_map;

		if ( $slow_only ) {
			$this->addFilter( function( $duration ) {
				// @todo Should this be inclusive (i.e. >=) instead?
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

					$this->add_query( $query[0], $query[1], $query[2] );
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
				'time' => $query['start'],
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

	public function add_query( $query, $duration, $start ) {
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
				'model' => $this->identify_model( $query ),
				'start' => $start,
			];
		}

		return $this;
	}

	public function add_custom_model_identifier( callable $identifier ) {
		$this->custom_model_identifiers[] = $identifier;
	}

	protected function identify_model( $query ) {
		$model = $this->call_custom_model_identifiers( $query );

		if ( is_string( $model ) ) {
			return $model;
		}

		$pattern = '/(?:from|into|update)\s+(`)?(?<table>[^\s`]+)(?(1)`)/i';

		if ( 1 === preg_match( $pattern, $query, $matches ) ) {
			foreach ( $this->pattern_model_map as $pattern => $model ) {
				if ( 1 === preg_match( $pattern, $matches['table'] ) ) {
					return $model;
				}
			}
		}

		return '(unknown)';
	}

	protected function call_custom_model_identifiers( $query ) {
		foreach ( $this->custom_model_identifiers as $identifier ) {
			$model = $identifier( $query );

			if ( is_string( $model ) ) {
				return $model;
			}
		}

		return null;
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

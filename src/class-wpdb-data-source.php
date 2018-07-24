<?php

namespace Clockwork_For_Wp;

use wpdb;
use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wpdb_Data_Source extends DataSource {
	/**
	 * @var wpdb
	 */
	protected $wpdb;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb = $wpdb;
	}

	public function resolve( Request $request ) {
		$request->databaseQueries = $this->collect_queries();

		return $request;
	}

	/**
	 * @return array<int, array>
	 */
	protected function collect_queries() {
		return array_map(
			/**
			 * @psalm-param array{0: string, 1: float, 2: string}  $query
			 * @psalm-return array{query: string, duration: float}
			 */
			function( $query ) {
				list( $query, $duration, $caller ) = $query;

				return [
					// @todo Consider highlighting keywords in query.
					'query' => $query,
					// @todo Verify multiplier - wpdb uses microtime which returns seconds - we want milliseconds so this should be correct.
					'duration' => $duration * 1000,
					// @todo Consider populating this value based on queried table?
					// 'model' => '',
					// @todo Probably not able to record this data without configuring a db dropin.
					// 'file' => '',
					// 'line' => '',
				];
			},
			$this->wpdb->queries
		);
	}
}

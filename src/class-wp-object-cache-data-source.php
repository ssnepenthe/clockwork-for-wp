<?php

namespace Clockwork_For_Wp;

use WP_Object_Cache;
use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Wp_Object_Cache_Data_Source extends DataSource {
	/**
	 * @var WP_Object_Cache
	 */
	protected $cache;

	public function __construct( WP_Object_Cache $cache ) {
		$this->cache = $cache;
	}

	public function resolve( Request $request ) {
		$request->cacheHits = $this->collect_hits();
		$request->cacheReads = $this->collect_reads();

		return $request;
	}

	/**
	 * @return integer
	 */
	protected function collect_reads() {
		return $this->cache->cache_hits + $this->cache->cache_misses;
	}

	/**
	 * @return integer
	 */
	protected function collect_hits() {
		return $this->cache->cache_hits;
	}
}

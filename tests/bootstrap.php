<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/plugin-helpers.php';
require_once __DIR__ . '/../src/wordpress-helpers.php';
require_once __DIR__ . '/helpers.php';

class Null_Storage_For_Tests extends \Clockwork\Storage\Storage {
	public function all( \Clockwork\Storage\Search $search = null ) {}
	public function find( $id ) {}
	public function latest( \Clockwork\Storage\Search $search = null ) {}
	public function previous( $id, $count = null, \Clockwork\Storage\Search $search = null ) {}
	public function next( $id, $count = null, \Clockwork\Storage\Search $search = null ) {}
	public function store( \Clockwork\Request\Request $request ) {}
	public function cleanup() {}
}

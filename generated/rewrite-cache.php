<?php

declare(strict_types=1);

use SimpleWpRouting\Dumper\OptimizedRewrite;
use SimpleWpRouting\Dumper\OptimizedRewriteCollection;

if ( ! \class_exists('CachedRewriteCollection69ca524bca42f25176307b5468dca9d806d2b913cebffea2379f7c2a7c74d09b')) {
	class CachedRewriteCollection69ca524bca42f25176307b5468dca9d806d2b913cebffea2379f7c2a7c74d09b extends OptimizedRewriteCollection {
		public function __construct() {
			$this->queryVariables = [
				'cfw_id' => 'id',
				'cfw_direction' => 'direction',
				'cfw_count' => 'count',
				'cfw_path' => 'path',
			];
			$this->rewriteRules = [
				'^__clockwork/auth$' => 'index.php?cfw___routeType=static',
				'^__clockwork/([0-9-]+|latest)/extended$' => 'index.php?cfw_id=$matches[1]&cfw___routeType=variable',
				'^__clockwork/([0-9-]+|latest)$' => 'index.php?cfw_id=$matches[1]&cfw___routeType=variable',
				'^__clockwork/([0-9-]+|latest)/(next|previous)$' => 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw___routeType=variable',
				'^__clockwork/([0-9-]+|latest)/(next|previous)/(\\d+)$' => 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_count=$matches[3]&cfw___routeType=variable',
				'^__clockwork$' => 'index.php?cfw___routeType=static',
				'^__clockwork/app$' => 'index.php?cfw___routeType=static',
				'^__clockwork/(.+)$' => 'index.php?cfw_path=$matches[1]&cfw___routeType=variable',
			];

			$rewrite0 = new OptimizedRewrite( [
				0 => 'POST',
			], '^__clockwork/auth$', 'index.php?cfw___routeType=static', [
			], [
				0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
				1 => 'authenticate',
			], null);
			$rewrite1 = new OptimizedRewrite( [
				0 => 'GET',
				1 => 'HEAD',
			], '^__clockwork/([0-9-]+|latest)/extended$', 'index.php?cfw_id=$matches[1]&cfw___routeType=variable', [
				'cfw_id' => 'id',
			], [
				0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
				1 => 'serve_extended_json',
			], null);
			$rewrite2 = new OptimizedRewrite( [
				0 => 'GET',
				1 => 'HEAD',
			], '^__clockwork/([0-9-]+|latest)$', 'index.php?cfw_id=$matches[1]&cfw___routeType=variable', [
				'cfw_id' => 'id',
			], [
				0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
				1 => 'serve_json',
			], null);
			$rewrite3 = new OptimizedRewrite( [
				0 => 'GET',
				1 => 'HEAD',
			], '^__clockwork/([0-9-]+|latest)/(next|previous)$', 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw___routeType=variable', [
				'cfw_id' => 'id',
				'cfw_direction' => 'direction',
			], [
				0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
				1 => 'serve_json',
			], null);
			$rewrite4 = new OptimizedRewrite( [
				0 => 'GET',
				1 => 'HEAD',
			], '^__clockwork/([0-9-]+|latest)/(next|previous)/(\\d+)$', 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_count=$matches[3]&cfw___routeType=variable', [
				'cfw_id' => 'id',
				'cfw_direction' => 'direction',
				'cfw_count' => 'count',
			], [
				0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
				1 => 'serve_json',
			], null);
			$rewrite5 = new OptimizedRewrite( [
				0 => 'PUT',
			], '^__clockwork/([0-9-]+|latest)$', 'index.php?cfw_id=$matches[1]&cfw___routeType=variable', [
				'cfw_id' => 'id',
			], [
				0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
				1 => 'update_data',
			], [
				0 => 'Clockwork_For_Wp\\Is',
				1 => 'collecting_client_metrics',
			]);
			$rewrite6 = new OptimizedRewrite( [
				0 => 'GET',
				1 => 'HEAD',
			], '^__clockwork$', 'index.php?cfw___routeType=static', [
			], [
				0 => 'Clockwork_For_Wp\\Web_App\\Web_App_Controller',
				1 => 'redirect',
			], null);
			$rewrite7 = new OptimizedRewrite( [
				0 => 'GET',
				1 => 'HEAD',
			], '^__clockwork/app$', 'index.php?cfw___routeType=static', [
			], [
				0 => 'Clockwork_For_Wp\\Web_App\\Web_App_Controller',
				1 => 'serve_assets',
			], null);
			$rewrite8 = new OptimizedRewrite( [
				0 => 'GET',
				1 => 'HEAD',
			], '^__clockwork/(.+)$', 'index.php?cfw_path=$matches[1]&cfw___routeType=variable', [
				'cfw_path' => 'path',
			], [
				0 => 'Clockwork_For_Wp\\Web_App\\Web_App_Controller',
				1 => 'serve_assets',
			], null);
			$this->rewritesByRegexAndMethod = [
				'^__clockwork/auth$' => [
					'POST' => $rewrite0,
				],
				'^__clockwork/([0-9-]+|latest)/extended$' => [
					'GET' => $rewrite1,
					'HEAD' => $rewrite1,
				],
				'^__clockwork/([0-9-]+|latest)$' => [
					'GET' => $rewrite2,
					'HEAD' => $rewrite2,
					'PUT' => $rewrite5,
				],
				'^__clockwork/([0-9-]+|latest)/(next|previous)$' => [
					'GET' => $rewrite3,
					'HEAD' => $rewrite3,
				],
				'^__clockwork/([0-9-]+|latest)/(next|previous)/(\\d+)$' => [
					'GET' => $rewrite4,
					'HEAD' => $rewrite4,
				],
				'^__clockwork$' => [
					'GET' => $rewrite6,
					'HEAD' => $rewrite6,
				],
				'^__clockwork/app$' => [
					'GET' => $rewrite7,
					'HEAD' => $rewrite7,
				],
				'^__clockwork/(.+)$' => [
					'GET' => $rewrite8,
					'HEAD' => $rewrite8,
				],
			];
		}
	}
}

return new CachedRewriteCollection69ca524bca42f25176307b5468dca9d806d2b913cebffea2379f7c2a7c74d09b();

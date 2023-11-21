<?php

declare(strict_types=1);

use SimpleWpRouting\Dumper\OptimizedRewrite;
use SimpleWpRouting\Dumper\OptimizedRewriteCollection;

if (! class_exists('CachedRewriteCollection69ca524bca42f25176307b5468dca9d806d2b913cebffea2379f7c2a7c74d09b')) {
    class CachedRewriteCollection69ca524bca42f25176307b5468dca9d806d2b913cebffea2379f7c2a7c74d09b extends OptimizedRewriteCollection
    {
        public function __construct()
        {
            $this->queryVariables = array (
  'cfw_id' => 'id',
  'cfw_direction' => 'direction',
  'cfw_count' => 'count',
  'cfw_path' => 'path',
);
            $this->rewriteRules = array (
  '^__clockwork/auth$' => 'index.php?cfw___routeType=static',
  '^__clockwork/([0-9-]+|latest)/extended$' => 'index.php?cfw_id=$matches[1]&cfw___routeType=variable',
  '^__clockwork/([0-9-]+|latest)$' => 'index.php?cfw_id=$matches[1]&cfw___routeType=variable',
  '^__clockwork/([0-9-]+|latest)/(next|previous)$' => 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw___routeType=variable',
  '^__clockwork/([0-9-]+|latest)/(next|previous)/(\\d+)$' => 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_count=$matches[3]&cfw___routeType=variable',
  '^__clockwork$' => 'index.php?cfw___routeType=static',
  '^__clockwork/app$' => 'index.php?cfw___routeType=static',
  '^__clockwork/(.+)$' => 'index.php?cfw_path=$matches[1]&cfw___routeType=variable',
);

            $rewrite0 = new OptimizedRewrite(array (
  0 => 'POST',
), '^__clockwork/auth$', 'index.php?cfw___routeType=static', array (
), array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'authenticate',
), NULL);
$rewrite1 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^__clockwork/([0-9-]+|latest)/extended$', 'index.php?cfw_id=$matches[1]&cfw___routeType=variable', array (
  'cfw_id' => 'id',
), array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'serve_extended_json',
), NULL);
$rewrite2 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^__clockwork/([0-9-]+|latest)$', 'index.php?cfw_id=$matches[1]&cfw___routeType=variable', array (
  'cfw_id' => 'id',
), array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'serve_json',
), NULL);
$rewrite3 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^__clockwork/([0-9-]+|latest)/(next|previous)$', 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw___routeType=variable', array (
  'cfw_id' => 'id',
  'cfw_direction' => 'direction',
), array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'serve_json',
), NULL);
$rewrite4 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^__clockwork/([0-9-]+|latest)/(next|previous)/(\\d+)$', 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_count=$matches[3]&cfw___routeType=variable', array (
  'cfw_id' => 'id',
  'cfw_direction' => 'direction',
  'cfw_count' => 'count',
), array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'serve_json',
), NULL);
$rewrite5 = new OptimizedRewrite(array (
  0 => 'PUT',
), '^__clockwork/([0-9-]+|latest)$', 'index.php?cfw_id=$matches[1]&cfw___routeType=variable', array (
  'cfw_id' => 'id',
), array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'update_data',
), array (
  0 => 'Clockwork_For_Wp\\Is',
  1 => 'collecting_client_metrics',
));
$rewrite6 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^__clockwork$', 'index.php?cfw___routeType=static', array (
), array (
  0 => 'Clockwork_For_Wp\\Web_App\\Web_App_Controller',
  1 => 'redirect',
), NULL);
$rewrite7 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^__clockwork/app$', 'index.php?cfw___routeType=static', array (
), array (
  0 => 'Clockwork_For_Wp\\Web_App\\Web_App_Controller',
  1 => 'serve_assets',
), NULL);
$rewrite8 = new OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), '^__clockwork/(.+)$', 'index.php?cfw_path=$matches[1]&cfw___routeType=variable', array (
  'cfw_path' => 'path',
), array (
  0 => 'Clockwork_For_Wp\\Web_App\\Web_App_Controller',
  1 => 'serve_assets',
), NULL);
$this->rewritesByRegexAndMethod = array (
  '^__clockwork/auth$' => 
  array (
    'POST' => $rewrite0,
  ),
  '^__clockwork/([0-9-]+|latest)/extended$' => 
  array (
    'GET' => $rewrite1,
    'HEAD' => $rewrite1,
  ),
  '^__clockwork/([0-9-]+|latest)$' => 
  array (
    'GET' => $rewrite2,
    'HEAD' => $rewrite2,
    'PUT' => $rewrite5,
  ),
  '^__clockwork/([0-9-]+|latest)/(next|previous)$' => 
  array (
    'GET' => $rewrite3,
    'HEAD' => $rewrite3,
  ),
  '^__clockwork/([0-9-]+|latest)/(next|previous)/(\\d+)$' => 
  array (
    'GET' => $rewrite4,
    'HEAD' => $rewrite4,
  ),
  '^__clockwork$' => 
  array (
    'GET' => $rewrite6,
    'HEAD' => $rewrite6,
  ),
  '^__clockwork/app$' => 
  array (
    'GET' => $rewrite7,
    'HEAD' => $rewrite7,
  ),
  '^__clockwork/(.+)$' => 
  array (
    'GET' => $rewrite8,
    'HEAD' => $rewrite8,
  ),
);
        }
    }
}

return new CachedRewriteCollection69ca524bca42f25176307b5468dca9d806d2b913cebffea2379f7c2a7c74d09b();

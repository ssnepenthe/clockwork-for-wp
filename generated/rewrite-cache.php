<?php

declare(strict_types=1);

return function (?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null): \ToyWpRouting\RewriteCollection {
    return new class($invocationStrategy) extends \ToyWpRouting\RewriteCollection
    {
        protected bool $locked = true;

        public function __construct(?\ToyWpRouting\InvocationStrategyInterface $invocationStrategy = null)
        {
            parent::__construct('cfw_', $invocationStrategy);

            $this->queryVariables = array (
  'cfw_id' => 'id',
  'cfw_matchedRule' => 'matchedRule',
  'cfw_direction' => 'direction',
  'cfw_count' => 'count',
  'cfw_file' => 'file',
);
            $this->rewriteRules = array (
  '^__clockwork/([0-9-]+|latest)/extended$' => 'index.php?cfw_id=$matches[1]&cfw_matchedRule=fc55b9a72b60021c5bdcc9afeeb39a07',
  '^__clockwork/([0-9-]+|latest)$' => 'index.php?cfw_id=$matches[1]&cfw_matchedRule=a94084cf934d45a3f6f5599a8065efee',
  '^__clockwork/([0-9-]+|latest)/(next|previous)$' => 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_matchedRule=48ac342b9f10651fa222e1f30d3e38e6',
  '^__clockwork/([0-9-]+|latest)/(next|previous)/(\\d+)$' => 'index.php?cfw_id=$matches[1]&cfw_direction=$matches[2]&cfw_count=$matches[3]&cfw_matchedRule=e297e7951cec4885654daaeded55a69a',
  '^__clockwork/auth$' => 'index.php?cfw_matchedRule=1cc470a4e2f705b422610d830eee6443',
  '^__clockwork$' => 'index.php?cfw_matchedRule=96dbc7c3844768158c5c3ad16158a7b9',
  '^__clockwork/app$' => 'index.php?cfw_matchedRule=1a7b5a96f01bcdd46e9c55ea26afbe5b',
  '^__clockwork/([^\\/]+\\.(html|json|js))$' => 'index.php?cfw_file=$matches[1]&cfw_matchedRule=e820c72227fa53b89aba03fb4459e578',
);

            $rewrite0 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'cfw_id' => 'id',
  'cfw_matchedRule' => 'matchedRule',
), $this->invocationStrategy, array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'serve_extended_json',
), NULL);
$rewrite1 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'cfw_id' => 'id',
  'cfw_matchedRule' => 'matchedRule',
  'cfw_direction' => 'direction',
  'cfw_count' => 'count',
), $this->invocationStrategy, array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'serve_json',
), NULL);
$rewrite2 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'PUT',
), array (
  'cfw_id' => 'id',
  'cfw_matchedRule' => 'matchedRule',
), $this->invocationStrategy, array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'update_data',
), array (
  0 => 'Clockwork_For_Wp\\Clockwork_Support',
  1 => 'is_collecting_client_metrics',
));
$rewrite3 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'POST',
), array (
  'cfw_matchedRule' => 'matchedRule',
), $this->invocationStrategy, array (
  0 => 'Clockwork_For_Wp\\Api\\Api_Controller',
  1 => 'authenticate',
), NULL);
$rewrite4 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'cfw_matchedRule' => 'matchedRule',
), $this->invocationStrategy, array (
  0 => 'Clockwork_For_Wp\\Web_App\\Web_App_Controller',
  1 => 'serve_redirect',
), NULL);
$rewrite5 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'cfw_matchedRule' => 'matchedRule',
), $this->invocationStrategy, array (
  0 => 'Clockwork_For_Wp\\Web_App\\Web_App_Controller',
  1 => 'serve_index',
), NULL);
$rewrite6 = new \ToyWpRouting\Compiler\OptimizedRewrite(array (
  0 => 'GET',
  1 => 'HEAD',
), array (
  'cfw_file' => 'file',
  'cfw_matchedRule' => 'matchedRule',
), $this->invocationStrategy, array (
  0 => 'Clockwork_For_Wp\\Web_App\\Web_App_Controller',
  1 => 'serve_asset',
), NULL);
$this->rewrites->attach($rewrite0);
$this->rewrites->attach($rewrite1);
$this->rewrites->attach($rewrite2);
$this->rewrites->attach($rewrite3);
$this->rewrites->attach($rewrite4);
$this->rewrites->attach($rewrite5);
$this->rewrites->attach($rewrite6);
$this->rewritesByHashAndMethod = array (
  'fc55b9a72b60021c5bdcc9afeeb39a07' => 
  array (
    'GET' => $rewrite0,
    'HEAD' => $rewrite0,
  ),
  'a94084cf934d45a3f6f5599a8065efee' => 
  array (
    'GET' => $rewrite1,
    'HEAD' => $rewrite1,
    'PUT' => $rewrite2,
  ),
  '48ac342b9f10651fa222e1f30d3e38e6' => 
  array (
    'GET' => $rewrite1,
    'HEAD' => $rewrite1,
  ),
  'e297e7951cec4885654daaeded55a69a' => 
  array (
    'GET' => $rewrite1,
    'HEAD' => $rewrite1,
  ),
  '1cc470a4e2f705b422610d830eee6443' => 
  array (
    'POST' => $rewrite3,
  ),
  '96dbc7c3844768158c5c3ad16158a7b9' => 
  array (
    'GET' => $rewrite4,
    'HEAD' => $rewrite4,
  ),
  '1a7b5a96f01bcdd46e9c55ea26afbe5b' => 
  array (
    'GET' => $rewrite5,
    'HEAD' => $rewrite5,
  ),
  'e820c72227fa53b89aba03fb4459e578' => 
  array (
    'GET' => $rewrite6,
    'HEAD' => $rewrite6,
  ),
);
        }
    };
};

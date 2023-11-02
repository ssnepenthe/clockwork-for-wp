<?php

declare(strict_types=1);

$finder = \PhpCsFixer\Finder::create()
	->in([
		__DIR__ . '/Integration',
		__DIR__ . '/Unit',
	]);

$config = (new \PhpCsFixer\Config())
	->setIndent("\t")
	->setRiskyAllowed(true);

$rules = include __DIR__ . '/../php-cs-fixer-rules.php';

return $config->setRules($rules(true))
	->setFinder($finder);

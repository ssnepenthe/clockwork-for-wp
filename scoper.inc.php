<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;
use PhpParser\Lexer\Emulative;
use pxlrbt\PhpScoper\PrefixRemover\IdentifierExtractor;
use pxlrbt\PhpScoper\PrefixRemover\RemovePrefixPatcher;

$stub_file = \getenv( 'WP_STUB_FILE' );

if ( ! $stub_file ) {
	exit( 'You must set the WP_STUB_FILE environment variable to run PHP-Scoper' );
}

if ( '~' === $stub_file[0] ) {
	$stub_file = $_SERVER['HOME'] . \mb_substr($stub_file, 1);
}

if ( ! \file_exists( $stub_file ) ) {
	exit( "Stub file {$stub_file} not found" );
}

if ( ! \is_readable( $stub_file ) ) {
	exit( "Stub file {$stub_file} not readable" );
}

$whitelist = [
	'Clockwork_For_Wp\*',
	'Clockwork\*',
];

if ( \filter_var( \getenv( 'EXCLUDE_PSR' ), \FILTER_VALIDATE_BOOLEAN ) ) {
	$whitelist[] = 'Psr\*';
}

$lexer = new Emulative( ['phpVersion' => '8.0'] );

// WP Function List.
$exclude_functions = \array_map(
	'strval',
	( new IdentifierExtractor( ['Stmt_Function'] ) )
		->addStub( $stub_file )
		->setLexer( $lexer )
		->extract()
);

// Laravel functions used by Clockwork.
$exclude_functions = \array_merge( $exclude_functions, ['app', 'env', 'storage_path'] );

return [
	'prefix' => 'CFW_Scoped',

	'finders' => [
		Finder::create()->files()->in( 'src' ),
		Finder::create()
			->files()
			->ignoreVCS( true )
			->notName( '/LICENSE|.*\\.md|.*\\.dist|Makefile|composer\\.json|composer\\.lock/' )
			->exclude( [
				'doc',
				'test',
				'test_old',
				'tests',
				'Tests',
				'vendor-bin',
			] )
			->in( 'vendor' ),
		Finder::create()->append( [
			'clockwork-for-wp.php',
			'composer.json',
			'composer.lock',
			'initialize-error-logger.php',
			'initialize-wp-cli-logger.php',
		] ),
	],

	'files-whitelist' => [
		'clockwork-for-wp.php',
	],

	'patchers' => [
		new RemovePrefixPatcher(
			( new IdentifierExtractor() )
				->addStub( $stub_file )
				->setLexer( $lexer )
				->extract()
		),
	],

	'exclude-namespaces' => [],

	'whitelist' => $whitelist,

	'expose-global-constants' => true,

	'expose-global-classes' => true,

	'expose-global-functions' => true,

	'exclude-classes' => [],

	'exclude-functions' => $exclude_functions,

	'exclude-constants' => [],
];

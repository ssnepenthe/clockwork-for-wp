<?php

namespace Clockwork_For_Wp\Tests\Unit\Routing;

use ArrayIterator;
use Clockwork_For_Wp\Routing\Fastroute_Converter;
use FastRoute\BadRouteException;
use InvalidArgumentException;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;

class Fastroute_Converter_Test extends TestCase {
	const TEST_PREFIX = 'pfx_';

	/** @dataProvider provide_test_convert */
	public function test_convert( $to_parse, $parsed ) {
		$converter = new Fastroute_Converter();

		$this->assertSame( $parsed, $converter->convert( $to_parse ) );
	}

	/** @dataProvider provide_test_convert_with_prefix */
	public function test_convert_with_prefix( $to_parse, $parsed ) {
		$converter = new Fastroute_Converter( self::TEST_PREFIX );

		$this->assertSame( $parsed, $converter->convert( $to_parse ) );
	}

	/** @dataProvider provide_test_convert_error */
	public function test_convert_error( $to_parse, $expected_exception_message ) {
		$this->expectException( BadRouteException::class );
		$this->expectExceptionMessage( $expected_exception_message );

		$converter = new Fastroute_Converter();
		$converter->convert( $to_parse );
	}

	public function test_additional_param_resolver_signature() {
		$converter = new Fastroute_Converter();
		$converter->resolve_additional_params_using( function( $regex, $query_array ) {
			$this->assertSame( '/([^/]+)', $regex );
			$this->assertSame( [ 'param' => '$matches[1]' ], $query_array );

			return [];
		} );

		$converter->convert( '/{param}' );
	}

	public function test_additional_param_resolver_non_array_return_value() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Additional param resolver must return an array' );

		$converter = new Fastroute_Converter();
		$converter->resolve_additional_params_using( function( $regex, $query_array ) {
			return '';
		} );

		$converter->convert( '/{param}' );
	}

	public function test_additional_param_resolver_non_string_array_key_return_value() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage(
			'Additional param resolver must return an array with string keys and string values'
		);

		$converter = new Fastroute_Converter();
		$converter->resolve_additional_params_using( function( $regex, $query_array ) {
			return ['test'];
		} );

		$converter->convert( '/{param}' );
	}

	public function test_additional_param_resolver_non_string_array_value_return_value() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage(
			'Additional param resolver must return an array with string keys and string values'
		);

		$converter = new Fastroute_Converter();
		$converter->resolve_additional_params_using( function( $regex, $query_array ) {
			return ['test' => 4];
		} );

		$converter->convert( '/{param}' );
	}

	public function test_additional_param_resolver_already_existing_param() {
		$this->expectException( InvalidArgumentException::class );
		$this->expectExceptionMessage(
			'Additional param resolver must not overwrite parsed query params'
		);

		$converter = new Fastroute_Converter();
		$converter->resolve_additional_params_using( function( $regex, $query_array ) {
			return ['param' => 'value'];
		} );

		$converter->convert( '/{param}' );
	}

	public function test_additional_param_resolver() {
		$converter = new Fastroute_Converter();
		$converter->resolve_additional_params_using( function( $regex, $query_array ) {
			return [ 'test_additional_param' => 'test_additional_value' ];
		} );

		$this->assertSame(
			[
				'^/([^/]+)$' => [
					'param' => '$matches[1]',
					'test_additional_param' => 'test_additional_value',
					'matched_route' => 'cb7a42543d1dcb1ae9407381064edc7c',
				],
			],
			$converter->convert( '/{param}' )
		);
	}

	public function provide_test_convert() {
		return new Rewrite_Test_Data_Iterator();
	}

	public function provide_test_convert_with_prefix() {
		return new Rewrite_Test_Data_Iterator( self::TEST_PREFIX );
	}

	public function provide_test_convert_error(): array
	{
		return [
			[
				'',
				'Empty routes not allowed',
			],
			[
				'[test]',
				'Empty routes not allowed',
			],
			[
				'/test[opt',
				"Number of opening '[' and closing ']' does not match",
			],
			[
				'/test[opt[opt2]',
				"Number of opening '[' and closing ']' does not match",
			],
			[
				'/testopt]',
				"Number of opening '[' and closing ']' does not match",
			],
			[
				'/test[]',
				'Empty optional part',
			],
			[
				'/test[[opt]]',
				'Empty optional part',
			],
			[
				'[[test]]',
				'Empty optional part',
			],
			[
				'/test[/opt]/required',
				'Optional segments can only occur at the end of a route',
			],
		];
	}
}

class Rewrite_Test_Data_Iterator implements IteratorAggregate {
	private $items = [
		[
			'/test',
			[
				'^/test$' => [ 'matched_route' => '4539330648b80f94ef3bf911f6d77ac9' ],
			]
		],
		[
			'/test/{param}',
			[
				'^/test/([^/]+)$' => [
					'param' => '$matches[1]',
					'matched_route' => '9eafb800f754dbc45fe995957f53d692',
				],
			],
		],
		[
			'/te{ param }st',
			[
				'^/te([^/]+)st$' => [
					'param' => '$matches[1]',
					'matched_route' => 'f612fcb7534acc2e41c66e14eb25257d'
				],
			],
		],
		[
			'/test/{param1}/test2/{param2}',
			[
				'^/test/([^/]+)/test2/([^/]+)$' => [
					'param1' => '$matches[1]',
					'param2' => '$matches[2]',
					'matched_route' => '8a84c99fc16019b92974f171d3a0b1fc',
				],
			],
		],
		[
			'/test/{param:\d+}',
			[
				'^/test/(\d+)$' => [
					'param' => '$matches[1]',
					'matched_route' => '6b31d5d9e8ed1b749aee0c8c86504432',
				],
			],
		],
		[
			'/test/{ param : \d{1,9} }',
			[
				'^/test/(\d{1,9})$' => [
					'param' => '$matches[1]',
					'matched_route' => '1a666958ee5cd914188d4d3d9b199c83',
				],
			],
		],
		[
			'/test[opt]',
			[
				'^/test$' => [ 'matched_route' => '4539330648b80f94ef3bf911f6d77ac9' ],
				'^/testopt$' => [ 'matched_route' => 'dcd7a3dc071ff33113e5a2b145e8f0e5' ],
			],
		],
		[
			'/test[/{param}]',
			[
				'^/test$' => [ 'matched_route' => '4539330648b80f94ef3bf911f6d77ac9' ],
				'^/test/([^/]+)$' => [
					'param' => '$matches[1]',
					'matched_route' => '9eafb800f754dbc45fe995957f53d692'
				],
			],
		],
		[
			'/{param}[opt]',
			[
				'^/([^/]+)$' => [
					'param' => '$matches[1]',
					'matched_route' => 'cb7a42543d1dcb1ae9407381064edc7c',
				],
				'^/([^/]+)opt$' => [
					'param' => '$matches[1]',
					'matched_route' => '284434840d30e9f17bc8338ce8482e20',
				],
			],
		],
		[
			'/test[/{name}[/{id:[0-9]+}]]',
			[
				'^/test$' => [ 'matched_route' => '4539330648b80f94ef3bf911f6d77ac9' ],
				'^/test/([^/]+)$' => [
					'name' => '$matches[1]',
					'matched_route' => '9eafb800f754dbc45fe995957f53d692'
				],
				'^/test/([^/]+)/([0-9]+)$' => [
					'name' => '$matches[1]',
					'id' => '$matches[2]',
					'matched_route' => '681c46f805f9a0a65627c2b84c3af598',
				],
			],
		],
		[
			'/{foo-bar}',
			[
				'^/([^/]+)$' => [
					'foo-bar' => '$matches[1]',
					'matched_route' => 'cb7a42543d1dcb1ae9407381064edc7c',
				],
			],
		],
		[
			'/{_foo:.*}',
			[
				'^/(.*)$' => [
					'_foo' => '$matches[1]',
					'matched_route' => '27177c608a2596608676d2965c954ca4',
				],
			],
		],
	];

	private $prefix;

	public function __construct( string $prefix = '' ) {
		$this->prefix = $prefix;
	}

	public function getIterator() {
		return new ArrayIterator( $this->prefixed_items() );
	}

	private function prefixed_items() {
		return array_map( function( $item ) {
			[ $route, $rewrites ] = $item;

			foreach ( $rewrites as $regex => $query_array ) {
				$prefixed_query_array = [];

				foreach ( $query_array as $key => $value ) {
					$prefixed_query_array[ "{$this->prefix}{$key}" ] = $value;
				}

				$rewrites[ $regex ] = $prefixed_query_array;
			}

			return [ $route, $rewrites ];
		}, $this->items );
	}
}

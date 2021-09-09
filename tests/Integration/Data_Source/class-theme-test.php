<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Helpers\StackFrame;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Theme;
use PHPUnit\Framework\TestCase;

class Theme_Test extends TestCase {
	/** @test */
	public function it_correctly_records_theme_data() {
		$data_source = new Theme();
		$request = new Request();

		$theme_root = '/srv/www/wordpress-default/public_html/wp-content/themes';

		$data_source->set_theme_root( $theme_root );
		$data_source->set_is_child_theme( false );
		$data_source->set_template( 'twentynineteen' );
		$data_source->set_stylesheet( 'twentynineteen' );
		$data_source->set_body_classes( [ 'one', 'two', 'three' ] );
		$data_source->set_content_width( 640 );
		$data_source->set_included_template( "{$theme_root}/twentynineteen/index.php" );
		$data_source->add_requested_template_part(
			'template-parts/header/site',
			'branding',
			[],
			[],
			"{$theme_root}/twentynineteen/template-parts/header/site.php",
			new StackFrame( [
				'function' => 'not_relevant',
				'file' => '/some/fake/file/path',
				'line' => 15
			] )
		);
		$data_source->add_requested_template_part(
			'template-parts/content/content',
			'',
			[],
			[],
			"{$theme_root}/twentynineteen/template-parts/content/content",
			new StackFrame( [
				'function' => 'not_relevant',
				'file' => '/some/fake/file/path',
				'line' => 15,
			] )
		);
		$data_source->add_requested_template_part(
			'template-parts/footer/footer',
			'widgets',
			[],
			[],
			"{$theme_root}/twentynineteen/template-parts/footer/footer",
			new StackFrame( [
				'function' => 'not_relevant',
				'file' => '/some/fake/file/path',
				'line' => 15,
			] )
		);

		$data_source->resolve( $request );

		$user_data = $request->userData( 'Theme' )->toArray();

		$this->assertEquals( [
			[
				'label' => 'Theme Root',
				'value' => $theme_root,
			],
			[
				'label' => 'Theme',
				'value' => 'twentynineteen',
			],
			[
				'label' => 'Content Width',
				'value' => '640',
			],
			[
				'label' => 'Included Template',
				'value' => 'index.php',
			],
			[
				'label' => 'Loaded Template Parts',
				'value' => [
					'template-parts/header/site' => [
						'slug' => 'template-parts/header/site',
						'name' => 'branding',
						'templates' => [],
						'args' => [],
						'file' => 'template-parts/header/site.php',
						'loaded' => true,
						'caller' => '/some/fake/file/path (line 15)',
					],
					'template-parts/content/content' => [
						'slug' => 'template-parts/content/content',
						'name' => '',
						'templates' => [],
						'args' => [],
						'file' => 'template-parts/content/content',
						'loaded' => true,
						'caller' => '/some/fake/file/path (line 15)',
					],
					'template-parts/footer/footer' => [
						'slug' => 'template-parts/footer/footer',
						'name' => 'widgets',
						'templates' => [],
						'args' => [],
						'file' => 'template-parts/footer/footer',
						'loaded' => true,
						'caller' => '/some/fake/file/path (line 15)',
					],
				],
			],
			[
				'label' => 'Body Classes',
				'value' => ['one', 'three', 'two'],
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Theme Data',
			],
		], $user_data[0] );
	}
}

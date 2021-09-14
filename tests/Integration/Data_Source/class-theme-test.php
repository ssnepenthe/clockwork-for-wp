<?php

namespace Clockwork_For_Wp\Tests\Integration\Data_Source;

use Clockwork\Helpers\StackFrame;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Theme;
use PHPUnit\Framework\TestCase;

use function Clockwork_For_Wp\array_only;

class Theme_Test extends TestCase {
	public function test_no_theme_data() {
		$data_source = new Theme();
		$request = new Request();

		$data_source->resolve( $request );

		$this->assertArrayNotHasKey( 'Theme', $request->userData );
	}

	public function test_theme_root() {
		$data_source = new Theme();
		$request = new Request();

		$theme_root = '/srv/www/wordpress-one/public_html/wp-content/themes';

		$data_source->set_theme_root( $theme_root );

		$data_source->resolve( $request );

		$this->assertSame( [
			'label' => 'Theme Root',
			'value' => $theme_root,
		], $request->userData( 'Theme' )->toArray()[0][0] );
	}

	public function test_theme() {
		$data_source = new Theme();
		$request = new Request();

		$template = 'twentynineteen';

		$data_source->set_template( $template );

		$data_source->resolve( $request );

		$this->assertSame( [
			'label' => 'Theme',
			'value' => $template,
		], $request->userData( 'Theme' )->toArray()[0][0] );
	}

	public function test_child_theme() {
		$data_source = new Theme();
		$request = new Request();

		$template = 'twentynineteen';
		$stylesheet = 'twentynineteen-child';

		$data_source->set_template( $template );
		$data_source->set_stylesheet( $stylesheet );
		$data_source->set_is_child_theme( true );

		$data_source->resolve( $request );

		$this->assertSame( [
			[
				'label' => 'Theme',
				'value' => $stylesheet,
			],
			[
				'label' => 'Parent Theme',
				'value' => $template,
			],
		], array_only( $request->userData( 'Theme' )->toArray()[0], [ 0, 1 ] ) );
	}

	public function test_content_width() {
		$data_source = new Theme();
		$request = new Request();

		$content_width = 720;

		$data_source->set_content_width( $content_width );

		$data_source->resolve( $request );

		$this->assertSame( [
			'label' => 'Content Width',
			'value' => $content_width,
		], $request->userData( 'Theme' )->toArray()[0][0] );
	}

	public function test_included_template() {
		$data_source = new Theme();
		$request = new Request();

		$theme_root = '/srv/www/wordpress-one/public_html/wp-content/themes';
		$theme = 'twentynineteen';
		$template = "{$theme_root}/{$theme}/index.php";

		$data_source->set_theme_root( $theme_root );
		$data_source->set_template( $theme );
		$data_source->set_stylesheet( 'irrelevant' );
		$data_source->set_included_template( $template );

		$data_source->resolve( $request );

		$this->assertSame( [
			'label' => 'Included Template',
			'value' => 'index.php',
		], $request->userData( 'Theme' )->toArray()[0][2] );
	}

	public function test_template_hierarchy() {
		$data_source = new Theme();
		$request = new Request();

		$hierarchy_one = ['front-page.php', 'home.php'];
		$hierarchy_two = ['index.php'];

		$data_source->add_hierarchy_templates( $hierarchy_one );
		$data_source->add_hierarchy_templates( $hierarchy_two );

		$data_source->resolve( $request );

		$this->assertSame( [
			'label' => 'Template Hierarchy',
			'value' => ['front-page.php', 'home.php', 'index.php'],
		], $request->userData( 'Theme' )->toArray()[0][0] );
	}

	public function test_loaded_template_parts() {
		$data_source = new Theme();
		$request = new Request();

		$theme_root = '/srv/www/wordpress-one/public_html/wp-content/themes';
		$theme = 'twentynineteen';

		$data_source->set_theme_root( $theme_root );
		$data_source->set_template( $theme );
		$data_source->set_stylesheet( 'irrelevant' );

		$data_source->add_requested_template_part(
			'template-parts/header/site',
			'branding',
			['template-parts/header/site-branding.php', 'template-parts/header/site.php'],
			[],
			"{$theme_root}/{$theme}/template-parts/header/site-branding.php",
			$this->make_stack_frame()
		);
		$data_source->add_requested_template_part(
			'template-parts/content/content',
			'',
			['template-parts/content/content.php'],
			[],
			"{$theme_root}/{$theme}/template-parts/content/content.php",
			$this->make_stack_frame()
		);
		$data_source->add_requested_template_part(
			'template-parts/footer/footer',
			'widgets',
			['template-parts/footer/footer-widgets.php', 'template-parts/footer/footer.php'],
			['test' => 'args', 'dummy' => 'data'],
			"{$theme_root}/{$theme}/template-parts/footer/footer-widgets.php",
			$this->make_stack_frame()
		);

		$data_source->resolve( $request );

		$this->assertSame( [
			'label' => 'Loaded Template Parts',
			'value' => [
				'template-parts/header/site-branding' => [
					'slug' => 'template-parts/header/site',
					'name' => 'branding',
					'templates' => ['template-parts/header/site-branding.php', 'template-parts/header/site.php'],
					'args' => [],
					'file' => 'template-parts/header/site-branding.php',
					'loaded' => true,
					'caller' => '/some/fake/file/path (line 15)',
				],
				'template-parts/content/content' => [
					'slug' => 'template-parts/content/content',
					'name' => '',
					'templates' => ['template-parts/content/content.php'],
					'args' => [],
					'file' => 'template-parts/content/content.php',
					'loaded' => true,
					'caller' => '/some/fake/file/path (line 15)',
				],
				'template-parts/footer/footer-widgets' => [
					'slug' => 'template-parts/footer/footer',
					'name' => 'widgets',
					'templates' => ['template-parts/footer/footer-widgets.php', 'template-parts/footer/footer.php'],
					'args' => ['test' => 'args', 'dummy' => 'data'],
					'file' => 'template-parts/footer/footer-widgets.php',
					'loaded' => true,
					'caller' => '/some/fake/file/path (line 15)',
				],
			],
		], $request->userData( 'Theme' )->toArray()[0][2] );
	}

	public function test_not_found_template_parts() {
		$data_source = new Theme();
		$request = new Request();

		$theme_root = '/srv/www/wordpress-one/public_html/wp-content/themes';
		$theme = 'twentynineteen';

		$data_source->set_theme_root( $theme_root );
		$data_source->set_template( $theme );
		$data_source->set_stylesheet( 'irrelevant' );

		$data_source->add_requested_template_part(
			'template-parts/header/site',
			'branding',
			['template-parts/header/site-branding.php', 'template-parts/header/site.php'],
			[],
			'',
			$this->make_stack_frame()
		);
		$data_source->add_requested_template_part(
			'template-parts/content/content',
			'',
			['template-parts/content/content.php'],
			[],
			'',
			$this->make_stack_frame()
		);
		$data_source->add_requested_template_part(
			'template-parts/footer/footer',
			'widgets',
			['template-parts/footer/footer-widgets.php', 'template-parts/footer/footer.php'],
			['test' => 'args', 'dummy' => 'data'],
			'',
			$this->make_stack_frame()
		);

		$data_source->resolve( $request );

		$this->assertSame( [
			'label' => 'Not Found Template Parts',
			'value' => [
				'template-parts/header/site-branding' => [
					'slug' => 'template-parts/header/site',
					'name' => 'branding',
					'templates' => ['template-parts/header/site-branding.php', 'template-parts/header/site.php'],
					'args' => [],
					'file' => 'template-parts/header/site-branding.php',
					'loaded' => false,
					'caller' => '/some/fake/file/path (line 15)',
				],
				'template-parts/content/content' => [
					'slug' => 'template-parts/content/content',
					'name' => '',
					'templates' => ['template-parts/content/content.php'],
					'args' => [],
					'file' => 'template-parts/content/content.php',
					'loaded' => false,
					'caller' => '/some/fake/file/path (line 15)',
				],
				'template-parts/footer/footer-widgets' => [
					'slug' => 'template-parts/footer/footer',
					'name' => 'widgets',
					'templates' => ['template-parts/footer/footer-widgets.php', 'template-parts/footer/footer.php'],
					'args' => ['test' => 'args', 'dummy' => 'data'],
					'file' => 'template-parts/footer/footer-widgets.php',
					'loaded' => false,
					'caller' => '/some/fake/file/path (line 15)',
				],
			],
		], $request->userData( 'Theme' )->toArray()[0][2] );
	}

	public function test_body_classes() {
		$data_source = new Theme();
		$request = new Request();

		$data_source->set_body_classes( [ 'one', 'two', 'three' ] );

		$data_source->resolve( $request );

		$this->assertSame( [
			'label' => 'Body Classes',
			// Classes are alphabetized.
			'value' => ['one', 'three', 'two'],
		], $request->userData( 'Theme' )->toArray()[0][0] );
	}

	public function test_theme_relative_path() {
		$data_source = new Theme();
		$request = new Request();

		$theme_root = '/srv/www/wordpress-one/public_html/wp-content/themes';
		$theme = 'twentynineteen';
		$template = 'index.php';

		$data_source->set_included_template( "{$theme_root}/{$theme}/{$template}" );

		$data_source->resolve( $request );

		// Unchanged when theme root is not set.
		$this->assertSame( [
			'label' => 'Included Template',
			'value' => "{$theme_root}/{$theme}/{$template}",
		], $request->userData( 'Theme' )->toArray()[0][0] );

		$data_source = new Theme();
		$request = new Request();

		$data_source->set_theme_root( $theme_root );
		$data_source->set_included_template( "{$theme_root}/{$theme}/{$template}" );

		$data_source->resolve( $request );

		// Strip theme root when it is set but template and stylesheet are not set.
		$this->assertSame( [
			'label' => 'Included Template',
			'value' => "{$theme}/{$template}",
		], $request->userData( 'Theme' )->toArray()[0][1] );

		$data_source = new Theme();
		$request = new Request();

		$data_source->set_theme_root( $theme_root );
		$data_source->set_template( $theme );
		$data_source->set_stylesheet( 'irrelevant' );
		$data_source->set_included_template( "{$theme_root}/{$theme}/{$template}" );

		$data_source->resolve( $request );

		// Strip theme root and theme when both set.
		$this->assertSame( [
			'label' => 'Included Template',
			'value' => $template,
		], $request->userData( 'Theme' )->toArray()[0][2] );

		$data_source = new Theme();
		$request = new Request();

		$data_source->set_theme_root( $theme_root );
		$data_source->set_template( $theme );
		$data_source->set_stylesheet( "{$theme}-child" );
		$data_source->set_is_child_theme( true );
		$data_source->set_included_template( "{$theme_root}/{$theme}-child/{$template}" );

		$data_source->resolve( $request );

		// Strip theme root and child theme when both set and is child theme.
		$this->assertSame( [
			'label' => 'Included Template',
			'value' => $template,
		], $request->userData( 'Theme' )->toArray()[0][3] );
	}

	private function make_stack_frame() {
		return new StackFrame( [
			'function' => 'not_relevant',
			'file' => '/some/fake/file/path',
			'line' => 15,
		], '%%testbasepath%%', '%%testvendorpath%%' );
	}
}

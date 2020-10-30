<?php

namespace Clockwork_For_Wp\Tests\Clockwork\Data_Source;

use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Theme;
use PHPUnit\Framework\TestCase;

class Theme_Test extends TestCase {
	/** @test */
	public function it_correctly_records_theme_data() {
		$data_source = new Theme();
		$request = new Request();

		$theme_root = '/srv/www/wordpress-default/public_html/wp-content/themes';

		$data_source->configure_theme( $theme_root, false, 'twentynineteen', 'twentynineteen' );
		$data_source->set_body_classes( [ 'one', 'two', 'three' ] );
		$data_source->set_content_width( 640 );
		$data_source->set_included_template( "{$theme_root}/twentynineteen/index.php" );
		$data_source->set_included_template_parts(
			"{$theme_root}/twentynineteen/template-parts/header/site-branding.php",
			"{$theme_root}/twentynineteen/template-parts/content/content.php",
			"{$theme_root}/twentynineteen/template-parts/footer/footer-widgets.php"
		);

		$data_source->resolve( $request );

		$user_data = $request->userData( 'Theme' )->toArray();

		$this->assertEquals( [
			[
				'Item' => 'Theme',
				'Value' => 'twentynineteen',
			],
			[
				'Item' => 'Content Width',
				'Value' => '640',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Miscellaneous',
			],
		], $user_data[0] );

		$this->assertEquals( [
			[
				'File' => 'index.php',
				'Path' => 'twentynineteen/index.php',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Included Template',
			],
		], $user_data[1] );

		$this->assertEquals( [
			[
				'File' => 'site-branding.php',
				'Path' => 'twentynineteen/template-parts/header/site-branding.php',
			],
			[
				'File' => 'content.php',
				'Path' => 'twentynineteen/template-parts/content/content.php',
			],
			[
				'File' => 'footer-widgets.php',
				'Path' => 'twentynineteen/template-parts/footer/footer-widgets.php',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Template Parts',
			],
		], $user_data[2] );

		$this->assertEquals( [
			[
				'Class' => 'one',
			],
			[
				'Class' => 'two',
			],
			[
				'Class' => 'three',
			],
			'__meta' => [
				'showAs' => 'table',
				'title' => 'Body Classes',
			],
		], $user_data[3] );
	}
}

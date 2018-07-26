<?php

namespace Clockwork_For_Wp;

use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;

class Theme_Data_Source extends DataSource {
	protected $views;

	public function __construct( Timeline $views = null ) {
		$this->views = $views ?: new Timeline();
	}

	public function resolve( Request $request ) {
		$this->register_template_parts();

		$request->viewsData = array_merge( $request->viewsData, $this->views->finalize() );

		return $request;
	}

	public function listen_to_events() {
		add_filter( 'template_include', function( $template ) {
			$relative = str_replace(
				[ get_template_directory(), get_stylesheet_directory() ],
				'',
				$template
			);

			$this->register_template( $relative );

			return $template;
		} );
	}

	public function register_template( $template ) {
		$hash = hash( 'md5', $template );

		$this->views->addEvent( "view_{$hash}", 'Rendering a view', null, null, [
			'name' => ltrim( $template, '/' ),
			'data' => '(unknown)', // @todo Can we retrieve data used to render view? Probably not since it comes from globals or global function calls...
		] );

		return $template;
	}

	protected function register_template_parts() {
		foreach ( $this->get_included_theme_files() as $template ) {
			$slug = ltrim( str_replace( '.php', '', $template ), '/' );

			if ( did_action( "get_template_part_{$slug}" ) ) {
				$this->register_template( $template );
			} else {
				$slug = preg_replace( '/\-[^\-]+$/', '', $slug );

				if ( did_action( "get_template_part_{$slug}" ) ) {
					$this->register_template( $template );
				}
			}
		}
	}

	protected function get_included_theme_files() {
		$template = get_template_directory();
		$stylesheet = get_stylesheet_directory();
		$includes = get_included_files();

		// @todo This will list all included theme files. I think it would be a drastic improvement to only show the main template file (the file included @ template_include) and included template parts (files for which a get_template_part_{$slug} action has been triggered).
		return array_filter(
			array_combine(
				$includes,
				array_map(
					/**
					 * @param string  $file
					 * @return string
					 */
					function( $file ) use ( $template, $stylesheet ) {
						return str_replace( [ $template, $stylesheet ], '', $file );
					},
					$includes
				)
			),
			/**
			 * @param  string $absolute
			 * @param  string $relative
			 * @return boolean
			 */
			function( $absolute, $relative ) {
				return $absolute !== $relative;
			},
			ARRAY_FILTER_USE_BOTH
		);
	}
}

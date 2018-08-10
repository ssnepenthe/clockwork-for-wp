<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork_For_Wp\Plugin;
use Clockwork\Request\Request;
use Clockwork\DataSource\DataSource;

class Theme extends DataSource {
	protected $body_classes;
	protected $content_width;
	protected $included_template;

	public function __construct( $content_width = null ) {
		$this->set_body_classes( [] );
		$this->set_content_width( $content_width );
		$this->set_included_template( '' );
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'theme' )->title( 'Theme' );

		$panel->table( 'Miscellaneous', $this->miscellaneous_table() );

		if ( '' !== $this->included_template ) {
			$panel->table( 'Included Template', $this->included_template_table() );
		}

		if ( 0 !== count( $this->all_template_parts() ) ) {
			$panel->table( 'Template Parts', $this->template_parts_table() );
		}

		if ( 0 !== count( $this->body_classes ) ) {
			$panel->table( 'Body Classes', $this->body_classes_table() );
		}

		return $request;
	}

	public function listen_to_events() {
		add_filter( 'body_class', function( $classes ) {
			$this->set_body_classes( $classes );

			return $classes;
		}, Plugin::LATE_EVENT );

		add_filter( 'template_include', function( $template ) {
			$this->set_included_template( $template );

			return $template;
		}, Plugin::LATE_EVENT );
	}

	public function set_body_classes( $body_classes ) {
		if ( ! is_array( $body_classes ) ) {
			$body_classes = [];
		}

		$this->body_classes = array_values( array_map( function( $class ) {
			return (string) $class;
		}, $body_classes ) );
	}

	public function set_content_width( $content_width ) {
		$this->content_width = is_int( $content_width ) ? $content_width : null;
	}

	public function set_included_template( $included_template ) {
		$this->included_template = is_string( $included_template ) ? $included_template : '';
	}

	protected function all_template_parts() {
		// @todo Cache this list?
		return array_merge(
			$this->parent_theme_template_parts(),
			$this->child_theme_template_parts()
		);
	}

	protected function body_classes_table() {
		return array_map( function( $class ) {
			return [ 'Class' => $class ];
		}, $this->body_classes );
	}

	protected function child_theme_files() {
		if ( ! is_child_theme() ) {
			return [];
		}

		return array_filter( get_included_files(), function( $file_path ) {
			return 0 === strpos( $file_path, get_stylesheet_directory() );
		} );
	}

	protected function child_theme_template_parts() {
		return array_filter( $this->child_theme_files(), [ $this, 'is_included_template_part' ] );
	}

	protected function included_template_table() {
		$File = pathinfo( $this->included_template, PATHINFO_BASENAME );
		$Path = ltrim( str_replace( get_theme_root(), '', $this->included_template ), '/' );

		return [ compact( 'File', 'Path' ) ];
	}

	protected function is_included_template_part( $file_path ) {
		$relative = str_replace(
			[ get_template_directory(), get_stylesheet_directory() ],
			'',
			$file_path
		);
		$slug = ltrim( str_replace( '.php', '', $relative ), '/' );

		if ( did_action( "get_template_part_{$slug}" ) ) {
			return true;
		} else {
			$slug = preg_replace( '/\-[^\-]+$/', '', $slug );

			if ( did_action( "get_template_part_{$slug}" ) ) {
				return true;
			}
		}

		return false;
	}

	protected function miscellaneous_table() {
		$is_child = is_child_theme();

		$miscellaneous = [
			[
				'Item' => 'Theme',
				'Value' => $is_child ? get_stylesheet() : get_template(),
			],
		];

		if ( $is_child ) {
			$miscellaneous[] = [
				'Item' => 'Parent Theme',
				'Value' => get_template(),
			];
		}

		if ( null !== $this->content_width ) {
			$miscellaneous[] = [
				'Item' => 'Content Width',
				'Value' => $this->content_width,
			];
		}

		return $miscellaneous;
	}

	protected function parent_theme_files() {
		return array_filter( get_included_files(), function( $file_path ) {
			return 0 === strpos( $file_path, get_template_directory() );
		} );
	}

	protected function parent_theme_template_parts() {
		return array_filter( $this->parent_theme_files(), [ $this, 'is_included_template_part' ] );
	}

	protected function template_parts_table() {
		return array_map( function( $file_path ) {
			$File = pathinfo( $file_path, PATHINFO_BASENAME );
			$Path = ltrim( str_replace( get_theme_root(), '', $file_path ), '/' );

			return compact( 'File', 'Path' );
		}, $this->all_template_parts() );
	}
}

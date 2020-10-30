<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Included_Files;

class Theme extends DataSource implements Subscriber {
	protected $body_classes = [];
	protected $content_width;
	protected $included_template = '';
	protected $included_template_parts = [];
	protected $is_child_theme = false;
	protected $stylesheet;
	protected $template;
	protected $theme_root;

	public function subscribe_to_events( Event_Manager $event_manager ) : void {
		// @todo Record theme render time (from template_include to wp_footer?).
		$event_manager
			->on( 'cfw_pre_resolve_request', function( $content_width ) {
				$this
					// @todo Constructor?
					->configure_theme(
						get_theme_root(),
						is_child_theme(),
						get_template(),
						get_stylesheet()
					)
					->set_content_width( $content_width )
					->set_included_template_parts(
						...array_merge(
							Included_Files::template_parts_from_parent_theme(),
							Included_Files::template_parts_from_child_theme()
						)
					);
			} )
			->on( 'body_class', function( $classes ) {
				$this->set_body_classes( is_array( $classes ) ? $classes : [] );

				return $classes;
			}, Event_Manager::LATE_EVENT )
			->on( 'template_include', function( $template ) {
				$this->set_included_template( $template );

				return $template;
			}, Event_Manager::LATE_EVENT );
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'Theme' );

		$panel->table( 'Miscellaneous', $this->miscellaneous_table() );

		if ( '' !== $this->included_template ) {
			$panel->table( 'Included Template', $this->included_template_table() );
		}

		if ( 0 !== count( $this->included_template_parts ) ) {
			$panel->table(
				'Template Parts',
				$this->template_parts_table()
			);
		}

		if ( 0 !== count( $this->body_classes ) ) {
			$panel->table( 'Body Classes', $this->body_classes_table() );
		}

		return $request;
	}

	public function configure_theme( $theme_root, $is_child_theme, $template, $stylesheet ) {
		$this->theme_root = $theme_root;
		$this->is_child_theme = $is_child_theme;
		$this->template = $template;
		$this->stylesheet = $stylesheet;

		return $this;
	}

	public function set_body_classes( array $body_classes ) {
		$this->body_classes = array_values( array_map( 'strval', $body_classes ) );

		return $this;
	}

	public function set_content_width( int $content_width ) {
		$this->content_width = $content_width;

		return $this;
	}

	public function set_included_template( string $included_template ) {
		$this->included_template = $included_template;

		return $this;
	}

	public function set_included_template_parts( string ...$template_parts ) {
		$this->included_template_parts = $template_parts;

		return $this;
	}

	protected function body_classes_table() {
		return array_map( function( $class ) {
			return [ 'Class' => $class ];
		}, $this->body_classes );
	}

	protected function included_template_table() {
		$File = pathinfo( $this->included_template, PATHINFO_BASENAME );
		$Path = ltrim( str_replace( $this->theme_root, '', $this->included_template ), '/' );

		return [ compact( 'File', 'Path' ) ];
	}

	protected function miscellaneous_table() {
		return array_filter( [
			[
				'Item' => 'Theme',
				'Value' => $this->is_child_theme ? $this->stylesheet : $this->template,
			],
			[
				'Item' => 'Parent Theme',
				'Value' => $this->is_child_theme ? $this->template : null,
			],
			[
				'Item' => 'Content Width',
				'Value' => $this->content_width,
			],
		], function( $row ) {
			return null !== $row['Value'];
		} );
	}

	// @todo Deferred set from provider?
	protected function template_parts_table() {
		return array_map( function( $file_path ) {
			$File = pathinfo( $file_path, PATHINFO_BASENAME );
			$Path = ltrim( str_replace( $this->theme_root, '', $file_path ), '/' );

			return compact( 'File', 'Path' );
		}, $this->included_template_parts );
	}
}

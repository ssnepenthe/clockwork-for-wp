<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Helpers\StackFilter;
use Clockwork\Helpers\StackFrame;
use Clockwork\Helpers\StackTrace;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Data_Source\Subscriber\Theme_Subscriber;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Provides_Subscriber;

final class Theme extends DataSource implements Provides_Subscriber {
	/**
	 * @var string[]
	 */
	private $body_classes = [];

	/**
	 * @var null|int
	 */
	private $content_width;

	/**
	 * @var null|string
	 */
	private $included_template;

	/**
	 * @var bool
	 */
	private $is_child_theme = false;

	/**
	 * @var null|string
	 */
	private $stylesheet;

	/**
	 * @var null|string
	 */
	private $template;

	/**
	 * @var string[]
	 */
	private $template_hierarchy = [];

	/**
	 * @var array<int, array{slug: string, name: string, templates: array, args: array, caller: string, file: string, loaded: bool}>
	 */
	private $template_parts = [];

	/**
	 * @var null|string
	 */
	private $theme_root;

	public function add_hierarchy_templates( array $templates ): self {
		$this->template_hierarchy = \array_merge( $this->template_hierarchy, $templates );

		return $this;
	}

	/**
	 * @param string[] $templates
	 */
	public function add_requested_template_part(
		string $slug,
		string $name,
		array $templates,
		array $args,
		string $located_template,
		?StackFrame $caller_frame = null
	): self {
		if ( $located_template ) {
			$loaded = true;
			$file = $located_template;
		} else {
			$loaded = false;
			$file = $slug;

			if ( '' !== $name ) {
				$file .= "-{$name}";
			}

			$file .= '.php';
		}

		$caller_frame = $caller_frame ?? StackTrace::get()->first(
			StackFilter::make()->isFunction( 'get_template_part' )
		);
		$caller = "{$caller_frame->file} (line {$caller_frame->line})";

		$this->template_parts[] = \compact(
			'slug',
			'name',
			'templates',
			'args',
			'file',
			'loaded',
			'caller'
		);

		return $this;
	}

	public function create_subscriber(): Subscriber {
		return new Theme_Subscriber( $this );
	}

	public function resolve( Request $request ): Request {
		$table = \array_filter(
			[
				[
					'label' => 'Theme Root',
					'value' => $this->theme_root,
				],
				[
					'label' => 'Theme',
					'value' => $this->is_child_theme ? $this->stylesheet : $this->template,
				],
				[
					'label' => 'Parent Theme',
					'value' => $this->is_child_theme ? $this->template : null,
				],
				[
					'label' => 'Content Width',
					'value' => $this->content_width,
				],
			],
			static function ( $row ) {
				return null !== $row['value'];
			}
		);

		if ( null !== $this->included_template ) {
			$table[] = [
				'label' => 'Included Template',
				'value' => $this->theme_relative_path( $this->included_template ),
			];
		}

		if ( 0 !== \count( $this->template_hierarchy ) ) {
			$table[] = [
				'label' => 'Template Hierarchy',
				'value' => \array_unique( $this->template_hierarchy ),
			];
		}

		if ( 0 !== \count( $this->template_parts ) ) {
			$found_templates = $not_found_templates = [];

			foreach ( $this->template_parts as $template_part ) {
				if ( $template_part['loaded'] ) {
					$template_part['file'] = $this->theme_relative_path( $template_part['file'] );
				}

				$key = \str_replace( '.php', '', $template_part['file'] );

				if ( $template_part['loaded'] ) {
					$found_templates[ $key ] = $template_part;
				} else {
					$not_found_templates[ $key ] = $template_part;
				}
			}

			if ( \count( $found_templates ) > 0 ) {
				$table[] = [
					'label' => 'Loaded Template Parts',
					'value' => $found_templates,
				];
			}

			if ( \count( $not_found_templates ) > 0 ) {
				$table[] = [
					'label' => 'Not Found Template Parts',
					'value' => $not_found_templates,
				];
			}
		}

		if ( 0 !== \count( $this->body_classes ) ) {
			$classes = $this->body_classes;
			\sort( $classes );

			$table[] = [
				'label' => 'Body Classes',
				'value' => $classes,
			];
		}

		if ( \count( $table ) > 0 ) {
			$request->userData( 'Theme' )->table( 'Theme Data', $table );
		}

		return $request;
	}

	public function set_body_classes( array $body_classes ): self {
		$this->body_classes = \array_values( \array_map( 'strval', $body_classes ) );

		return $this;
	}

	public function set_content_width( int $content_width ): self {
		$this->content_width = $content_width;

		return $this;
	}

	public function set_included_template( string $included_template ): self {
		$this->included_template = $included_template;

		return $this;
	}

	public function set_is_child_theme( bool $is_child_theme ): self {
		$this->is_child_theme = $is_child_theme;

		return $this;
	}

	public function set_stylesheet( string $stylesheet ): self {
		$this->stylesheet = $stylesheet;

		return $this;
	}

	public function set_template( string $template ): self {
		$this->template = $template;

		return $this;
	}

	public function set_theme_root( string $theme_root ): self {
		$this->theme_root = $theme_root;

		return $this;
	}

	private function theme_relative_path( string $path ): string {
		if ( null === $this->theme_root ) {
			return $path;
		}

		$search = $this->theme_root;

		$has_theme = \is_string( $this->stylesheet ) && \is_string( $this->template );

		if ( $has_theme ) {
			$search .= '/' . ( $this->is_child_theme ? $this->stylesheet : $this->template );
		}

		return \ltrim( \str_replace( $search, '', $path ), '/' );
	}
}

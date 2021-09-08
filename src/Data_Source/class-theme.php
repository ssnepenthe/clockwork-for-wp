<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;
use Clockwork_For_Wp\Included_Files;

final class Theme extends DataSource implements Subscriber {
	private $body_classes = [];
	private $content_width;
	private $included_template = '';
	private $included_template_parts = [];
	private $is_child_theme = false;
	private $stylesheet;
	private $template;
	/**
	 * @var string[]
	 */
	private $template_hierarchy = [];
	private $theme_root;

	public function add_hierarchy_templates( array $templates ): self {
		$this->template_hierarchy = \array_merge( $this->template_hierarchy, $templates );

		return $this;
	}

	public function configure_theme( $theme_root, $is_child_theme, $template, $stylesheet ) {
		$this->theme_root = $theme_root;
		$this->is_child_theme = $is_child_theme;
		$this->template = $template;
		$this->stylesheet = $stylesheet;

		return $this;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => function ( $content_width ): void {
				$this
					->configure_theme(
						\get_theme_root(),
						\is_child_theme(),
						\get_template(),
						\get_stylesheet()
					)
					->set_content_width( $content_width )
					->set_included_template_parts(
						...\array_merge(
							Included_Files::template_parts_from_parent_theme(),
							Included_Files::template_parts_from_child_theme()
						)
					);
			},
			'body_class' => [
				function ( $classes ) {
					$this->set_body_classes( \is_array( $classes ) ? $classes : [] );

					return $classes;
				},
				Event_Manager::LATE_EVENT,
			],
			'template_include' => [
				function ( $template ) {
					$this->set_included_template( $template );

					return $template;
				},
				Event_Manager::LATE_EVENT,
			],
			'template_redirect' => function ( Event_Manager $events ): void {
				foreach (
					$this->hierarchy_conditional_filter_map() as $conditional => $filter
				) {
					if ( \function_exists( $conditional ) && $conditional() ) {
						$events->on(
							$filter,
							/**
							 * @param array $templates
							 *
							 * @return array
							 */
							function ( $templates ) {
								/**
								 * @psalm-suppress RedundantCastGivenDocblockType
								 */
								$this->add_hierarchy_templates( (array) $templates );

								return $templates;
							},
							Event_Manager::LATE_EVENT
						);
					}
				}
			},
		];
	}

	public function resolve( Request $request ) {
		$panel = $request->userData( 'Theme' );

		$panel->table( 'Miscellaneous', $this->miscellaneous_table() );

		if ( '' !== $this->included_template ) {
			$panel->table( 'Included Template', $this->included_template_table() );
		}

		if ( 0 !== \count( $this->template_hierarchy ) ) {
			$panel->table( 'Template Hierarchy', $this->template_hierarchy_table() );
		}

		if ( 0 !== \count( $this->included_template_parts ) ) {
			$panel->table(
				'Template Parts',
				$this->template_parts_table()
			);
		}

		if ( 0 !== \count( $this->body_classes ) ) {
			$panel->table( 'Body Classes', $this->body_classes_table() );
		}

		return $request;
	}

	public function set_body_classes( array $body_classes ) {
		$this->body_classes = \array_values( \array_map( 'strval', $body_classes ) );

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

	private function body_classes_table() {
		return \array_map(
			static function ( $class ) {
				return [ 'Class' => $class ];
			},
			$this->body_classes
		);
	}

	private function file_basename( string $path ): string {
		return \pathinfo( $path, \PATHINFO_BASENAME );
	}

	/**
	 * @return array<string, string>
	 */
	private function hierarchy_conditional_filter_map(): array {
		// @todo Should this be configurable?
		return [
			'is_embed' => 'embed_template_hierarchy',
			'is_404' => '404_template_hierarchy',
			'is_search' => 'search_template_hierarchy',
			'is_front_page' => 'frontpage_template_hierarchy',
			'is_home' => 'home_template_hierarchy',
			'is_privacy_policy' => 'privacypolicy_template_hierarchy',
			'is_post_type_archive' => 'archive_template_hierarchy',
			'is_tax' => 'taxonomy_template_hierarchy',
			'is_attachment' => 'attachment_template_hierarchy',
			'is_single' => 'single_template_hierarchy',
			'is_page' => 'page_template_hierarchy',
			'is_singular' => 'singular_template_hierarchy',
			'is_category' => 'category_template_hierarchy',
			'is_tag' => 'tag_template_hierarchy',
			'is_author' => 'author_template_hierarchy',
			'is_date' => 'date_template_hierarchy',
			'is_archive' => 'archive_template_hierarchy',
			'__return_true' => 'index_template_hierarchy',
		];
	}

	private function included_template_table() {
		return [
			[
				'File' => $this->file_basename( $this->included_template ),
				'Path' => $this->theme_relative_path( $this->included_template ),
			],
		];
	}

	private function miscellaneous_table() {
		return \array_filter(
			[
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
			],
			static function ( $row ) {
				return null !== $row['Value'];
			}
		);
	}

	/**
	 * @return array<int, array{File: string, Path: string}>
	 */
	private function template_hierarchy_table(): array {
		return \array_map(
			function ( $file_path ) {
				return [
					'File' => $this->file_basename( $file_path ),
					'Path' => $this->theme_relative_path( $file_path ),
				];
			},
			\array_unique( $this->template_hierarchy )
		);
	}

	private function template_parts_table() {
		return \array_map(
			function ( $file_path ) {
				return [
					'File' => $this->file_basename( $file_path ),
					'Path' => $this->theme_relative_path( $file_path ),
				];
			},
			$this->included_template_parts
		);
	}

	private function theme_relative_path( string $path ): string {
		return \ltrim( \str_replace( $this->theme_root, '', $path ), '/' );
	}
}

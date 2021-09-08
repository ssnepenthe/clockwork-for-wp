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
	private $included_template;
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

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => function ( $content_width ): void {
				$this
					->set_theme_root( \get_theme_root() )
					->set_is_child_theme( \is_child_theme() )
					->set_template( \get_template() )
					->set_stylesheet( \get_stylesheet() )
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

		$table = \array_filter(
			[
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
				'value' => \array_map(
					function ( $file_path ) {
						return $this->theme_relative_path( $file_path );
					},
					\array_unique( $this->template_hierarchy )
				),
			];
		}

		if ( 0 !== \count( $this->included_template_parts ) ) {
			$template_parts = \array_map(
				function ( $file_path ) {
					return $this->theme_relative_path( $file_path );
				},
				$this->included_template_parts
			);
			\sort( $template_parts );

			$table[] = [
				'label' => 'Included Template Parts',
				'value' => $template_parts,
			];
		}

		if ( 0 !== \count( $this->body_classes ) ) {
			$classes = $this->body_classes;
			\sort( $classes );

			$table[] = [
				'label' => 'Body Classes',
				'value' => $classes,
			];
		}

		$panel->table( 'Theme Data', $table );

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

	private function theme_relative_path( string $path ): string {
		return \ltrim( \str_replace( $this->theme_root, '', $path ), '/' );
	}
}

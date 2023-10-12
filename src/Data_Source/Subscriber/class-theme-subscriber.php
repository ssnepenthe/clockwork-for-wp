<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Data_Source\Subscriber;

use Clockwork_For_Wp\Data_Source\Theme;
use Clockwork_For_Wp\Event_Management\Event_Manager;
use Clockwork_For_Wp\Event_Management\Subscriber;

/**
 * @internal
 */
final class Theme_Subscriber implements Subscriber {
	private Theme $data_source;

	public function __construct( Theme $data_source ) {
		$this->data_source = $data_source;
	}

	public function get_subscribed_events(): array {
		return [
			'cfw_pre_resolve' => 'on_cfw_pre_resolve',
			'body_class' => [ 'on_body_class', Event_Manager::LATE_EVENT ],
			'get_template_part' => 'on_get_template_part',
			'template_include' => [ 'on_template_include', Event_Manager::LATE_EVENT ],
			'template_redirect' => 'on_template_redirect',
		];
	}

	/**
	 * @param array $classes
	 *
	 * @return array $classes
	 */
	public function on_body_class( $classes ) {
		/**
		 * @psalm-suppress RedundantConditionGivenDocblockType
		 * @psalm-suppress DocblockTypeContradiction
		 */
		$recorded_classes = \is_array( $classes ) ? $classes : [];

		$this->data_source->set_body_classes( $recorded_classes );

		return $classes;
	}

	/**
	 * @param int $content_width
	 */
	public function on_cfw_pre_resolve( $content_width ): void {
		/**
		 * @psalm-suppress RedundantCastGivenDocblockType
		 */
		$recorded_content_width = (int) $content_width;

		$this->data_source
			->set_theme_root( \get_theme_root() )
			->set_is_child_theme( \is_child_theme() )
			->set_template( \get_template() )
			->set_stylesheet( \get_stylesheet() )
			->set_content_width( $recorded_content_width );
	}

	/**
	 * @param string[] $templates
	 */
	public function on_get_template_part(
		string $slug,
		string $name,
		array $templates,
		array $args
	): void {
		$this->data_source->add_requested_template_part(
			$slug,
			$name,
			$templates,
			$args,
			\locate_template( $templates )
		);
	}

	/**
	 * @param string $template
	 *
	 * @return string
	 */
	public function on_template_include( $template ) {
		/**
		 * @psalm-suppress RedundantCastGivenDocblockType
		 */
		$recorded_template = (string) $template;

		$this->data_source->set_included_template( $recorded_template );

		return $template;
	}

	public function on_template_redirect( Event_Manager $events ): void {
		$conditional_filter_map = $this->hierarchy_conditional_filter_map();

		foreach ( $conditional_filter_map as $conditional => $filter ) {
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
						 * @psalm-suppress RedundantConditionGivenDocblockType
						 * @psalm-suppress DocblockTypeContradiction
						 */
						$recorded_templates = \is_array( $templates ) ? $templates : [];

						$this->data_source->add_hierarchy_templates( $recorded_templates );

						return $templates;
					},
					Event_Manager::LATE_EVENT
				);
			}
		}
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
}

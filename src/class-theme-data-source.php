<?php

namespace Clockwork_For_Wp;

use Clockwork\Request\Request;
use Clockwork\Request\Timeline;
use Clockwork\DataSource\DataSource;

class Theme_Data_Source extends DataSource {
	/**
	 * @var Timeline
	 */
	protected $timeline;

	/**
	 * @param Timeline|null $timeline
	 */
	public function __construct( Timeline $timeline = null ) {
		$this->timeline = $timeline ?: new Timeline();
	}

	public function resolve( Request $request ) {
		$request->timelineData = array_merge(
			$request->timelineData,
			$this->timeline->finalize( $request->time )
		);
		$request->viewsData = $this->collect_views_data();

		return $request;
	}

	/**
	 * @return array<string, array>
	 */
	protected function collect_views_data() {
		$template = get_template_directory();
		$stylesheet = get_stylesheet_directory();
		$includes = get_included_files();

		// @todo This will list all included theme files. I think it would be a drastic improvement to only show the main template file (the file included @ template_include) and included template parts (files for which a get_template_part_{$slug} action has been triggered).
		$templates = array_filter(
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

		$views = new Timeline();

		foreach ( $templates as $absolute => $relative ) {
			$views->addEvent( "view_{$absolute}", 'Rendering a view', null, null, [
				'name' => $relative,
				'data' => '(unknown)', // @todo Can we retrieve data used to render view? Probably not since it comes from globals or global function calls...
			] );
		}

		return $views->finalize();
	}
}

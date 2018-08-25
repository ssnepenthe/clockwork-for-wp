<?php

namespace Clockwork_For_Wp\Definitions;

use Pimple\Container;
use Clockwork_For_Wp\Plugin;
use Clockwork_For_Wp\Route_Manager;
use Clockwork_For_Wp\Definitions\Definition;

class Routes extends Definition {
	public function get_identifier() {
		return 'routes';
	}

	public function get_subscribed_events() {
		return [
			[ 'option_rewrite_rules',            'merge_rewrite_rules'                      ],
			[ 'pre_update_option_rewrite_rules', 'diff_rewrite_rules'                       ],
			[ 'query_vars',                      'merge_query_vars'                         ],
			[ 'rewrite_rules_array',             'merge_rewrite_rules'                      ],
			[ 'template_redirect',               'call_matched_handler', Plugin::LATE_EVENT ],
		];
	}

	public function get_value() {
		return function( Container $container ) {
			return new Route_Manager( $container['wp'] );
		};
	}

	public function is_enabled() {
		// @todo
		return true;
	}
}

<?php

namespace Clockwork_For_Wp;

use Pimple\Container;
use Clockwork_For_Wp\Definitions\Definition_Interface;
use Clockwork_For_Wp\Definitions\Toggling_Definition_Interface;
use Clockwork_For_Wp\Definitions\Subscribing_Definition_Interface;

class Plugin {
	const DEFAULT_EVENT = 10;
	const EARLY_EVENT = -999;
	const LATE_EVENT = 999;

	protected $booted = false;
	protected $container;
	protected $definitions = [];

	public function __construct( array $values = [] ) {
		$this->container = new Container( $values );
		$this->container->register( new WordPress_Provider() );

		foreach ( [
			// Data sources.
			new Definitions\Data_Sources\Php( $this ),

			new Definitions\Data_Sources\Conditionals( $this ),
			new Definitions\Data_Sources\Constants( $this ),
			new Definitions\Data_Sources\Core( $this ),
			new Definitions\Data_Sources\Errors( $this ),
			new Definitions\Data_Sources\Rest_Api( $this ),
			new Definitions\Data_Sources\Theme( $this ),
			new Definitions\Data_Sources\Timestart( $this ),
			new Definitions\Data_Sources\Transients( $this ),
			new Definitions\Data_Sources\Wp_Hook( $this ),
			new Definitions\Data_Sources\Wp_Http( $this ),
			new Definitions\Data_Sources\Wp_Mail( $this ),
			new Definitions\Data_Sources\Wp_Object_Cache( $this ),
			new Definitions\Data_Sources\Wp_Query( $this ),
			new Definitions\Data_Sources\Wp_Rewrite( $this ),
			new Definitions\Data_Sources\Wp( $this ),
			new Definitions\Data_Sources\Wpdb( $this ),
			new Definitions\Data_Sources\Xdebug( $this ),

			// Helpers.
			new Definitions\Helpers\Api( $this ),
			new Definitions\Helpers\Request( $this ),
			new Definitions\Helpers\Web( $this ),

			// The rest.
			new Definitions\Clockwork_Authenticator( $this ),
			new Definitions\Clockwork_Storage( $this ),
			new Definitions\Clockwork( $this ),
			new Definitions\Config( $this ),
			new Definitions\Routes( $this ),
		] as $definition ) {
			$this->register( $definition );
		}
	}

	public function boot() {
		if ( $this->booted ) {
			return;
		}

		foreach ( $this->definitions() as $definition ) {
			if ( ! $definition instanceof Subscribing_Definition_Interface ) {
				continue;
			}

			if ( $definition instanceof Toggling_Definition_Interface ) {
				if ( $definition->is_enabled() ) {
					$this->attach( $definition );
				}
			} else {
				$this->attach( $definition );
			}
		}

		$this->booted = true;
	}

	public function container() {
		return $this->container;
	}

	public function definitions() {
		return $this->definitions;
	}

	public function service( $identifier ) {
		return $this->container[ $identifier ];
	}

	protected function attach( Subscribing_Definition_Interface $definition ) {
		foreach ( $definition->get_subscribed_events() as $args ) {
			$this->on(
				$args[0],
				$definition->get_identifier(),
				$args[1],
				isset( $args[2] ) ? $args[2] : 10,
				isset( $args[3] ) ? $args[3] : 1
			);
		}
	}

	protected function on( $tag, $identifier, $method, $priority = 10, $accepted_args = 1 ) {
		// Because add_action is just an alias of add_filter.
		add_filter( $tag, function() use ( $identifier, $method ) {
			return call_user_func_array(
				[ $this->container[ $identifier ], $method ],
				func_get_args()
			);
		}, $priority, $accepted_args );
	}

	protected function register( Definition_Interface $definition ) {
		$this->definitions[] = $definition;

		// Maybe add ability to define registration strategy (->factory(), ->protect(), ->extend())?
		$this->container[ $definition->get_identifier() ] = $definition->get_value();
	}

	public function is_enabled() {
		return $this->service( 'config' )->is_enabled();
	}

	public function is_collecting_data_always() {
		return $this->service( 'config' )->is_collecting_data_always();
	}

	public function is_collecting_data() {
		return $this->is_enabled() || $this->is_collecting_data_always();
	}

	public function is_web_enabled() {
		return $this->is_enabled() && $this->service( 'config' )->is_web_enabled();
	}

	public function is_definition_registered( $identifier ) {
		foreach ( $this->definitions() as $definition ) {
			if ( $identifier === $definition->get_identifier() ) {
				return true;
			}
		}

		return false;
	}

	public function is_data_source_disabled( $identifier ) {
		if ( ! $this->is_definition_registered( "data_sources.{$identifier}" ) ) {
			return true;
		}

		return in_array(
			$identifier,
			$this->service( 'config' )->get_disabled_data_sources(),
			true
		);
	}

	public function is_data_source_enabled( $identifier ) {
		return ! $this->is_data_source_disabled( $identifier );
	}

	public function is_uri_filtered( $uri ) {
		foreach ( $this->service( 'config' )->get_filtered_uris() as $filtered_uri ) {
			$regex = '#' . str_replace( '#', '\#', $filtered_uri ) . '#';

			if ( preg_match( $regex, $uri ) ) {
				return true;
			}
		}

		return false;
	}
}

<?php

namespace Clockwork_For_Wp;

use ArrayAccess;
use Clockwork_For_Wp\Api\Api_Provider;
use Clockwork_For_Wp\Data_Source\Data_Source_Provider;
use Clockwork_For_Wp\Event_Management\Event_Management_Provider;
use Clockwork_For_Wp\Routing\Routing_Provider;
use Clockwork_For_Wp\Web_App\Web_App_Provider;
use Pimple\Container;

class Plugin implements ArrayAccess {
	protected $container;
	protected $providers = [];
	protected $booted = false;

	public function __construct( array $providers = null, array $values = null ) {
		if ( null === $providers ) {
			$providers = [
				Plugin_Provider::class,
				Wordpress_Provider::class,

				Api_Provider::class,
				Data_Source_Provider::class,
				Event_Management_Provider::class,
				Routing_Provider::class,
				Web_App_Provider::class,

				// We are doing something we realistically shouldn't and resolving some services
				// from within the register method. For this reason the clockwork provider
				// must always be last to ensure all services are already registered.
				Clockwork_Provider::class,
			];
		}

		$this->container = new Container( $values ?: [] );

		foreach ( $providers as $provider ) {
			if ( ! $provider instanceof Provider ) {
				$provider = new $provider( $this );
			}

			$this->register( $provider );
		}
	}

	public function get_container() {
		return $this->container;
	}

	public function boot() {
		if ( $this->booted ) {
			return;
		}

		foreach ( $this->providers as $provider ) {
			$provider->boot();
		}

		$this->booted = true;
	}

	public function register( Provider $provider ) {
		$provider->register();

		$this->providers[ get_class( $provider ) ] = $provider;

		return $this;
	}

	public function config( $path, $default = null ) {
		// @todo Return default when config is not registered in container?
		return $this[ Config::class ]->get( $path, $default );
	}

	public function get_enabled_data_sources() {
		return array_filter( $this->config( 'data_sources', [] ), function( $data_source ) {
			return (bool) $data_source['enabled'];
		} );
	}

	public function is_feature_enabled( $feature ) {
		return $this->config( "data_sources.{$feature}.enabled", false )
			&& $this->is_feature_available( $feature );
	}

	public function is_feature_available( $feature ) {
		// @todo Allow custom conditions to be registered.
		if ( 'wpdb' === $feature ) {
			return defined( 'SAVEQUERIES' ) && SAVEQUERIES;
		} elseif ( 'xdebug' === $feature ) {
			return extension_loaded( 'xdebug' ); // @todo get_loaded_extensions()?
		}

		return true;
	}

	public function is_method_filtered( $method ) {
		$filter_methods = array_map( 'strtoupper', $this->config( 'filter_methods', [] ) );

		return in_array( $method, $filter_methods, true );
	}

	public function is_uri_filtered( $uri ) {
		$filter_uris = $this->config( 'filter_uris', [] );
		$filter_uris[] = '\/__clockwork(?:\/.*)?';

		foreach ( $filter_uris as $filter_uri ) {
			$regex = '#' . str_replace( '#', '\#', $filter_uri ) . '#';

			if ( preg_match( $regex, $uri ) ) {
				return true;
			}
		}

		return false;
	}

	public function is_collecting_data() {
		return $this->is_collecting_requests();
	}

	public function is_collecting_requests() {
		return ( $this->is_enabled() || $this->config( 'collect_data_always', false ) )
			&& ! $this->is_running_in_console()
			&& ! $this->is_method_filtered( $_SERVER['REQUEST_METHOD'] )
			&& ! $this->is_uri_filtered( $_SERVER['REQUEST_URI'] );
	}

	public function is_enabled() {
		return (bool) $this->config( 'enable', true );
	}

	public function is_running_in_console() {
		return ( defined( 'WP_CLI' ) && WP_CLI ) || in_array( PHP_SAPI, [ 'cli', 'phpdbg' ], true );
	}

	public function is_web_enabled() {
		return $this->config( 'enable', true ) && $this->config( 'web', true );
	}

	// @todo extend method?
	public function protect( $callable ) {
		return $this->container->protect( $callable );
	}

	public function factory( $callable ) {
		return $this->container->factory( $callable );
	}

	public function offsetExists( $offset ) {
		return $this->container->offsetExists( $offset );
	}

	public function offsetGet( $offset ) {
		return $this->container->offsetGet( $offset );
	}

	public function offsetSet( $offset, $value ) {
		$this->container->offsetSet( $offset, $value );
	}

	public function offsetUnset( $offset ) {
		$this->container->offsetUnset( $offset );
	}
}

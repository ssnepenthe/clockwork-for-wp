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
				Api_Provider::class,
				Data_Source_Provider::class,
				Event_Management_Provider::class,
				Routing_Provider::class,
				Web_App_Provider::class,
				Plugin_Provider::class,
				Wordpress_Provider::class,
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

	public function is_uri_filtered( $uri ) {
		// @todo Always ensure clockwork uris are filtered.
		foreach ( $this->config( 'filtered_uris', [] ) as $filtered_uri ) {
			$regex = '#' . str_replace( '#', '\#', $filtered_uri ) . '#';

			if ( preg_match( $regex, $uri ) ) {
				return true;
			}
		}

		return false;
	}

	public function is_collecting_data() {
		return $this->config( 'enable', true ) || $this->config( 'collect_data_always', false );
	}

	public function is_enabled() {
		return (bool) $this->config( 'enable', true );
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

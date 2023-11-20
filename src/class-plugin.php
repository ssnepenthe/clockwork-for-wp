<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

use Clockwork\Clockwork;
use Clockwork_For_Wp\Api\Api_Provider;
use Clockwork_For_Wp\Cli_Data_Collection\Cli_Data_Collection_Provider;
use Clockwork_For_Wp\Data_Source\Data_Source_Provider;
use Clockwork_For_Wp\Data_Source\Errors;
use Clockwork_For_Wp\Routing\Routing_Provider;
use Clockwork_For_Wp\Web_App\Web_App_Provider;
use Clockwork_For_Wp\Wp_Cli\Wp_Cli_Provider;
use InvalidArgumentException;
use Pimple\Container;
use RuntimeException;
use WpEventDispatcher\Priority;

/**
 * @internal
 */
final class Plugin {
	private $booted = false;

	private $is;

	private $locked = false;

	private $pimple;

	private $providers = [];

	public function __construct( ?array $providers = null, ?array $values = null ) {
		if ( null === $providers ) {
			$providers = [
				new Clockwork_Provider(),
				new Plugin_Provider(),

				new Api_Provider(),
				new Cli_Data_Collection_Provider(),
				new Data_Source_Provider(),
				new Routing_Provider(),
				new Web_App_Provider(),
				new Wp_Cli_Provider(),
			];
		}

		$this->pimple = new Container( $values ?: [] );
		$this->pimple[ self::class ] = $this;

		foreach ( $providers as $provider ) {
			if ( ! $provider instanceof Provider ) {
				throw new InvalidArgumentException( 'Invalid provider type in plugin constructor' );
			}

			$this->register( $provider );
		}
	}

	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		foreach ( $this->providers as $provider ) {
			$provider->boot( $this );
		}

		$this->booted = true;
	}

	public function get_pimple(): Container {
		return $this->pimple;
	}

	public function is(): Is {
		if ( ! $this->is instanceof Is ) {
			$this->is = new Is(
				$this->pimple[ Read_Only_Configuration::class ],
				$this->pimple[ Clockwork::class ],
				$this->pimple[ Incoming_Request::class ]
			);
		}

		return $this->is;
	}

	// @todo Method name?
	public function lock(): void {
		if ( $this->locked ) {
			return;
		}

		foreach ( $this->providers as $provider ) {
			$provider->registered( $this );
		}

		$this->locked = true;
	}

	public function register( Provider $provider ) {
		if ( $this->locked ) {
			throw new RuntimeException( 'Cannot register providers after plugin has been locked' );
		}

		$provider->register( $this );

		$this->providers[ \get_class( $provider ) ] = $provider;

		return $this;
	}

	public function run(): void {
		// Resolve error handler immediately so we catch as many errors as possible.
		// @todo Move to plugin constructor?
		Errors::get_instance()->register();

		\add_action( 'plugin_loaded', function ( $file ): void {
			if ( $this->pimple[ 'file' ] !== \realpath( $file ) ) {
				return;
			}

			$this->lock();
		}, Priority::EARLY );

		\add_action( 'plugins_loaded', [ $this, 'boot' ], Priority::EARLY );
	}
}

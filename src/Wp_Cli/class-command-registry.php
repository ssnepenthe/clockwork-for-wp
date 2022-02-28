<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use Closure;
use Invoker\InvokerInterface;
use WP_CLI;

/**
 * @internal
 */
final class Command_Registry {
	private $invoker;

	private $namespace = [];

	private $registered_commands = [];

	public function __construct( InvokerInterface $invoker ) {
		$this->invoker = $invoker;
	}

	public function add( Command $command ) {
		if ( ! empty( $this->namespace ) ) {
			$command->set_namespace( \implode( ' ', $this->namespace ) );
		}

		$this->registered_commands[] = $command;

		return $this;
	}

	public function initialize(): void {
		foreach ( $this->registered_commands as $command ) {
			$args = [];

			if ( $shortdesc = $command->get_description() ) {
				$args['shortdesc'] = $shortdesc;
			}

			if ( ! empty( $synopsis = $command->get_synopsis() ) ) {
				$args['synopsis'] = $synopsis;
			}

			if ( $longdesc = $command->get_usage() ) {
				$args['longdesc'] = $longdesc;
			}

			if ( $before_invoke = $command->get_before_invoke_callback() ) {
				$args['before_invoke'] = $this->wrap_callback( $before_invoke );
			}

			if ( $after_invoke = $command->get_after_invoke_callback() ) {
				$args['after_invoke'] = $this->wrap_callback( $after_invoke );
			}

			if ( $when = $command->get_when() ) {
				$args['when'] = $when;
			}

			$handler = $command->get_handler();

			if ( Namespace_Identifier::class !== $handler ) {
				$handler = $this->wrap_command_handler( $command );
			}

			WP_CLI::add_command( $command->get_name(), $handler, $args );
		}
	}

	public function namespace( string $namespace, string $description, callable $callback ): void {
		$command = new Command();
		$command->set_name( $namespace );
		$command->set_handler( Namespace_Identifier::class );
		$command->set_description( $description );

		$this->add( $command );

		$this->namespace[] = $namespace;

		$callback( $this );

		\array_pop( $this->namespace );
	}

	private function snake_case( string $string ): string {
		return \mb_strtolower( \str_replace( '-', '_', $string ) );
	}

	private function wrap_callback( $callback ): Closure {
		return function () use ( $callback ) {
			return $this->invoker->call( $callback );
		};
	}

	private function wrap_command_handler( Command $command ): Closure {
		return function ( $args, $assoc_args ) use ( $command ) {
			$parameters = [
				'args' => $args,
				'assoc_args' => $assoc_args,
			];

			$registered_args = $command->get_arguments();

			while ( \count( $args ) ) {
				$current = \array_shift( $registered_args );

				$name = $current->get_name();
				$snake_name = $this->snake_case( $name );

				if ( $current->get_repeating() ) {
					$parameters[ $snake_name ] = $args;

					$args = [];
				} else {
					$arg = \array_shift( $args );

					$parameters[ $snake_name ] = $arg;
				}
			}

			foreach ( $command->get_options() as $option ) {
				$name = $option->get_name();
				$snake_name = $this->snake_case( $name );

				if ( \array_key_exists( $name, $assoc_args ) ) {
					$parameters[ $snake_name ] = $assoc_args[ $name ];

					unset( $assoc_args[ $name ] );
				} elseif ( $option instanceof Flag ) {
					$parameters[ $snake_name ] = false;
				}
			}

			if ( $command->get_accept_arbitrary_options() && ! empty( $assoc_args ) ) {
				$parameters['arbitrary_options'] = $assoc_args;
			}

			return $this->invoker->call( $command->get_handler(), $parameters );
		};
	}
}

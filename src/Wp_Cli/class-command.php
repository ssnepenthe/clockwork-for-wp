<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use InvalidArgumentException;
use RuntimeException;

class Command {
	protected $accept_arbitrary_options = false;

	protected $after_invoke_callback;

	protected $arguments = [];

	protected $before_invoke_callback;

	protected $description;

	protected $handler;

	protected $name;

	protected $namespace;

	protected $options = [];

	protected $usage;

	protected $when;

	public function __construct() {
		$this->configure();
	}

	public function add_argument( Argument $argument ) {
		$last_argument = \end( $this->arguments );

		if ( $last_argument instanceof Argument ) {
			if ( $last_argument->get_repeating() ) {
				throw new RuntimeException(
					'Cannot register additional arguments after a repeating argument'
				);
			}

			// Required arguments should never come after optional arguments.
			if ( $last_argument->get_optional() && ! $argument->get_optional() ) {
				throw new RuntimeException(
					'Cannot register required argument after an optional argument'
				);
			}
		}

		$this->arguments[ $argument->get_name() ] = $argument;

		return $this;
	}

	public function add_flag( Flag $flag ) {
		$this->options[ $flag->get_name() ] = $flag;

		return $this;
	}

	public function add_option( Option $option ) {
		$this->options[ $option->get_name() ] = $option;

		return $this;
	}

	public function get_accept_arbitrary_options(): bool {
		return $this->accept_arbitrary_options;
	}

	public function get_after_invoke_callback() {
		if ( null === $this->after_invoke_callback && \method_exists( $this, 'after_invoke' ) ) {
			return [ $this, 'after_invoke' ];
		}

		return $this->after_invoke_callback;
	}

	public function get_arguments(): array {
		return $this->arguments;
	}

	public function get_before_invoke_callback() {
		if ( null === $this->before_invoke_callback && \method_exists( $this, 'before_invoke' ) ) {
			return [ $this, 'before_invoke' ];
		}

		return $this->before_invoke_callback;
	}

	public function get_description(): ?string {
		return $this->description;
	}

	public function get_handler() {
		if ( null !== $this->handler ) {
			return $this->handler;
		}

		if ( \method_exists( $this, 'handle' ) ) {
			return [ $this, 'handle' ];
		}

		throw new RuntimeException(
			"Handler not set for command '{$this->get_name()}'"
			. ' - set explicitly using the \$command->setHandler() method'
			. ' or implicitly by implementing the \'handle\' method on your command class'
		);
	}

	public function get_name(): string {
		if ( ! \is_string( $this->name ) || '' === $this->name ) {
			throw new InvalidArgumentException( 'Command name must be non-empty string' );
		}

		return $this->namespace ? "{$this->namespace} {$this->name}" : $this->name;
	}

	public function get_options(): array {
		return $this->options;
	}

	public function get_synopsis(): array {
		$arguments = \array_map( static function ( $argument ) {
			return $argument->get_synopsis();
		}, $this->arguments );

		$options = \array_map( static function ( $option ) {
			return $option->get_synopsis();
		}, $this->options );

		$synopsis = \array_merge( $arguments, $options );

		if ( $this->accept_arbitrary_options ) {
			$synopsis[] = [
				'optional' => true,
				'repeating' => false,
				'type' => 'generic',
			];
		}

		return $synopsis;
	}

	public function get_usage(): ?string {
		return $this->usage;
	}

	public function get_when(): ?string {
		return $this->when;
	}

	public function set_accept_arbitrary_options() {
		$this->accept_arbitrary_options = true;

		return $this;
	}

	public function set_after_invoke_callback( $after_invoke_callback ) {
		$this->after_invoke_callback = $after_invoke_callback;

		return $this;
	}

	public function set_before_invoke_callback( $before_invoke_callback ) {
		$this->before_invoke_callback = $before_invoke_callback;

		return $this;
	}

	public function set_description( string $description ) {
		$this->description = $description;

		return $this;
	}

	public function set_handler( $handler ) {
		$this->handler = $handler;

		return $this;
	}

	public function set_name( string $name ) {
		$this->name = $name;

		return $this;
	}

	public function set_namespace( string $namespace ) {
		$this->namespace = $namespace;

		return $this;
	}

	public function set_usage( string $usage ) {
		$this->usage = $usage;

		return $this;
	}

	public function set_when( string $when ) {
		$this->when = $when;

		return $this;
	}

	protected function configure(): void {
		// Nothing by default...
	}
}

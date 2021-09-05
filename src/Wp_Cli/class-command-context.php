<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use WP_CLI;
use WP_CLI\DocParser;
use WP_CLI\SynopsisParser;

final class Command_Context {
	private $args;
	private $command;
	private $command_path;
	private $options;
	private $parser;
	private $synopsis;

	public function __construct( $command, $args, $options, $command_path ) {
		$this->command = $command;
		$this->args = $args;
		$this->options = $options;
		$this->command_path = $command_path;

		$this->synopsis = SynopsisParser::parse( $this->command->get_synopsis() );
		$this->parser = new DocParser( $this->mock_doc() );
	}

	// @todo What about "unknown" param type?
	// @todo What about repeating arguments?
	public function arguments() {
		$arguments = [];
		$i = 0;

		foreach ( $this->get_params( ['positional'] ) as $arg ) {
			if ( isset( $this->args[ $i ] ) ) {
				$arguments[ $arg['name'] ] = $this->args[ $i ];
			}

			$i++;
		}

		return $arguments;
	}

	public function default_arguments() {
		$args_with_defaults = [];

		foreach ( $this->get_params( ['positional'] ) as $param ) {
			$param_args = $this->parser->get_arg_args( $param['name'] );

			if ( ! isset( $param_args['default'] ) ) {
				continue;
			}

			$args_with_defaults[ $param['name'] ] = $param_args['default'];
		}

		return $args_with_defaults;
	}

	public function default_options() {
		// @todo Should we also get WP-CLI global defaults? i.e. Runner::$config or Runner::$runtime_config.
		// If added, we should unset any from our options that came from defaults.
		$options_with_defaults = [];

		foreach ( $this->get_params( ['assoc', 'flag'] ) as $param ) {
			$param_args = $this->parser->get_param_args( $param['name'] );

			if ( ! isset( $param_args['default'] ) ) {
				continue;
			}

			$options_with_defaults[ $param['name'] ] = $param_args['default'];
		}

		return $options_with_defaults;
	}

	public function get_params( $types ) {
		return \array_filter( $this->synopsis, static function ( $param ) use ( $types ) {
			return \in_array( $param['type'], $types, true );
		} );
	}

	public function name() {
		return \implode( ' ', $this->command_path );
	}

	// @todo Verify how "generic" options are handled... I think it should be fine.
	public function options() {
		return $this->options;
	}

	public function output() {
		$logger = Cli_Collection_Helper::get_plugin_logger();
		// We are flushing buffers in the "wp_loaded" hook at priority 999.
		// $logger->ob_end();

		// @todo Need to look in to coloring in clockwork UI.
		// @todo Better formatting.
		return \trim( $logger->stdout . \PHP_EOL . \PHP_EOL . $logger->stderr );
	}

	private function mock_doc() {
		$mock_doc = [ $this->command->get_shortdesc(), '' ];
		$mock_doc = \array_merge( $mock_doc, \explode( "\n", $this->command->get_longdesc() ) );

		return '/**' . \PHP_EOL . '* ' . \implode( \PHP_EOL . '* ', $mock_doc ) . \PHP_EOL . '*/';
	}

	public static function current() {
		$runner = WP_CLI::get_runner();

		$command = $runner->find_command_to_run( $runner->arguments );

		if ( ! \is_array( $command ) ) {
			return;
		}

		$global = \_cfw_instance()->config( 'wp_cli.record_global_parameters', false );
		$global_runtime = \_cfw_instance()->config(
			'wp_cli.record_global_runtime_parameters',
			true
		);
		$global_filter = static function ( $option ) {
			return null !== $option && '' !== $option && [] !== $option;
		};

		$options = $runner->assoc_args;

		if ( $global ) {
			$options = \array_merge( \array_filter( $runner->config, $global_filter ), $options );
		} elseif ( $global_runtime ) {
			$options = \array_merge(
				\array_filter( $runner->runtime_config, $global_filter ),
				$options
			);
		}

		return new self( $command[0], $command[1], $options, $command[2] );
	}
}

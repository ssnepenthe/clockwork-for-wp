<?php

namespace Clockwork_For_Wp\Wp_Cli;

use WP_CLI;
use WP_CLI\DocParser;
use WP_CLI\SynopsisParser;

class Command_Context {
	protected $command;
	protected $args;
	protected $command_path;

	protected $synopsis;
	protected $parser;

	public function __construct( $command, $args, $command_path ) {
		$this->command = $command;
		$this->args = $args;
		$this->command_path = $command_path;

		$this->synopsis = SynopsisParser::parse( $this->command->get_synopsis() );
		$this->parser = new DocParser( $this->mock_doc() );
	}

	public static function current() {
		$runner = WP_CLI::get_runner();

		$command = $runner->find_command_to_run( $runner->arguments );

		if ( ! is_array( $command ) ) {
			return null;
		}

		return new static( ...$command );
	}

	public function get_params( $types ) {
		return array_filter( $this->synopsis, function( $param ) use ( $types ) {
			return in_array( $param['type'], $types, true );
		} );
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

	// @todo Verify how "generic" options are handled... I think it should be fine.
	public function options() {
		return WP_CLI::get_runner()->assoc_args;
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

	public function name() {
		return implode( ' ', $this->command_path );
	}

	public function output() {
		$logger = Cli_Collection_Helper::get_plugin_logger();
		// We are flushing buffers in the "wp_loaded" hook at priority 999.
		// $logger->ob_end();

		// @todo Need to look in to coloring in clockwork UI.
		// @todo Better formatting.
		return trim( $logger->stdout . PHP_EOL . PHP_EOL . $logger->stderr );
	}

	protected function mock_doc() {
		$mock_doc = [ $this->command->get_shortdesc(), '' ];
		$mock_doc = array_merge( $mock_doc, explode( "\n", $this->command->get_longdesc() ) );
		$mock_doc = '/**' . PHP_EOL . '* ' . implode( PHP_EOL . '* ', $mock_doc ) . PHP_EOL . '*/';

		return $mock_doc;
	}
}

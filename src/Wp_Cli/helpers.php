<?php

declare(strict_types=1);

namespace Clockwork_For_Wp\Wp_Cli;

use Invoker\Invoker;
use WP_CLI;

function add_command( $command ): void {
	static $namespace;

	if ( ! \defined( 'WP_CLI' ) || ! WP_CLI || ! \class_exists( 'WP_CLI' ) ) {
		return;
	}

	if ( ! $namespace ) {
		$namespace = 'clockwork';

		/**
		 * @psalm-suppress TooManyArguments
		 *
		 * @see https://github.com/humanmade/psalm-plugin-wordpress/issues/14
		 */
		WP_CLI::add_command(
			$namespace,
			Command_Namespace::class,
			[
				'shortdesc' => 'Manages the Clockwork for WP plugin.',
			]
		);
	}

	/**
	 * @psalm-suppress TooManyArguments
	 *
	 * @see https://github.com/humanmade/psalm-plugin-wordpress/issues/14
	 */
	WP_CLI::add_command(
		"{$namespace} {$command->name()}",
		static function ( $args, $assoc_args ) use ( $command ): void {
			$parameters = [
				'args' => $args,
				// 'arguments' => $args,
				'assoc_args' => $assoc_args,
				'opts' => $assoc_args,
				// 'options' => $assoc_args,
			];

			foreach ( $command->arguments() as $i => $synopsis ) {
				if ( isset( $args[ $i ] ) ) {
					$parameters[ $synopsis['name'] ] = $args[ $i ];
				}
			}

			foreach ( $command->options() as $synopsis ) {
				if ( isset( $assoc_args[ $synopsis['name'] ] ) ) {
					$parameters[ $synopsis['name'] ] = $assoc_args[ $synopsis['name'] ];
				} elseif ( 'flag' === $synopsis['type'] ) {
					$parameters[ $synopsis['name'] ] = false;
				}
			}

			\_cfw_instance()[ Invoker::class ]->call( $command, $parameters );
		},
		[
			'shortdesc' => $command->shortdesc(),
			'longdesc' => $command->longdesc(),
			'synopsis' => $command->synopsis(),
		]
	);
}

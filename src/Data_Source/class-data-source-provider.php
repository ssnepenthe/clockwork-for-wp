<?php

namespace Clockwork_For_Wp\Data_Source;

use Clockwork_For_Wp\Base_Provider;
use Clockwork_For_Wp\Config;

class Data_Source_Provider extends Base_Provider {
	public function register() {
		$this->plugin[ Conditionals::class ] = function() {
			$conditionals = [
				'is_404',
				'is_admin',
				'is_archive',
				'is_attachment',
				'is_author',
				'is_blog_admin',
				'is_category',
				'is_comment_feed',
				'is_customize_preview',
				'is_date',
				'is_day',
				'is_embed',
				'is_feed',
				'is_front_page',
				'is_home',
				'is_month',
				'is_network_admin',
				'is_page',
				'is_page_template',
				'is_paged',
				'is_post_type_archive',
				'is_preview',
				'is_robots',
				'is_rtl',
				'is_search',
				'is_single',
				'is_singular',
				'is_ssl',
				'is_sticky', // @todo???
				'is_tag',
				'is_tax',
				'is_time',
				'is_trackback',
				'is_user_admin',
				'is_year',
			];

			if ( is_multisite() ) {
				$conditionals[] = 'is_main_network';
				$conditionals[] = 'is_main_site';
			}

			return new Conditionals( ...$conditionals );
		};

		$this->plugin[ Constants::class ] = function() {
			$constants = [
				'WP_DEBUG',
				'WP_DEBUG_DISPLAY',
				'WP_DEBUG_LOG',
				'SCRIPT_DEBUG',
				'WP_CACHE',
				'CONCATENATE_SCRIPTS',
				'COMPRESS_SCRIPTS',
				'COMPRESS_CSS',
				'WP_LOCAL_DEV',
			];

			if ( is_multisite() ) {
				$constants[] = 'SUNRISE';
			}

			return new Constants( ...$constants );
		};

		$this->plugin[ Core::class ] = function() {
			return new Core( $this->plugin['wp_version'], $this->plugin['timestart'] );
		};

		$this->plugin[ Errors::class ] = function() {
			// If display_errors is set, output can potentially prevent necessary headers
			// from being sent. For this reason we will prevent errors from being
			// displayed immediately, outputting them manually on shutdown.
			$display = (bool) ini_get( 'display_errors' );

			if ( $display ) {
				ini_set( 'display_errors', 0 );
			}

			return new Errors( $display, \error_reporting() );
		};

		$this->plugin[ Php::class ] = function() {
			$cookies = implode( '|', [ AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE ] );

			// @todo Option in plugin config for additional patterns?
			return new Php( '/pass|pwd/i', "/{$cookies}/i" );
		};

		$this->plugin[ Rest_Api::class ] = function() {
			return new Rest_Api();
		};

		$this->plugin[ Theme::class ] = function() {
			return new Theme();
		};

		$this->plugin[ Transients::class ] = function() {
			return new Transients();
		};

		$this->plugin[ Wp_Hook::class ] = function() {
			$config = $this->plugin[ Config::class ]->get( 'data_sources.wp_hook.config', [] );

			return new Wp_Hook(
				$config['except_tags'] ?? [],
				$config['only_tags'] ?? [],
				$config['except_callbacks'] ?? [],
				$config['only_callbacks'] ?? []
			);
		};

		$this->plugin[ Wp_Http::class ] = function() {
			return new Wp_Http();
		};

		$this->plugin[ Wp_Mail::class ] = function() {
			return new Wp_Mail();
		};

		$this->plugin[ Wp_Object_Cache::class ] = function() {
			return new Wp_Object_Cache();
		};

		$this->plugin[ Wp_Query::class ] = function() {
			return new Wp_Query();
		};

		$this->plugin[ Wp_Rewrite::class ] = function() {
			return new Wp_Rewrite();
		};

		$this->plugin[ Wp::class ] = function() {
			return new Wp();
		};

		$this->plugin[ Wpdb::class ] = function() {
			$config = $this->plugin[ Config::class ]->get( 'data_sources.wpdb.config', [] );

			return new Wpdb(
				$config['detect_duplicate_queries'] ?? false,
				$config['slow_only'] ?? false,
				$config['slow_threshold'] ?? 50
			);
		};

		$this->plugin[ Xdebug::class ] = function() {
			return new Xdebug();
		};
	}

	protected function subscribers() : array {
		$subscribers = [];

		if ( $this->plugin->is_feature_enabled( 'errors' ) ) {
			$subscribers[] = Errors::class;
		}

		if ( $this->plugin->is_feature_enabled( 'rest_api' ) ) {
			$subscribers[] = Rest_Api::class;
		}

		if ( $this->plugin->is_feature_enabled( 'theme' ) ) {
			$subscribers[] = Theme::class;
		}

		if ( $this->plugin->is_feature_enabled( 'transients' ) ) {
			$subscribers[] = Transients::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_hook' ) ) {
			$subscribers[] = Wp_Hook::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_http' ) ) {
			$subscribers[] = Wp_Http::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_mail' ) ) {
			$subscribers[] = Wp_Mail::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_object_cache' ) ) {
			$subscribers[] = Wp_Object_Cache::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_query' ) ) {
			$subscribers[] = Wp_Query::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp_rewrite' ) ) {
			$subscribers[] = Wp_Rewrite::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wp' ) ) {
			$subscribers[] = Wp::class;
		}

		if ( $this->plugin->is_feature_enabled( 'wpdb' ) ) {
			$subscribers[] = Wpdb::class;
		}

		if ( $this->plugin->is_feature_enabled( 'xdebug' ) ) {
			$subscribers[] = Xdebug::class;
		}

		return $subscribers;
	}
}

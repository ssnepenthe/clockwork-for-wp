<?php

namespace Clockwork_For_Wp;

class Included_Files {
	public static function all() {
		return \get_included_files();
	}

	public static function filtered( $callback ) {
		return \array_filter( static::all(), $callback );
	}

	public static function from_child_theme() {
		if ( ! \is_child_theme() ) {
			return [];
		}

		return static::filtered( static::in_child_theme_dir_callback() );
	}

	public static function from_parent_theme() {
		return static::filtered( static::in_parent_theme_dir_callback() );
	}

	public static function template_parts_from_child_theme() {
		if ( ! \is_child_theme() ) {
			return [];
		}

		return static::filtered( function( $file_path ) {
			return static::in_child_theme_dir_callback()( $file_path )
				&& static::is_included_template_part_callback()( $file_path );
		} );
	}

	public static function template_parts_from_parent_theme() {
		return static::filtered( function( $file_path ) {
			return static::in_parent_theme_dir_callback()( $file_path )
				&& static::is_included_template_part_callback()( $file_path );
		} );
	}

	protected static function in_child_theme_dir_callback() {
		return function( $file_path ) {
			return 0 === \strpos( $file_path, \get_stylesheet_directory() );
		};
	}

	protected static function in_parent_theme_dir_callback() {
		return function( $file_path ) {
			return 0 === \strpos( $file_path, \get_template_directory() );
		};
	}

	protected static function is_included_template_part_callback() {
		return function( $file_path ) {
			$relative = \str_replace(
				[ \get_template_directory(), \get_stylesheet_directory() ],
				'',
				$file_path
			);
			$slug = \ltrim( \str_replace( '.php', '', $relative ), '/' );

			if ( \did_action( "get_template_part_{$slug}" ) ) {
				return true;
			} else {
				$slug = \preg_replace( '/\-[^\-]+$/', '', $slug );

				if ( \did_action( "get_template_part_{$slug}" ) ) {
					return true;
				}
			}

			return false;
		};
	}
}

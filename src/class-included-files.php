<?php

declare(strict_types=1);

namespace Clockwork_For_Wp;

final class Included_Files {
	public static function all() {
		return \get_included_files();
	}

	public static function filtered( $callback ) {
		return \array_filter( self::all(), $callback );
	}

	public static function from_child_theme() {
		if ( ! \is_child_theme() ) {
			return [];
		}

		return self::filtered( self::in_child_theme_dir_callback() );
	}

	public static function from_parent_theme() {
		return self::filtered( self::in_parent_theme_dir_callback() );
	}

	public static function template_parts_from_child_theme() {
		if ( ! \is_child_theme() ) {
			return [];
		}

		return self::filtered( static function ( $file_path ) {
			return self::in_child_theme_dir_callback()( $file_path )
				&& self::is_included_template_part_callback()( $file_path );
		} );
	}

	public static function template_parts_from_parent_theme() {
		return self::filtered( static function ( $file_path ) {
			return self::in_parent_theme_dir_callback()( $file_path )
				&& self::is_included_template_part_callback()( $file_path );
		} );
	}

	private static function in_child_theme_dir_callback() {
		return static function ( $file_path ) {
			return 0 === \mb_strpos( $file_path, \get_stylesheet_directory() );
		};
	}

	private static function in_parent_theme_dir_callback() {
		return static function ( $file_path ) {
			return 0 === \mb_strpos( $file_path, \get_template_directory() );
		};
	}

	private static function is_included_template_part_callback() {
		return static function ( $file_path ) {
			$relative = \str_replace(
				[ \get_template_directory(), \get_stylesheet_directory() ],
				'',
				$file_path
			);
			$slug = \ltrim( \str_replace( '.php', '', $relative ), '/' );

			if ( \did_action( "get_template_part_{$slug}" ) ) {
				return true;
			}
			$slug = \preg_replace( '/\-[^\-]+$/', '', $slug );

			return (bool) ( \did_action( "get_template_part_{$slug}" ) );
		};
	}
}

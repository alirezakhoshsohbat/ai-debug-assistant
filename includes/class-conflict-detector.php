<?php

namespace AIDA\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Conflict Detector Class
 */
class Conflict_Detector {

	/**
	 * Run all conflict checks.
	 */
	public static function detect() {
		return array(
			'hooks'   => self::check_hook_conflicts(),
			'scripts' => self::check_script_duplicates(),
			'styles'  => self::check_style_duplicates(),
			'jquery'  => self::check_jquery_conflicts(),
			'rest'    => self::check_rest_route_conflicts(),
		);
	}

	/**
	 * Check for multiple plugins modifying critical hooks.
	 */
	private static function check_hook_conflicts() {
		global $wp_filter;
		$critical_hooks = array( 'template_redirect', 'init', 'wp_head', 'wp_footer', 'the_content', 'woocommerce_checkout_process' );
		$conflicts      = array();

		foreach ( $critical_hooks as $hook ) {
			if ( isset( $wp_filter[ $hook ] ) ) {
				$sources = array();
				foreach ( $wp_filter[ $hook ]->callbacks as $priority => $functions ) {
					foreach ( $functions as $id => $callback ) {
						$source = self::get_callback_source( $callback['function'] );
						if ( $source ) {
							$sources[] = $source;
						}
					}
				}

				$source_counts = array_count_values( $sources );
				if ( count( $source_counts ) > 3 ) { // More than 3 different plugins/themes on one hook
					$conflicts[] = array(
						'hook'      => $hook,
						'sources'   => $source_counts,
						'risk'      => 'متوسط',
						'message'   => sprintf( 'تعداد %d منبع مختلف روی هوک %s شناسایی شد که ممکن است باعث تداخل شود.', count( $source_counts ), $hook ),
					);
				}
			}
		}

		return $conflicts;
	}

	/**
	 * Check for duplicate scripts or multiple versions.
	 */
	private static function check_script_duplicates() {
		global $wp_scripts;
		if ( ! $wp_scripts ) return array();

		$conflicts = array();
		$seen_names = array();

		foreach ( $wp_scripts->queue as $handle ) {
			$obj = $wp_scripts->registered[ $handle ];
			$filename = basename( strtok( $obj->src, '?' ) );
			
			if ( isset( $seen_names[ $filename ] ) ) {
				$conflicts[] = array(
					'handle'  => $handle,
					'src'     => $obj->src,
					'risk'    => 'زیاد',
					'message' => sprintf( 'فایل اسکریپت تکراری (%s) توسط دو هندل متفاوت (%s و %s) بارگذاری شده است.', $filename, $handle, $seen_names[ $filename ] ),
				);
			}
			$seen_names[ $filename ] = $handle;
		}

		return $conflicts;
	}

	/**
	 * Check for duplicate styles.
	 */
	private static function check_style_duplicates() {
		global $wp_styles;
		if ( ! $wp_styles ) return array();

		$conflicts = array();
		$seen_names = array();

		foreach ( $wp_styles->queue as $handle ) {
			if ( ! isset( $wp_styles->registered[ $handle ] ) ) continue;
			$obj = $wp_styles->registered[ $handle ];
			$filename = basename( strtok( $obj->src, '?' ) );
			
			if ( isset( $seen_names[ $filename ] ) ) {
				$conflicts[] = array(
					'handle'  => $handle,
					'src'     => $obj->src,
					'risk'    => 'متوسط',
					'message' => sprintf( 'فایل استایل تکراری (%s) شناسایی شد.', $filename ),
				);
			}
			$seen_names[ $filename ] = $handle;
		}

		return $conflicts;
	}

	/**
	 * Specifically check for jQuery conflicts (multiple versions).
	 */
	private static function check_jquery_conflicts() {
		global $wp_scripts;
		if ( ! $wp_scripts ) return array();

		$jquery_handles = array();
		foreach ( $wp_scripts->queue as $handle ) {
			if ( stripos( $handle, 'jquery' ) !== false && $handle !== 'jquery-core' && $handle !== 'jquery-migrate' ) {
				$jquery_handles[] = $handle;
			}
		}

		if ( count( $jquery_handles ) > 0 ) {
			return array(
				array(
					'risk'    => 'بحرانی',
					'handles' => $jquery_handles,
					'message' => 'نسخه‌های متعددی از jQuery یا افزونه‌های آن شناسایی شد. این موضوع معمولاً باعث از کار افتادن کدهای جاوا اسکریپت می‌شود.',
				)
			);
		}

		return array();
	}

	/**
	 * Check for duplicate REST routes.
	 */
	private static function check_rest_route_conflicts() {
		$server = rest_get_server();
		$routes = $server->get_routes();
		$conflicts = array();

		foreach ( $routes as $route => $handlers ) {
			if ( count( $handlers ) > 1 ) {
				// Multiple handlers for the same route might be a conflict
				$conflicts[] = array(
					'route' => $route,
					'risk'  => 'متوسط',
					'count' => count( $handlers ),
					'message' => sprintf( 'مسیر REST (%s) دارای %d هندلر مختلف است.', $route, count( $handlers ) ),
				);
			}
		}

		return $conflicts;
	}

	/**
	 * Try to identify the plugin or theme from a callback.
	 */
	private static function get_callback_source( $callback ) {
		if ( is_string( $callback ) ) {
			if ( strpos( $callback, '::' ) !== false ) {
				$callback = explode( '::', $callback );
			} else {
				$ref = new \ReflectionFunction( $callback );
				return self::path_to_source( $ref->getFileName() );
			}
		}

		if ( is_array( $callback ) ) {
			$ref = new \ReflectionMethod( $callback[0], $callback[1] );
			return self::path_to_source( $ref->getFileName() );
		}

		if ( is_object( $callback ) && ! ( $callback instanceof \Closure ) ) {
			$ref = new \ReflectionObject( $callback );
			return self::path_to_source( $ref->getFileName() );
		}

		return 'Unknown';
	}

	private static function path_to_source( $path ) {
		if ( ! $path ) return false;
		$path = str_replace( '\\', '/', $path );
		
		if ( strpos( $path, 'wp-content/plugins/' ) !== false ) {
			$parts = explode( 'wp-content/plugins/', $path );
			$subparts = explode( '/', $parts[1] );
			return 'Plugin: ' . $subparts[0];
		}

		if ( strpos( $path, 'wp-content/themes/' ) !== false ) {
			$parts = explode( 'wp-content/themes/', $path );
			$subparts = explode( '/', $parts[1] );
			return 'Theme: ' . $subparts[0];
		}

		return 'WordPress Core';
	}
}

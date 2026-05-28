<?php

namespace AIDA\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Config Manager Class
 * Handles modifications to wp-config.php
 */
class Config_Manager {

	/**
	 * Get the path to wp-config.php
	 */
	public static function get_config_path() {
		$path = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $path ) ) {
			// Check one level up (standard for some WP installs)
			$path = dirname( ABSPATH ) . '/wp-config.php';
		}
		return $path;
	}

	/**
	 * Check if WP_DEBUG is enabled
	 */
	public static function is_debug_enabled() {
		return defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * Update WP_DEBUG in wp-config.php
	 * 
	 * @param bool $enabled
	 * @return bool Success or failure
	 */
	public static function update_debug_mode( $enabled ) {
		$config_path = self::get_config_path();
		
		if ( ! is_writable( $config_path ) ) {
			return false;
		}

		$content = file_get_contents( $config_path );
		$value   = $enabled ? 'true' : 'false';

		// Regex to find define('WP_DEBUG', ...) or define("WP_DEBUG", ...)
		$patterns = array(
			'WP_DEBUG'         => "/define\(\s*['\"]WP_DEBUG['\"]\s*,\s*[^)]+\s*\);/i",
			'WP_DEBUG_LOG'     => "/define\(\s*['\"]WP_DEBUG_LOG['\"]\s*,\s*[^)]+\s*\);/i",
			'WP_DEBUG_DISPLAY' => "/define\(\s*['\"]WP_DEBUG_DISPLAY['\"]\s*,\s*[^)]+\s*\);/i",
		);

		$replacements = array(
			'WP_DEBUG'         => "define('WP_DEBUG', $value);",
			'WP_DEBUG_LOG'     => "define('WP_DEBUG_LOG', $value);",
			'WP_DEBUG_DISPLAY' => "define('WP_DEBUG_DISPLAY', false);", // Always false for security/cleanliness
		);

		foreach ( $patterns as $constant => $pattern ) {
			if ( preg_match( $pattern, $content ) ) {
				$content = preg_replace( $pattern, $replacements[$constant], $content );
			} else {
				// If not found, add it before "stop editing" line
				$stop_line = "/* That's all, stop editing!";
				if ( strpos( $content, $stop_line ) !== false ) {
					$content = str_replace( $stop_line, $replacements[$constant] . "\n" . $stop_line, $content );
				} else {
					$content .= "\n" . $replacements[$constant];
				}
			}
		}

		return file_put_contents( $config_path, $content ) !== false;
	}
}

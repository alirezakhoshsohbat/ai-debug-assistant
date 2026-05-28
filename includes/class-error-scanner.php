<?php

namespace AIDA\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Error Scanner Class
 */
class Error_Scanner {

	/**
	 * Scan for errors in debug.log.
	 * 
	 * @param array $args {
	 *     Optional. Arguments to filter/sort errors.
	 *     @type string $orderby  Severity or Date.
	 *     @type string $order    ASC or DESC.
	 *     @type int    $limit    Number of errors to return.
	 *     @type int    $offset   Offset for pagination.
	 * }
	 */
	public static function scan( $args = array() ) {
		$defaults = array(
			'orderby' => 'timestamp',
			'order'   => 'DESC',
			'limit'   => 20,
			'offset'  => 0,
			'filter_severity' => '',
		);
		$args = wp_parse_args( $args, $defaults );

		$log_file = WP_CONTENT_DIR . '/debug.log';
		$errors   = array();

		if ( file_exists( $log_file ) && is_readable( $log_file ) ) {
			// Read a larger chunk to allow better filtering/sorting
			$lines = self::read_last_lines( $log_file, 500 );
			foreach ( $lines as $line ) {
				$parsed = self::parse_error_line( $line );
				if ( $parsed ) {
					if ( ! empty( $args['filter_severity'] ) && $parsed['severity'] !== $args['filter_severity'] ) {
						continue;
					}
					$errors[] = $parsed;
				}
			}
		}

		// Sort
		usort( $errors, function( $a, $b ) use ( $args ) {
			$val_a = $a[$args['orderby']];
			$val_b = $b[$args['orderby']];

			if ( $args['orderby'] === 'severity' ) {
				$priority = array( 'بحرانی' => 3, 'متوسط' => 2, 'کم' => 1 );
				$val_a = isset( $priority[$val_a] ) ? $priority[$val_a] : 0;
				$val_b = isset( $priority[$val_b] ) ? $priority[$val_b] : 0;
			}

			if ( $val_a == $val_b ) return 0;

			if ( strtoupper( $args['order'] ) === 'DESC' ) {
				return ( $val_a > $val_b ) ? -1 : 1;
			} else {
				return ( $val_a < $val_b ) ? -1 : 1;
			}
		} );

		return array(
			'total' => count( $errors ),
			'items' => array_slice( $errors, $args['offset'], $args['limit'] )
		);
	}

	/**
	 * Read last N lines of a file efficiently.
	 */
	private static function read_last_lines( $filename, $lines = 100 ) {
		if ( ! file_exists( $filename ) ) return array();
		
		$file = fopen( $filename, 'rb' );
		if ( ! $file ) return array();

		$line_count = 0;
		$pos = -1;
		$data = '';

		while ( $line_count < $lines && fseek( $file, $pos, SEEK_END ) !== -1 ) {
			$char = fgetc( $file );
			if ( $char === "\n" ) {
				$line_count++;
			}
			$data = $char . $data;
			$pos--;
		}

		fclose( $file );
		return array_filter( explode( "\n", $data ) );
	}

	/**
	 * Parse error line to extract severity, message, file, and line.
	 */
	private static function parse_error_line( $line ) {
		if ( empty( $line ) ) return false;

		// Extract timestamp: [24-May-2026 12:34:56 UTC]
		$timestamp = '';
		if ( preg_match( '/^\[([^\]]+)\]\s*(.*)/', $line, $matches ) ) {
			$timestamp = $matches[1];
			$message_part = $matches[2];
		} else {
			$message_part = $line;
		}

		$severity = 'کم'; // Default: Low
		$type = 'Notice';
		
		if ( stripos( $message_part, 'PHP Fatal error' ) !== false || stripos( $message_part, 'PHP Parse error' ) !== false ) {
			$severity = 'بحرانی';
			$type = 'Fatal Error';
		} elseif ( stripos( $message_part, 'PHP Warning' ) !== false ) {
			$severity = 'متوسط';
			$type = 'Warning';
		} elseif ( stripos( $message_part, 'PHP Notice' ) !== false ) {
			$severity = 'کم';
			$type = 'Notice';
		} elseif ( stripos( $message_part, 'PHP Deprecated' ) !== false ) {
			$severity = 'کم';
			$type = 'Deprecated';
		}

		// Try to extract file and line
		$file = '';
		$line_num = '';
		if ( preg_match( '/in (.*) on line (\d+)/', $message_part, $matches ) ) {
			$file = $matches[1];
			$line_num = $matches[2];
			// Clean up message
			$message_part = str_replace( $matches[0], '', $message_part );
		}

		return array(
			'raw'       => $line,
			'message'   => trim( $message_part ),
			'severity'  => $severity,
			'type'      => $type,
			'timestamp' => $timestamp,
			'file'      => $file,
			'line'      => $line_num,
		);
	}

	/**
	 * Extract timestamp from log line.
	 */
	private static function extract_timestamp( $line ) {
		if ( preg_match( '/^\[([^\]]+)\]/', $line, $matches ) ) {
			return $matches[1];
		}
		return '';
	}
}

<?php
/**
 * Simple file-based logger for SATORI Report Logs.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Logging;

/**
 * Logger utility.
 */
class Logger {

	/**
	 * Write a log entry to the plugin log file.
	 *
	 * @param string $level   Log level (debug, info, warning, error).
	 * @param string $message Log message.
	 * @param array  $context Optional context replacements.
	 * @return void
	 */
	public static function log( $level, $message, array $context = array() ) {
		$level = strtolower( trim( (string) $level ) );
		if ( 'debug' === $level && ! self::is_debug_enabled() ) {
			return;
		}
		
		$uploads = wp_upload_dir();
		if ( empty( $uploads['basedir'] ) ) {
			return;
		}
		
		$log_dir = trailingslashit( $uploads['basedir'] ) . 'satori-report-logs/';
		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
		}
		
		$log_file = $log_dir . 'satori-report-logs.log';
		$entry    = self::interpolate_context( $message, $context );
		$line     = sprintf( '[%s] %s: %s%s', gmdate( 'Y-m-d H:i:s' ), strtoupper( $level ), $entry, PHP_EOL );
		
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen -- Logger manages its own file handle.
		$file_handle = fopen( $log_file, 'a' );
		if ( false === $file_handle ) {
			return;
		}
		
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite -- Logger manages its own file handle.
		fwrite( $file_handle, $line );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose -- Logger manages its own file handle.
		fclose( $file_handle );
	}
	
	/**
	 * Determine if debug logging is enabled.
	 *
	 * @return bool
	 */
	protected static function is_debug_enabled() {
		if ( defined( 'SATORI_REPORT_LOGS_DEBUG' ) ) {
			return (bool) SATORI_REPORT_LOGS_DEBUG;
		}
		
		return (bool) get_option( 'satori_report_logs_debug', false );
	}
	
	/**
	 * Replace context placeholders in the message.
	 *
	 * @param string $message Message with placeholders in {key} format.
	 * @param array  $context Context data.
	 * @return string
	 */
	protected static function interpolate_context( $message, array $context ) {
		$replace = array();
		foreach ( $context as $key => $value ) {
			$replace[ '{' . $key . '}' ] = (string) $value;
		}
		
		return strtr( $message, $replace );
	}
}

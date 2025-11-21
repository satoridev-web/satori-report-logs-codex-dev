<?php
/**
 * Simple autoloader for SATORI Report Logs classes.
 *
 * @package Satori_Report_Logs
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( defined( 'SATORI_REPORT_LOGS_AUTOLOADER_LOADED' ) ) {
    return;
}

define( 'SATORI_REPORT_LOGS_AUTOLOADER_LOADED', true );

spl_autoload_register(
    function ( $fqcn ) {
        $prefix = 'Satori\\Report_Logs\\';

        if ( 0 !== strpos( $fqcn, $prefix ) ) {
            return;
        }

        $relative = substr( $fqcn, strlen( $prefix ) );
        $relative = str_replace( '\\', '/', $relative );
        $relative = strtolower( $relative );

        $parts = explode( '/', $relative );
        $class = array_pop( $parts );

        $base = 'includes/';

        if ( ! empty( $parts ) && 'admin' === $parts[0] ) {
            $base = '';
        }

        $plugin_path = defined( 'SATORI_REPORT_LOGS_PATH' ) ? SATORI_REPORT_LOGS_PATH : plugin_dir_path( __DIR__ . '/..' );

        $path = $plugin_path . $base;

        if ( ! empty( $parts ) ) {
            $path .= implode( '/', $parts ) . '/';
        }

        $path .= 'class-' . str_replace( '_', '-', $class ) . '.php';

        if ( file_exists( $path ) ) {
            require_once $path;
        }
    }
);

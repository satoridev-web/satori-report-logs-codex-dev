<?php
/**
 * Plugin Name:       SATORI Report Logs
 * Plugin URI:        https://satori.com.au/
 * Description:       Monthly service and maintenance report logs with HTML, CSV, and PDF output for WordPress sites.
 * Version:           0.1.0
 * Author:            Satori Graphics Pty Ltd
 * Author URI:        https://satori.com.au/
 * Text Domain:       satori-report-logs
 * Domain Path:       /languages
 *
 * @package Satori_Report_Logs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------
 * SATORI Report Logs — Plugin Bootstrap
 * -------------------------------------------------*/

define( 'SATORI_REPORT_LOGS_PATH', plugin_dir_path( __FILE__ ) );
define( 'SATORI_REPORT_LOGS_URL', plugin_dir_url( __FILE__ ) );
define( 'SATORI_REPORT_LOGS_VERSION', '0.1.0' );

/**
 * Simple autoloader for Satori\Report_Logs\* classes.
 *
 * @param string $fqcn Fully qualified class name.
 * @return void
 */
spl_autoload_register(
        function ( $fqcn ) {
                if ( strpos( $fqcn, 'Satori\\Report_Logs\\' ) !== 0 ) {
                        return;
                }

                $relative = str_replace(
                        array( 'Satori\\Report_Logs\\', '\\' ),
                        array( '', '/' ),
                        $fqcn
                );

                $relative = strtolower( $relative );
                $parts    = explode( '/', $relative );
                $class    = array_pop( $parts );

                $base = 'includes/';

                if ( ! empty( $parts ) && 'admin' === $parts[0] ) {
                        $base = '';
                }

                $path = SATORI_REPORT_LOGS_PATH . $base;

                if ( ! empty( $parts ) ) {
                        $path .= implode( '/', $parts ) . '/';
                }

                $path .= 'class-' . $class . '.php';

                if ( file_exists( $path ) ) {
                        require_once $path;
                }
        }
);

/**
 * Boot the plugin core.
 *
 * @return void
 */
function satori_report_logs_boot() {
        if ( ! class_exists( '\Satori\Report_Logs\Plugin' ) ) {
                return;
        }

        if ( ! class_exists( '\Satori\Report_Logs\Db\Schema' ) ) {
                return;
        }

        \Satori\Report_Logs\Db\Schema::instance()->maybe_upgrade();
        \Satori\Report_Logs\Plugin::instance();
}
add_action( 'plugins_loaded', 'satori_report_logs_boot' );

/**
 * Handle plugin activation tasks.
 *
 * @return void
 */
function satori_report_logs_activate() {
        \Satori\Report_Logs\Db\Schema::instance()->install();
}
register_activation_hook( __FILE__, 'satori_report_logs_activate' );

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
if ( ! defined( 'SATORI_REPORT_LOGS_DEBUG' ) ) {
        define( 'SATORI_REPORT_LOGS_DEBUG', false );
}

require_once SATORI_REPORT_LOGS_PATH . 'includes/autoload.php';

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
        if ( ! class_exists( '\Satori\Report_Logs\Db\Schema' ) ) {
                return;
        }

        \Satori\Report_Logs\Db\Schema::instance()->install();
}
register_activation_hook( __FILE__, 'satori_report_logs_activate' );

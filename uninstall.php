<?php
/**
 * Plugin uninstall handler.
 *
 * @package Satori_Report_Logs
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

if ( ! defined( 'SATORI_REPORT_LOGS_PATH' ) ) {
    define( 'SATORI_REPORT_LOGS_PATH', plugin_dir_path( __FILE__ ) );
}

require_once SATORI_REPORT_LOGS_PATH . 'includes/autoload.php';

if ( ! class_exists( '\Satori\Report_Logs\Db\Schema' ) ) {
    return;
}

$schema = \Satori\Report_Logs\Db\Schema::instance();

global $wpdb;

$items_table   = esc_sql( $schema->get_items_table() );
$reports_table = esc_sql( $schema->get_reports_table() );

$wpdb->query( "DROP TABLE IF EXISTS `{$items_table}`" );
$wpdb->query( "DROP TABLE IF EXISTS `{$reports_table}`" );

delete_option( 'satori_report_logs_db_version' );
delete_option( 'satori_report_logs_debug' );

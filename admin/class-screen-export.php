<?php
/**
 * Export screen stub for report logs.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Admin;

use Satori\Report_Logs\Db\Reports_Repository;
use Satori\Report_Logs\Export\Export_Manager;

class Screen_Export {

        /**
         * Reports repository instance.
         *
         * @var Reports_Repository
         */
        protected $reports_repository;

        /**
         * Export manager instance.
         *
         * @var Export_Manager
         */
        protected $export_manager;

        /**
         * Constructor.
         *
         * @param Reports_Repository $reports_repository Reports repository.
         */
        public function __construct( Reports_Repository $reports_repository ) {
                $this->reports_repository = $reports_repository;
                $this->export_manager     = new Export_Manager( $reports_repository );
        }

        /**
         * Render the export screen.
         *
         * @return void
         */
        public function render() {
                if ( ! current_user_can( 'manage_options' ) ) {
                        wp_die( esc_html__( 'You do not have permission to access this page.', 'satori-report-logs' ) );
                }

                $log_id = isset( $_REQUEST['log_id'] ) ? absint( $_REQUEST['log_id'] ) : 0;
                $format = isset( $_REQUEST['format'] ) ? sanitize_key( wp_unslash( $_REQUEST['format'] ) ) : 'html';
                $report = $log_id ? $this->reports_repository->get_report( $log_id ) : null;
                $error  = '';

                if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
                        check_admin_referer( 'satori_report_logs_export' );

                        $result = $this->export_manager->export( $log_id, $format );

                        if ( is_wp_error( $result ) ) {
                                $error = $result->get_error_message();
                        }
                }

                echo '<div class="wrap satori-report-logs-admin">';
                echo '<h1>' . esc_html__( 'Export Report', 'satori-report-logs' ) . '</h1>';

                if ( ! $report ) {
                        echo '<div class="notice notice-error"><p>' . esc_html__( 'Report not found or missing.', 'satori-report-logs' ) . '</p></div>';
                        echo '</div>';
                        return;
                }

                if ( $error ) {
                        echo '<div class="notice notice-error"><p>' . esc_html( $error ) . '</p></div>';
                }

                $month_name    = date_i18n( 'F', mktime( 0, 0, 0, (int) $report['month'], 10 ) );
                $report_period = $month_name . ' ' . (int) $report['year'];

                echo '<p>' . sprintf( esc_html__( 'Select an export format for %s.', 'satori-report-logs' ), esc_html( $report_period ) ) . '</p>';

                echo '<form method="post" action="">';
                wp_nonce_field( 'satori_report_logs_export' );
                echo '<input type="hidden" name="page" value="' . esc_attr( Admin::MENU_SLUG ) . '" />';
                echo '<input type="hidden" name="action" value="export" />';
                echo '<input type="hidden" name="log_id" value="' . esc_attr( $log_id ) . '" />';

                echo '<table class="form-table">';
                echo '<tr>';
                echo '<th scope="row"><label for="format">' . esc_html__( 'Format', 'satori-report-logs' ) . '</label></th>';
                echo '<td>';
                echo '<select name="format" id="format">';
                echo '<option value="html"' . selected( 'html', $format, false ) . '>' . esc_html__( 'HTML (download)', 'satori-report-logs' ) . '</option>';
                echo '<option value="csv"' . selected( 'csv', $format, false ) . '>' . esc_html__( 'CSV (download)', 'satori-report-logs' ) . '</option>';
                echo '<option value="pdf"' . selected( 'pdf', $format, false ) . '>' . esc_html__( 'PDF', 'satori-report-logs' ) . '</option>';
                echo '</select>';
                echo '</td>';
                echo '</tr>';
                echo '</table>';

                submit_button( esc_html__( 'Generate Export', 'satori-report-logs' ) );

                echo '</form>';
                echo '</div>';
        }
}

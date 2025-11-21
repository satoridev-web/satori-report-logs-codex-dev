<?php
/**
 * Export manager orchestrating HTML, CSV, and PDF exports.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Export;

use Satori\Report_Logs\Db\Reports_Repository;
use WP_Error;

/**
 * Handles dispatching export generation and sending headers.
 */
class Export_Manager {

        /**
         * Reports repository instance.
         *
         * @var Reports_Repository
         */
        protected $reports_repository;

        /**
         * Constructor.
         *
         * @param Reports_Repository $reports_repository Reports repository instance.
         */
        public function __construct( Reports_Repository $reports_repository ) {
                $this->reports_repository = $reports_repository;
        }

        /**
         * Execute an export for a given log and format.
         *
         * @param int    $log_id Report log ID.
         * @param string $format Export format: html, csv, pdf.
         * @return void|WP_Error
         */
        public function export( $log_id, $format ) {
                $log_id = absint( $log_id );
                $format = sanitize_key( $format );

                if ( ! in_array( $format, array( 'html', 'csv', 'pdf' ), true ) ) {
                        return new WP_Error( 'satori_report_logs_invalid_format', __( 'Invalid export format.', 'satori-report-logs' ) );
                }

                if ( ! $log_id ) {
                        return new WP_Error( 'satori_report_logs_missing_id', __( 'Missing report ID for export.', 'satori-report-logs' ) );
                }

                $report = $this->reports_repository->get_report( $log_id );

                if ( ! $report ) {
                        return new WP_Error( 'satori_report_logs_missing_report', __( 'Report not found.', 'satori-report-logs' ) );
                }

                $items    = $this->reports_repository->get_items_for_report( $log_id );
                $filename = $this->build_filename( $report, $format );

                switch ( $format ) {
                        case 'csv':
                                $exporter     = new Export_Csv();
                                $content_type = 'text/csv; charset=utf-8';
                                $disposition  = 'attachment';
                                break;
                        case 'pdf':
                                $exporter     = new Export_Pdf();
                                $content_type = 'application/pdf';
                                $disposition  = 'inline';
                                break;
                        case 'html':
                        default:
                                $exporter     = new Export_Html();
                                $content_type = 'text/html; charset=utf-8';
                                $disposition  = 'attachment';
                                break;
                }

                $output = $exporter->generate( $report, $items );

                if ( is_wp_error( $output ) ) {
                        return $output;
                }

                /**
                 * Filter the generated export output before sending.
                 *
                 * @param string $output Generated export content.
                 * @param string $format Export format.
                 * @param array  $report Report data.
                 * @param array  $items  Report items.
                 */
                $output = apply_filters( 'satori_report_logs_export_output', $output, $format, $report, $items );

                $this->send_headers( $content_type, $disposition, $filename );

                /**
                 * Fires after an export has been generated.
                 *
                 * @param int    $log_id Report log ID.
                 * @param string $format Export format.
                 */
                do_action( 'satori_report_logs_export', $log_id, $format );

                echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- output is escaped in templates or trusted binary.
                exit;
        }

        /**
         * Build a download filename for the export.
         *
         * @param array  $report  Report data.
         * @param string $format  Export format.
         * @return string
         */
        protected function build_filename( array $report, $format ) {
                $month = isset( $report['month'] ) ? absint( $report['month'] ) : 0;
                $year  = isset( $report['year'] ) ? absint( $report['year'] ) : 0;

                $parts = array( 'satori-report-log' );

                if ( $year ) {
                        $parts[] = $year;
                }

                if ( $month ) {
                        $parts[] = str_pad( (string) $month, 2, '0', STR_PAD_LEFT );
                }

                $filename = implode( '-', $parts ) . '.' . $format;

                return sanitize_file_name( $filename );
        }

        /**
         * Send download headers for the export.
         *
         * @param string $content_type Content type header value.
         * @param string $disposition  Content disposition (attachment|inline).
         * @param string $filename     Download filename.
         * @return void
         */
        protected function send_headers( $content_type, $disposition, $filename ) {
                if ( function_exists( 'nocache_headers' ) ) {
                        nocache_headers();
                }

                header( 'Content-Type: ' . $content_type );
                header( 'Content-Disposition: ' . $disposition . '; filename="' . $filename . '"' );
        }
}

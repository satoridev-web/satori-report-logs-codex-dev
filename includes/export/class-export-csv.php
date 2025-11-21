<?php
/**
 * CSV export generator.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Export;

use WP_Error;

/**
 * Build CSV output for report logs.
 */
class Export_Csv {

        /**
         * Render CSV export content.
         *
         * @param array $report Report data.
         * @param array $items  Report items.
         * @return string|WP_Error
         */
        public function generate( array $report, array $items ) {
                $handle = fopen( 'php://temp', 'r+' );

                if ( false === $handle ) {
                        return new WP_Error( 'satori_report_logs_csv_error', __( 'Could not open CSV output buffer.', 'satori-report-logs' ) );
                }

                fputcsv( $handle, array( 'Field', 'Value' ) );

                $month_name = isset( $report['month'] ) ? date_i18n( 'F', mktime( 0, 0, 0, (int) $report['month'], 10 ) ) : '';
                $year       = isset( $report['year'] ) ? absint( $report['year'] ) : '';

                if ( $month_name || $year ) {
                        fputcsv( $handle, array( __( 'Report Period', 'satori-report-logs' ), trim( $month_name . ' ' . $year ) ) );
                }

                foreach ( $items as $item ) {
                        $label = isset( $item['label'] ) ? wp_strip_all_tags( $item['label'] ) : '';
                        $value = isset( $item['value'] ) ? wp_strip_all_tags( $item['value'] ) : '';

                        if ( '' === $label && '' === $value ) {
                                continue;
                        }

                        fputcsv( $handle, array( $label, $value ) );
                }

                rewind( $handle );
                $csv = stream_get_contents( $handle );
                fclose( $handle );

                return (string) $csv;
        }
}

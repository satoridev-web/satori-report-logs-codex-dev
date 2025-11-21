<?php
/**
 * HTML export generator.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Export;

use WP_Error;

/**
 * Build HTML output for report logs.
 */
class Export_Html {

        /**
         * Render HTML export content.
         *
         * @param array $report Report data.
         * @param array $items  Report items.
         * @return string|WP_Error
         */
        public function generate( array $report, array $items ) {
                $template = apply_filters(
                        'satori_report_logs_export_html_template',
                        SATORI_REPORT_LOGS_PATH . 'templates/export-html.php',
                        $report,
                        $items
                );

                if ( ! file_exists( $template ) ) {
                        return new WP_Error( 'satori_report_logs_missing_template', __( 'HTML export template not found.', 'satori-report-logs' ) );
                }

                ob_start();

                include $template;

                return ob_get_clean();
        }
}

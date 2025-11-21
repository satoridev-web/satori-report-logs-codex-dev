<?php
/**
 * PDF export generator using Dompdf.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Export;

use Dompdf\Dompdf;
use Dompdf\Options;
use WP_Error;

/**
 * Build PDF output for report logs.
 */
class Export_Pdf {

        /**
         * Render PDF export content.
         *
         * @param array $report Report data.
         * @param array $items  Report items.
         * @return string|WP_Error
         */
        public function generate( array $report, array $items ) {
                $this->maybe_bootstrap_autoloader();

                if ( ! class_exists( '\\Dompdf\\Dompdf' ) ) {
                        return new WP_Error(
                                'satori_report_logs_missing_dompdf',
                                __( 'PDF exports require Dompdf. Please install dependencies via Composer.', 'satori-report-logs' )
                        );
                }

                $template = apply_filters(
                        'satori_report_logs_export_pdf_template',
                        SATORI_REPORT_LOGS_PATH . 'templates/export-pdf.php',
                        $report,
                        $items
                );

                if ( ! file_exists( $template ) ) {
                        return new WP_Error( 'satori_report_logs_missing_template', __( 'PDF export template not found.', 'satori-report-logs' ) );
                }

                ob_start();

                include $template;

                $html = ob_get_clean();

                $options = new Options();
                $options->set( 'isRemoteEnabled', true );
                $options->set( 'defaultFont', 'DejaVu Sans' );

                $dompdf = new Dompdf( $options );
                $dompdf->loadHtml( $html );
                $dompdf->setPaper( 'A4', 'portrait' );
                $dompdf->render();

                return $dompdf->output();
        }

        /**
         * Attempt to load Composer autoloader for Dompdf.
         *
         * @return void
         */
        protected function maybe_bootstrap_autoloader() {
                if ( class_exists( '\\Dompdf\\Dompdf' ) ) {
                        return;
                }

                $autoload = SATORI_REPORT_LOGS_PATH . 'vendor/autoload.php';

                if ( file_exists( $autoload ) ) {
                        require_once $autoload;
                }
        }
}

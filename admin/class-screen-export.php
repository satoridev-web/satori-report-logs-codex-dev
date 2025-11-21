<?php
/**
 * Export screen stub for report logs.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Admin;

use Satori\Report_Logs\Db\Reports_Repository;

class Screen_Export {

	/**
	 * Reports repository instance.
	 *
	 * @var Reports_Repository
	 */
	protected $reports_repository;

	/**
	 * Constructor.
	 *
	 * @param Reports_Repository $reports_repository Reports repository.
	 */
	public function __construct( Reports_Repository $reports_repository ) {
		$this->reports_repository = $reports_repository;
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

		$log_id = isset( $_GET['log_id'] ) ? absint( $_GET['log_id'] ) : 0;
		$format = isset( $_GET['format'] ) ? sanitize_key( wp_unslash( $_GET['format'] ) ) : 'html';
		$report = $log_id ? $this->reports_repository->get_report( $log_id ) : null;

		echo '<div class="wrap satori-report-logs-admin">';
		echo '<h1>' . esc_html__( 'Export Report', 'satori-report-logs' ) . '</h1>';

		if ( ! $report ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Report not found or missing.', 'satori-report-logs' ) . '</p></div>';
			echo '</div>';
			return;
		}

		echo '<p>' . esc_html__( 'Export engine not implemented yet. This page will trigger HTML/CSV/PDF generation in a future version.', 'satori-report-logs' ) . '</p>';
		echo '<p>' . sprintf( esc_html__( 'Preparing export for %1$s %2$d in %3$s format.', 'satori-report-logs' ), date_i18n( 'F', mktime( 0, 0, 0, (int) $report['month'], 10 ) ), (int) $report['year'], esc_html( strtoupper( $format ) ) ) . '</p>';
		echo '<a href="#" class="button button-secondary">' . esc_html__( 'Download (coming soon)', 'satori-report-logs' ) . '</a>';
		echo '</div>';
	}
}

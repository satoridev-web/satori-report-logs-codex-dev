<?php
/**
 * Dashboard screen for report logs.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Admin;

use Satori\Report_Logs\Capabilities;
use Satori\Report_Logs\Db\Reports_Repository;

class Screen_Dashboard {

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
	 * Render the dashboard.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( Capabilities::get_required_capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'satori-report-logs' ) );
		}

		$reports   = $this->reports_repository->get_reports();
		$add_url   = add_query_arg(
			array(
				'page'   => Admin::MENU_SLUG,
				'action' => 'edit',
			),
			admin_url( 'admin.php' )
		);
		$edit_text = esc_html__( 'Edit', 'satori-report-logs' );
		$error     = isset( $_GET['error'] ) ? sanitize_key( wp_unslash( $_GET['error'] ) ) : '';

		echo '<div class="wrap satori-report-logs-admin">';
		echo '<h1>' . esc_html__( 'Report Logs', 'satori-report-logs' ) . '</h1>';
		echo '<p>' . esc_html__( 'Manage monthly report logs.', 'satori-report-logs' ) . '</p>';
		echo '<a class="button button-primary" href="' . esc_url( $add_url ) . '">' . esc_html__( 'Add New Report', 'satori-report-logs' ) . '</a>';

		if ( $error ) {
			$error_message = esc_html__( 'Unable to complete that action. Please try again.', 'satori-report-logs' );
			echo '<div class="notice notice-error"><p>' . esc_html( $error_message ) . '</p></div>';
		}

		echo '<table class="wp-list-table widefat fixed striped">';
		echo '<thead><tr>';
		echo '<th>' . esc_html__( 'Month', 'satori-report-logs' ) . '</th>';
		echo '<th>' . esc_html__( 'Year', 'satori-report-logs' ) . '</th>';
		echo '<th>' . esc_html__( 'Status', 'satori-report-logs' ) . '</th>';
		echo '<th>' . esc_html__( 'Updated At', 'satori-report-logs' ) . '</th>';
		echo '<th>' . esc_html__( 'Actions', 'satori-report-logs' ) . '</th>';
		echo '</tr></thead>';
		echo '<tbody>';

		if ( empty( $reports ) ) {
			echo '<tr><td colspan="5">' . esc_html__( 'No reports found. Click "Add New Report" to create one.', 'satori-report-logs' ) . '</td></tr>';
		} else {
			foreach ( $reports as $report ) {
				$report_id  = absint( $report['id'] );
				$month_name = date_i18n( 'F', mktime( 0, 0, 0, (int) $report['month'], 10 ) );
				$status     = $this->reports_repository->get_item_value_by_label( $report_id, 'Status' );
				$status     = $status ? $status : esc_html__( 'Not set', 'satori-report-logs' );
				$updated_at = ! empty( $report['updated_at'] ) ? mysql2date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $report['updated_at'] ) : '—';

				$edit_url = add_query_arg(
					array(
						'page'   => Admin::MENU_SLUG,
						'action' => 'edit',
						'log_id' => $report_id,
					),
					admin_url( 'admin.php' )
				);

				$export_url = add_query_arg(
					array(
						'page'   => Admin::MENU_SLUG,
						'action' => 'export',
						'log_id' => $report_id,
					),
					admin_url( 'admin.php' )
				);

				echo '<tr>';
				echo '<td>' . esc_html( $month_name ) . '</td>';
				echo '<td>' . esc_html( $report['year'] ) . '</td>';
				echo '<td>' . esc_html( $status ) . '</td>';
				echo '<td>' . esc_html( $updated_at ) . '</td>';
				echo '<td>';
				echo '<a class="button button-small" href="' . esc_url( $edit_url ) . '">' . $edit_text . '</a> ';
				echo '<a class="button button-small" href="' . esc_url( $export_url ) . '">' . esc_html__( 'Export', 'satori-report-logs' ) . '</a>';
				echo '<form class="srl-duplicate-form" action="' . esc_url( admin_url( 'admin.php' ) ) . '" method="get">';
				echo '<input type="hidden" name="page" value="' . esc_attr( Admin::MENU_SLUG ) . '" />';
				echo '<input type="hidden" name="action" value="duplicate" />';
				echo '<input type="hidden" name="log_id" value="' . esc_attr( $report_id ) . '" />';
				wp_nonce_field( 'satori_report_logs_duplicate', '_wpnonce', true, true );
				echo '<label class="screen-reader-text" for="duplicate-target-' . esc_attr( $report_id ) . '">' . esc_html__( 'Duplicate target', 'satori-report-logs' ) . '</label>';
				echo '<select name="target" id="duplicate-target-' . esc_attr( $report_id ) . '">';
				echo '<option value="same">' . esc_html__( 'Same month/year', 'satori-report-logs' ) . '</option>';
				echo '<option value="next">' . esc_html__( 'Next month', 'satori-report-logs' ) . '</option>';
				echo '</select>';
				echo '<button type="submit" class="button button-small">' . esc_html__( 'Duplicate', 'satori-report-logs' ) . '</button>';
				echo '</form>';
				echo '</td>';
				echo '</tr>';
			}
		}

		echo '</tbody>';
		echo '</table>';
		echo '</div>';
	}
}

<?php
/**
 * Editor screen for a single report log.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Admin;

use Satori\Report_Logs\Db\Reports_Repository;

class Screen_Editor {

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
	 * Render the editor screen.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'satori-report-logs' ) );
		}

		$log_id         = isset( $_GET['log_id'] ) ? absint( $_GET['log_id'] ) : 0;
		$notice        = '';
		$notice_class  = 'updated';
		$report        = $log_id ? $this->reports_repository->get_report( $log_id ) : null;
		$existing_item = array();

		if ( null !== $report ) {
			$existing_item = $this->map_items_by_label( $this->reports_repository->get_items_for_report( $log_id ) );
		}

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'satori_report_logs_editor' );

			$log_id = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
			$month  = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : 0;
			$year   = isset( $_POST['year'] ) ? absint( $_POST['year'] ) : 0;

			$site_label   = isset( $_POST['site_label'] ) ? sanitize_text_field( wp_unslash( $_POST['site_label'] ) ) : '';
			$environment  = isset( $_POST['environment'] ) ? sanitize_text_field( wp_unslash( $_POST['environment'] ) ) : '';
			$status       = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
			$overview     = isset( $_POST['overview'] ) ? wp_kses_post( wp_unslash( $_POST['overview'] ) ) : '';
			$tasks        = isset( $_POST['tasks_completed'] ) ? wp_kses_post( wp_unslash( $_POST['tasks_completed'] ) ) : '';
			$issues       = isset( $_POST['issues_found'] ) ? wp_kses_post( wp_unslash( $_POST['issues_found'] ) ) : '';
			$errors       = array();

			if ( $month < 1 || $month > 12 ) {
				$errors[] = esc_html__( 'Month must be between 1 and 12.', 'satori-report-logs' );
			}

			if ( 0 === $year ) {
				$errors[] = esc_html__( 'Year is required.', 'satori-report-logs' );
			}

			if ( empty( $errors ) ) {
				if ( $log_id ) {
					$report = $this->reports_repository->get_report( $log_id );

					if ( null === $report ) {
						$errors[] = esc_html__( 'Report not found.', 'satori-report-logs' );
					}
				} else {
					$existing = $this->reports_repository->find_report_by_month_year( $month, $year );
					if ( $existing ) {
						$log_id = (int) $existing['id'];
						$report = $existing;
					} else {
						$log_id = $this->reports_repository->create_report(
							array(
								'month' => $month,
								'year'  => $year,
							)
						);
						$report = $log_id ? $this->reports_repository->get_report( $log_id ) : null;
					}
				}
			}

			if ( empty( $errors ) && $report ) {
				$this->reports_repository->update_report(
					$log_id,
					array(
						'month' => $month,
						'year'  => $year,
					)
				);

				$items = array(
					array(
						'row_type'   => 'header',
						'label'      => 'Site Label',
						'value'      => $site_label,
						'sort_order' => 0,
					),
					array(
						'row_type'   => 'header',
						'label'      => 'Environment',
						'value'      => $environment,
						'sort_order' => 1,
					),
					array(
						'row_type'   => 'header',
						'label'      => 'Status',
						'value'      => $status,
						'sort_order' => 2,
					),
					array(
						'row_type'   => 'section',
						'label'      => 'Overview',
						'value'      => $overview,
						'sort_order' => 10,
					),
					array(
						'row_type'   => 'section',
						'label'      => 'Tasks Completed',
						'value'      => $tasks,
						'sort_order' => 20,
					),
					array(
						'row_type'   => 'section',
						'label'      => 'Issues Found',
						'value'      => $issues,
						'sort_order' => 30,
					),
				);

				$this->reports_repository->save_items( $log_id, $items );
				$existing_item = $this->map_items_by_label( $items );
				$notice        = esc_html__( 'Report saved successfully.', 'satori-report-logs' );
			} elseif ( ! empty( $errors ) ) {
				$notice       = implode( ' ', $errors );
				$notice_class = 'error';
			}
		}

		if ( isset( $site_label, $environment, $status, $overview, $tasks, $issues ) ) {
			$existing_item = array_merge(
				$existing_item,
				array(
					'Site Label'      => $site_label,
					'Environment'     => $environment,
					'Status'          => $status,
					'Overview'        => $overview,
					'Tasks Completed' => $tasks,
					'Issues Found'    => $issues,
				)
			);
		}

		$month = $report['month'] ?? ( isset( $_POST['month'] ) ? absint( $_POST['month'] ) : (int) date( 'n' ) );
		$year  = $report['year'] ?? ( isset( $_POST['year'] ) ? absint( $_POST['year'] ) : (int) date( 'Y' ) );

		$site_label  = $existing_item['Site Label'] ?? '';
		$environment = $existing_item['Environment'] ?? '';
		$status      = $existing_item['Status'] ?? '';
		$overview    = $existing_item['Overview'] ?? '';
		$tasks       = $existing_item['Tasks Completed'] ?? '';
		$issues      = $existing_item['Issues Found'] ?? '';

		echo '<div class="wrap satori-report-logs-admin">';
		echo '<h1>' . esc_html__( 'Report Editor', 'satori-report-logs' ) . '</h1>';

		if ( ! empty( $notice ) ) {
			echo '<div class="' . esc_attr( $notice_class ) . ' notice"><p>' . esc_html( $notice ) . '</p></div>';
		}

		echo '<form method="post" action="">';
		wp_nonce_field( 'satori_report_logs_editor' );
		echo '<input type="hidden" name="log_id" value="' . esc_attr( $log_id ) . '" />';
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="month">' . esc_html__( 'Month', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="number" min="1" max="12" name="month" id="month" value="' . esc_attr( $month ) . '" class="small-text" required /></td></tr>';
		echo '<tr><th scope="row"><label for="year">' . esc_html__( 'Year', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="number" name="year" id="year" value="' . esc_attr( $year ) . '" class="small-text" required /></td></tr>';
		echo '<tr><th scope="row"><label for="site_label">' . esc_html__( 'Site Label', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="text" name="site_label" id="site_label" value="' . esc_attr( $site_label ) . '" class="regular-text" /></td></tr>';
		echo '<tr><th scope="row"><label for="environment">' . esc_html__( 'Environment', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="text" name="environment" id="environment" value="' . esc_attr( $environment ) . '" class="regular-text" /></td></tr>';
		echo '<tr><th scope="row"><label for="status">' . esc_html__( 'Status', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="text" name="status" id="status" value="' . esc_attr( $status ) . '" class="regular-text" /></td></tr>';
		echo '</table>';

		echo '<h2>' . esc_html__( 'Overview', 'satori-report-logs' ) . '</h2>';
		echo '<textarea name="overview" id="overview" rows="5" class="large-text">' . esc_textarea( $overview ) . '</textarea>';

		echo '<h2>' . esc_html__( 'Tasks Completed', 'satori-report-logs' ) . '</h2>';
		echo '<textarea name="tasks_completed" id="tasks_completed" rows="5" class="large-text">' . esc_textarea( $tasks ) . '</textarea>';

		echo '<h2>' . esc_html__( 'Issues Found', 'satori-report-logs' ) . '</h2>';
		echo '<textarea name="issues_found" id="issues_found" rows="5" class="large-text">' . esc_textarea( $issues ) . '</textarea>';

		submit_button( __( 'Save Report', 'satori-report-logs' ) );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Map items by label.
	 *
	 * @param array $items Items array.
	 * @return array
	 */
	protected function map_items_by_label( array $items ) {
		$mapped = array();

		foreach ( $items as $item ) {
			if ( isset( $item['label'] ) ) {
				$mapped[ $item['label'] ] = $item['value'] ?? '';
			}
		}

		return $mapped;
	}
}

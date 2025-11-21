<?php
/**
 * Editor screen for a single report log.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Admin;

use Satori\Report_Logs\Capabilities;
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
		if ( ! current_user_can( Capabilities::get_required_capability() ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'satori-report-logs' ) );
		}

		$log_id         = isset( $_GET['log_id'] ) ? absint( $_GET['log_id'] ) : 0;
		$notice        = '';
		$notice_class  = 'updated';
		$notices       = array();
		$report        = $log_id ? $this->reports_repository->get_report( $log_id ) : null;
		$existing_item = array();
		$active_tab    = isset( $_REQUEST['tab'] ) ? sanitize_key( wp_unslash( $_REQUEST['tab'] ) ) : 'details';
		$allowed_tabs  = array( 'details', 'content' );
		if ( ! in_array( $active_tab, $allowed_tabs, true ) ) {
			$active_tab = 'details';
		}

		if ( null !== $report ) {
			$existing_item = $this->map_items_by_label( $this->reports_repository->get_items_for_report( $log_id ) );
		}

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			check_admin_referer( 'satori_report_logs_editor' );

			$log_id = isset( $_POST['log_id'] ) ? absint( $_POST['log_id'] ) : 0;
			$month  = isset( $_POST['month'] ) ? absint( $_POST['month'] ) : 0;
			$year   = isset( $_POST['year'] ) ? absint( $_POST['year'] ) : 0;
			$active_tab = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : $active_tab;

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

		$form_action = add_query_arg(
			array(
				'page'   => Admin::MENU_SLUG,
				'action' => 'edit',
				'log_id' => $log_id,
				'tab'    => $active_tab,
			),
			admin_url( 'admin.php' )
		);

		if ( isset( $_GET['duplicated'] ) && '1' === $_GET['duplicated'] ) {
			$notices[] = array(
				'class'   => 'updated',
				'content' => esc_html__( 'Report duplicated. Review the details below and save when ready.', 'satori-report-logs' ),
			);
		}

		if ( $notice ) {
			$notices[] = array(
				'class'   => $notice_class,
				'content' => $notice,
			);
		}

		echo '<div class="wrap satori-report-logs-admin">';
		echo '<h1>' . esc_html__( 'Report Editor', 'satori-report-logs' ) . '</h1>';
		echo '<p class="description">' . esc_html__( 'Update the reporting period, site context, and monthly notes.', 'satori-report-logs' ) . '</p>';

		foreach ( $notices as $message ) {
			$notice_class = isset( $message['class'] ) ? $message['class'] : 'updated';
			$notice_text  = isset( $message['content'] ) ? $message['content'] : '';
			if ( $notice_text ) {
				echo '<div class="' . esc_attr( $notice_class ) . ' notice"><p>' . esc_html( $notice_text ) . '</p></div>';
			}
		}

		echo '<h2 class="nav-tab-wrapper srl-editor-tabs">';
		echo '<a href="#" class="nav-tab' . ( 'details' === $active_tab ? ' nav-tab-active' : '' ) . '" data-tab="details">' . esc_html__( 'Report Details', 'satori-report-logs' ) . '</a>';
		echo '<a href="#" class="nav-tab' . ( 'content' === $active_tab ? ' nav-tab-active' : '' ) . '" data-tab="content">' . esc_html__( 'Report Content', 'satori-report-logs' ) . '</a>';
		echo '</h2>';

		echo '<form method="post" action="' . esc_url( $form_action ) . '">';
		wp_nonce_field( 'satori_report_logs_editor' );
		echo '<input type="hidden" name="log_id" value="' . esc_attr( $log_id ) . '" />';
		echo '<input type="hidden" name="tab" value="' . esc_attr( $active_tab ) . '" />';

		echo '<div class="srl-tab-panel' . ( 'details' === $active_tab ? ' is-active' : '' ) . '" data-tab="details">';
		echo '<h2 class="title">' . esc_html__( 'Reporting window', 'satori-report-logs' ) . '</h2>';
		echo '<p class="description">' . esc_html__( 'Set the month and year for this report. This controls how entries are grouped on the dashboard.', 'satori-report-logs' ) . '</p>';
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="month">' . esc_html__( 'Month', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="number" min="1" max="12" name="month" id="month" value="' . esc_attr( $month ) . '" class="small-text" required />';
		echo '<p class="description">' . esc_html__( 'Use numeric format (1-12).', 'satori-report-logs' ) . '</p></td></tr>';
		echo '<tr><th scope="row"><label for="year">' . esc_html__( 'Year', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="number" name="year" id="year" value="' . esc_attr( $year ) . '" class="small-text" required />';
		echo '<p class="description">' . esc_html__( 'Enter the four-digit calendar year.', 'satori-report-logs' ) . '</p></td></tr>';
		echo '</table>';

		echo '<h2 class="title">' . esc_html__( 'Site context', 'satori-report-logs' ) . '</h2>';
		echo '<p class="description">' . esc_html__( 'Add quick identifiers to help collaborators recognise this location or environment.', 'satori-report-logs' ) . '</p>';
		echo '<table class="form-table">';
		echo '<tr><th scope="row"><label for="site_label">' . esc_html__( 'Site Label', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="text" name="site_label" id="site_label" value="' . esc_attr( $site_label ) . '" class="regular-text" />';
		echo '<p class="description">' . esc_html__( 'Friendly label such as a store, greenhouse, or client name.', 'satori-report-logs' ) . '</p></td></tr>';
		echo '<tr><th scope="row"><label for="environment">' . esc_html__( 'Environment', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="text" name="environment" id="environment" value="' . esc_attr( $environment ) . '" class="regular-text" />';
		echo '<p class="description">' . esc_html__( 'Note the environment or system (e.g. staging, production).', 'satori-report-logs' ) . '</p></td></tr>';
		echo '<tr><th scope="row"><label for="status">' . esc_html__( 'Status', 'satori-report-logs' ) . '</label></th>';
		echo '<td><input type="text" name="status" id="status" value="' . esc_attr( $status ) . '" class="regular-text" />';
		echo '<p class="description">' . esc_html__( 'Summarise the overall health at a glance.', 'satori-report-logs' ) . '</p></td></tr>';
		echo '</table>';
		echo '</div>';

		echo '<div class="srl-tab-panel' . ( 'content' === $active_tab ? ' is-active' : '' ) . '" data-tab="content">';
		echo '<h2 class="title">' . esc_html__( 'Narrative sections', 'satori-report-logs' ) . '</h2>';
		echo '<p class="description">' . esc_html__( 'Capture highlights, completed tasks, and issues for the reporting period. You can paste bullet points or short paragraphs.', 'satori-report-logs' ) . '</p>';
		echo '<h3>' . esc_html__( 'Overview', 'satori-report-logs' ) . '</h3>';
		echo '<p class="description">' . esc_html__( 'Provide a short summary of activities and outcomes.', 'satori-report-logs' ) . '</p>';
		echo '<textarea name="overview" id="overview" rows="5" class="large-text">' . esc_textarea( $overview ) . '</textarea>';

		echo '<h3>' . esc_html__( 'Tasks Completed', 'satori-report-logs' ) . '</h3>';
		echo '<p class="description">' . esc_html__( 'List the key work completed this month (one item per line works well).', 'satori-report-logs' ) . '</p>';
		echo '<textarea name="tasks_completed" id="tasks_completed" rows="5" class="large-text">' . esc_textarea( $tasks ) . '</textarea>';

		echo '<h3>' . esc_html__( 'Issues Found', 'satori-report-logs' ) . '</h3>';
		echo '<p class="description">' . esc_html__( 'Record any problems, risks, or follow-ups that need attention.', 'satori-report-logs' ) . '</p>';
		echo '<textarea name="issues_found" id="issues_found" rows="5" class="large-text">' . esc_textarea( $issues ) . '</textarea>';
		echo '</div>';

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

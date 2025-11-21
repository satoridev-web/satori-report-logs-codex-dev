<?php
/**
 * Admin functionality.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Admin;

/* -------------------------------------------------
 * Admin — Main
 * -------------------------------------------------*/

class Admin {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	/**
	 * Register the main admin menu.
	 *
	 * @return void
	 */
	public function register_menu() {
		add_menu_page(
			__( 'SATORI Report Logs', 'satori-report-logs' ),
			__( 'Report Logs', 'satori-report-logs' ),
			'manage_options',
			'satori-report-logs',
			array( $this, 'render_dashboard_page' ),
			'dashicons-analytics',
			56
		);
	}

	/**
	 * Render the main dashboard page (will become our “spreadsheet-style” UI).
	 *
	 * @return void
	 */
	public function render_dashboard_page() {
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SATORI Report Logs', 'satori-report-logs' ) . '</h1>';
		echo '<p>' . esc_html__( 'Initial skeleton — HTML table / spreadsheet-style editor coming soon.', 'satori-report-logs' ) . '</p>';
		echo '</div>';
	}
}

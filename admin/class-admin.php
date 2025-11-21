<?php
/**
 * Admin functionality.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Admin;

use Satori\Report_Logs\Db\Reports_Repository;

/* -------------------------------------------------
 * Admin — Main
 * -------------------------------------------------*/

class Admin {

	/**
	 * Menu slug for the admin screens.
	 */
	const MENU_SLUG = 'satori-report-logs';

	/**
	 * Reports repository instance.
	 *
	 * @var Reports_Repository
	 */
	protected $reports_repository;

	/**
	 * Dashboard screen instance.
	 *
	 * @var Screen_Dashboard
	 */
	protected $dashboard_screen;

	/**
	 * Editor screen instance.
	 *
	 * @var Screen_Editor
	 */
	protected $editor_screen;

	/**
	 * Export screen instance.
	 *
	 * @var Screen_Export
	 */
	protected $export_screen;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->reports_repository = Reports_Repository::instance();
		$this->dashboard_screen   = new Screen_Dashboard( $this->reports_repository );
		$this->editor_screen      = new Screen_Editor( $this->reports_repository );
		$this->export_screen      = new Screen_Export( $this->reports_repository );

		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
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
			self::MENU_SLUG,
			array( $this, 'render_page' ),
			'dashicons-analytics',
			56
		);
	}

	/**
	 * Enqueue admin assets for plugin pages.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_' . self::MENU_SLUG !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'satori-report-logs-admin',
			SATORI_REPORT_LOGS_URL . 'assets/css/admin.css',
			array(),
			SATORI_REPORT_LOGS_VERSION
		);

		wp_enqueue_script(
			'satori-report-logs-admin',
			SATORI_REPORT_LOGS_URL . 'assets/js/admin.js',
			array( 'jquery' ),
			SATORI_REPORT_LOGS_VERSION,
			true
		);
	}

	/**
	 * Render the correct screen based on the action query var.
	 *
	 * @return void
	 */
	public function render_page() {
		$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : '';

		switch ( $action ) {
			case 'edit':
				$this->editor_screen->render();
				break;
			case 'export':
				$this->export_screen->render();
				break;
			default:
				$this->dashboard_screen->render();
		}
	}
}

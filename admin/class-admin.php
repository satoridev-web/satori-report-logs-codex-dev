<?php
/**
 * Admin functionality.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Admin;

use Satori\Report_Logs\Capabilities;
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
                        Capabilities::get_required_capability(),
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
                        case 'duplicate':
                                $this->handle_duplicate_action();
                                break;
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

        /**
         * Handle duplication requests before rendering screens.
         *
         * @return void
         */
        protected function handle_duplicate_action() {
                if ( ! current_user_can( Capabilities::get_required_capability() ) ) {
                        wp_die( esc_html__( 'You do not have permission to access this page.', 'satori-report-logs' ) );
                }

                check_admin_referer( 'satori_report_logs_duplicate' );

                $log_id = isset( $_GET['log_id'] ) ? absint( $_GET['log_id'] ) : 0;
                $target = isset( $_GET['target'] ) ? sanitize_key( wp_unslash( $_GET['target'] ) ) : 'same';

                if ( ! $log_id ) {
                        wp_safe_redirect(
                                add_query_arg(
                                        array(
                                                'page'  => self::MENU_SLUG,
                                                'error' => 'missing_log',
                                        ),
                                        admin_url( 'admin.php' )
                                )
                        );
                        exit;
                }

                $source_report = $this->reports_repository->get_report( $log_id );
                $month         = $source_report['month'] ?? 0;
                $year          = $source_report['year'] ?? 0;

                if ( 'next' === $target && $month && $year ) {
                        $month++;
                        if ( $month > 12 ) {
                                $month = 1;
                                $year++;
                        }
                }

                $new_log_id = $this->reports_repository->duplicate_report(
                        $log_id,
                        array(
                                'month' => (int) $month,
                                'year'  => (int) $year,
                        )
                );

                if ( ! $new_log_id ) {
                        wp_safe_redirect(
                                add_query_arg(
                                        array(
                                                'page'  => self::MENU_SLUG,
                                                'error' => 'duplicate_failed',
                                        ),
                                        admin_url( 'admin.php' )
                                )
                        );
                        exit;
                }

                wp_safe_redirect(
                        add_query_arg(
                                array(
                                        'page'       => self::MENU_SLUG,
                                        'action'     => 'edit',
                                        'log_id'     => $new_log_id,
                                        'duplicated' => 1,
                                ),
                                admin_url( 'admin.php' )
                        )
                );
                exit;
        }
}

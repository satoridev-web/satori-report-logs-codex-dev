<?php
/**
 * Core plugin class.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs;

use Satori\Report_Logs\Db\Reports_Repository;
use Satori\Report_Logs\Db\Schema;

/* -------------------------------------------------
 * Main Plugin Class
 * -------------------------------------------------*/

class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin
	 */
	protected static $instance;

	/**
	 * Get singleton instance.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Database schema manager instance.
	 *
	 * @var Schema
	 */
	protected $schema;

	/**
	 * Reports repository instance.
	 *
	 * @var Reports_Repository
	 */
	protected $reports_repository;

	/**
	 * Plugin constructor.
	 */
	protected function __construct() {
		$this->schema             = Schema::instance();
		$this->reports_repository = Reports_Repository::instance();

		$this->define_hooks();
	}

	/**
	 * Register core hooks.
	 *
	 * @return void
	 */
	protected function define_hooks() {
		if ( is_admin() ) {
			new Admin\Admin();
		}
	}

	/**
	 * Get the reports repository instance.
	 *
	 * @return Reports_Repository
	 */
	public function get_reports_repository() {
		return $this->reports_repository;
	}
}

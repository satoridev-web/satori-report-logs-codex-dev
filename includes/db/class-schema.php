<?php
/**
 * Database schema management.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Db;

/**
 * Handles installation and upgrades for the plugin database tables.
 */
class Schema {

	/**
	 * Current database schema version.
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Singleton instance.
	 *
	 * @var Schema
	 */
	protected static $instance;

	/**
	 * Reports table name.
	 *
	 * @var string
	 */
	protected $reports_table;

	/**
	 * Report items table name.
	 *
	 * @var string
	 */
	protected $items_table;

	/**
	 * Create the singleton instance.
	 */
	protected function __construct() {
		global $wpdb;

		$this->reports_table = $wpdb->prefix . 'satori_report_logs';
		$this->items_table   = $wpdb->prefix . 'satori_report_log_items';
	}

	/**
	 * Get the singleton instance.
	 *
	 * @return Schema
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Get reports table name.
	 *
	 * @return string
	 */
	public function get_reports_table() {
		return $this->reports_table;
	}

	/**
	 * Get report items table name.
	 *
	 * @return string
	 */
	public function get_items_table() {
		return $this->items_table;
	}

	/**
	 * Install the plugin database tables.
	 *
	 * @return void
	 */
	public function install() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$tables = array(
			"CREATE TABLE {$this->reports_table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				month tinyint(2) unsigned NOT NULL,
				year smallint(4) unsigned NOT NULL,
				created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY year_month (year, month)
			) {$charset_collate};",
			"CREATE TABLE {$this->items_table} (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				log_id bigint(20) unsigned NOT NULL,
				row_type varchar(50) NOT NULL,
				label varchar(255) NOT NULL,
				value longtext NOT NULL,
				sort_order int(11) NOT NULL DEFAULT 0,
				PRIMARY KEY  (id),
				KEY log_id (log_id),
				KEY sort_order (sort_order)
			) {$charset_collate};",
		);

		foreach ( $tables as $table_sql ) {
			dbDelta( $table_sql );
		}

		update_option( 'satori_report_logs_db_version', self::DB_VERSION );
	}

	/**
	 * Run database migrations if the schema version has changed.
	 *
	 * @return void
	 */
	public function maybe_upgrade() {
		$installed_version = get_option( 'satori_report_logs_db_version' );

		if ( self::DB_VERSION !== $installed_version ) {
			$this->install();
		}
	}
}

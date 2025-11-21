<?php
/**
 * Reports repository for CRUD operations.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Db;

/**
 * Provides data access helpers for reports and their items.
 */
class Reports_Repository {

	/**
	 * Singleton instance.
	 *
	 * @var Reports_Repository
	 */
	protected static $instance;

	/**
	 * Table name for reports.
	 *
	 * @var string
	 */
	protected $reports_table;

	/**
	 * Table name for report items.
	 *
	 * @var string
	 */
	protected $items_table;

	/**
	 * Schema helper instance.
	 *
	 * @var Schema
	 */
	protected $schema;

	/**
	 * Prevent direct construction.
	 */
	protected function __construct() {
		$this->schema         = Schema::instance();
		$this->reports_table = $this->schema->get_reports_table();
		$this->items_table   = $this->schema->get_items_table();
	}

	/**
	 * Get singleton instance.
	 *
	 * @return Reports_Repository
	 */
	public static function instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Create a new report record.
	 *
	 * @param array $data Report data.
	 * @return int|false Inserted report ID on success, false otherwise.
	 */
	public function create_report( array $data ) {
		global $wpdb;

		if ( ! isset( $data['month'], $data['year'] ) ) {
			return false;
		}

		$month = absint( $data['month'] );
		$year  = absint( $data['year'] );

		if ( $month < 1 || $month > 12 || 0 === $year ) {
			return false;
		}

		$now = current_time( 'mysql' );

		$inserted = $wpdb->insert(
			$this->reports_table,
			array(
				'month'      => $month,
				'year'       => $year,
				'created_at' => $now,
				'updated_at' => $now,
			),
			array( '%d', '%d', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return false;
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update an existing report.
	 *
	 * @param int   $report_id Report ID.
	 * @param array $data      Report data to update.
	 * @return bool True on success, false on failure.
	 */
	public function update_report( $report_id, array $data ) {
		global $wpdb;

		$fields = array();
		$format = array();

		if ( isset( $data['month'] ) ) {
			$fields['month'] = absint( $data['month'] );
			$format[]        = '%d';
		}

		if ( isset( $data['year'] ) ) {
			$fields['year'] = absint( $data['year'] );
			$format[]       = '%d';
		}

		$fields['updated_at'] = current_time( 'mysql' );
		$format[]             = '%s';

		if ( empty( $fields ) ) {
			return false;
		}

		$updated = $wpdb->update(
			$this->reports_table,
			$fields,
			array( 'id' => absint( $report_id ) ),
			$format,
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * Get a single report record.
	 *
	 * @param int $report_id Report ID.
	 * @return array|null Report data or null if not found.
	 */
	public function get_report( $report_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->reports_table} WHERE id = %d",
			absint( $report_id )
		);

		return $wpdb->get_row( $sql, ARRAY_A );
	}

	/**
	 * Get all items for a given report.
	 *
	 * @param int $report_id Report ID.
	 * @return array List of items.
	 */
	public function get_items_for_report( $report_id ) {
		global $wpdb;

		$sql = $wpdb->prepare(
			"SELECT * FROM {$this->items_table} WHERE log_id = %d ORDER BY sort_order ASC, id ASC",
			absint( $report_id )
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Save all items for a given report (replace existing items).
	 *
	 * @param int   $report_id Report ID.
	 * @param array $items     List of items to persist.
	 * @return bool True on success, false on failure.
	 */
	public function save_items( $report_id, array $items ) {
		global $wpdb;

		$report_id = absint( $report_id );

		$wpdb->delete( $this->items_table, array( 'log_id' => $report_id ), array( '%d' ) );

		foreach ( $items as $position => $item ) {
			$wpdb->insert(
				$this->items_table,
				array(
					'log_id'     => $report_id,
					'row_type'   => isset( $item['row_type'] ) ? sanitize_text_field( $item['row_type'] ) : '',
					'label'      => isset( $item['label'] ) ? sanitize_text_field( $item['label'] ) : '',
					'value'      => isset( $item['value'] ) ? wp_kses_post( $item['value'] ) : '',
					'sort_order' => isset( $item['sort_order'] ) ? intval( $item['sort_order'] ) : intval( $position ),
				),
				array( '%d', '%s', '%s', '%s', '%d' )
			);
		}

		return true;
	}
}

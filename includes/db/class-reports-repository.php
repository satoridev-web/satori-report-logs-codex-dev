<?php
/**
 * Reports repository for CRUD operations.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs\Db;

use Satori\Report_Logs\Logging\Logger;

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
         * Find a report by month and year.
         *
         * @param int $month Report month.
         * @param int $year  Report year.
         * @return array|null Report data or null if not found.
         */
        public function find_report_by_month_year( $month, $year ) {
                global $wpdb;

                $month = absint( $month );
                $year  = absint( $year );

                if ( $month < 1 || $month > 12 || 0 === $year ) {
                        return null;
                }

                $sql = $wpdb->prepare(
                        "SELECT * FROM {$this->reports_table} WHERE month = %d AND year = %d",
                        $month,
                        $year
                );

                return $wpdb->get_row( $sql, ARRAY_A );
        }

        /**
         * Get all reports ordered by year and month (desc).
         *
         * @return array
         */
        public function get_reports() {
                global $wpdb;

                $sql = "SELECT * FROM {$this->reports_table} ORDER BY year DESC, month DESC";

                return $wpdb->get_results( $sql, ARRAY_A );
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
         * Duplicate an existing report with all of its items.
         *
         * @param int   $log_id Source report ID.
         * @param array $args   Optional overrides for 'month' and 'year'.
         * @return int|false New log ID on success, false on failure.
         */
        public function duplicate_report( $log_id, array $args = array() ) {
                $log_id = absint( $log_id );

                if ( ! $log_id ) {
                        return false;
                }

                $report = $this->get_report( $log_id );

                if ( ! $report ) {
                        return false;
                }

                $month = isset( $args['month'] ) ? absint( $args['month'] ) : (int) $report['month'];
                $year  = isset( $args['year'] ) ? absint( $args['year'] ) : (int) $report['year'];

                if ( $month < 1 || $month > 12 || 0 === $year ) {
                        return false;
                }

                $new_log_id = $this->create_report(
                        array(
                                'month' => $month,
                                'year'  => $year,
                        )
                );

                if ( ! $new_log_id ) {
                        Logger::log(
                                'error',
                                'Failed to duplicate report {source} to {month}/{year}.',
                                array(
                                        'source' => $log_id,
                                        'month'  => $month,
                                        'year'   => $year,
                                )
                        );

                        return false;
                }

                $items = $this->get_items_for_report( $log_id );

                if ( ! empty( $items ) ) {
                        $this->save_items( $new_log_id, $items );
                }

                Logger::log(
                        'info',
                        'Report {source} duplicated as {target}.',
                        array(
                                'source' => $log_id,
                                'target' => $new_log_id,
                        )
                );

                return (int) $new_log_id;
        }

        /**
         * Get a single item value by label for a report.
         *
         * @param int    $report_id Report ID.
         * @param string $label     Item label to search for.
         * @return string|null Item value or null if not found.
         */
        public function get_item_value_by_label( $report_id, $label ) {
                global $wpdb;

                $sql = $wpdb->prepare(
                        "SELECT value FROM {$this->items_table} WHERE log_id = %d AND label = %s ORDER BY id DESC LIMIT 1",
                        absint( $report_id ),
                        sanitize_text_field( $label )
                );

                return $wpdb->get_var( $sql );
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

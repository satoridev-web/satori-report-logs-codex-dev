<?php
/**
 * Capability helpers for SATORI Report Logs.
 *
 * @package Satori_Report_Logs
 */

namespace Satori\Report_Logs;

/**
 * Capability helper class.
 */
class Capabilities {

	/**
	 * Get the required capability for managing report logs.
	 *
	 * @return string Capability name.
	 */
	public static function get_required_capability() {
		/**
		 * Filter the capability required for accessing SATORI Report Logs screens and actions.
		 *
		 * @param string $capability Capability name. Default 'manage_options'.
		 */
		return apply_filters( 'satori_report_logs_capability', 'manage_options' );
	}
}

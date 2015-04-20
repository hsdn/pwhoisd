<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd\Log;

/**
 * Log Interface.
 */
interface LogInterface {

	/**
	 * Adds debug message to log.
	 *
	 * @param   string  $message  Message to write
	 * @return  void
	 */
	public function debug($message);

	/**
	 * Adds info message to log.
	 *
	 * @param   string  $message  Message to write
	 * @return  void
	 */
	public function info($message);

	/**
	 * Adds warning message to log.
	 *
	 * @param   string  $message  Message to write
	 * @return  void
	 */
	public function warning($message);

	/**
	 * Adds error message to log.
	 *
	 * @param   string  $message  Message to write
	 * @return  void
	 */
	public function error($message);

} // end of interface LogInterface

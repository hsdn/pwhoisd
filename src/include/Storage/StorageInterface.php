<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd\Storage;

/**
 * Storage Interface.
 */
interface StorageInterface {

	/**
	 * Gets storage search result.
	 *
	 * @param   string  $request  Search string
	 * @return  array|bool
	 */
	public function get($request);


} // end of interface StorageInterface

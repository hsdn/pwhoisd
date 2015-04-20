<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd\Config;

/**
 * Config Interface.
 */
interface ConfigInterface extends \ArrayAccess
{
	/**
	 * Set one or more configuration values.
	 *
	 * @throws \InvalidArgumentException  If key must be a string or an associative array
	 * @param  string|array $key    Config key value or array of keys and values.
	 * @param  mixed        $value  Configuration value or null if $key is given an array.
	 * @return self
	 */
	public function set($key, $value = NULL);

	/**
	 * Check if a configuration value is set.
	 *
	 * @param  string $key  Confifuration key to check. If null if given it will check if any value is set at all.
	 * @return bool   True if the key exists, false if not.
	 */
	public function has($key = NULL);

	/**
	 * Get a configuration value.
	 *
	 * @throws \RuntimeException  Specified key not found in configuration
	 * @param  string $key     Configuration key whose value to get.
	 * @param  mixed  $default Default value if the searched key is not found.
	 * @return mixed  Matching Configuration value or $default if the key was not found.
	 */
	public function get($key = NULL, $default = NULL);

	/**
	 * Remove a configuration value.
	 *
	 * @param  string  $key  Configuration key to remove.
	 * @return self.
	 */
	public function remove($key);

	/**
	 * Clear all configuration values.
	 *
	 * @return void
	 */
	public function clear();

} // end of interface ConfigInterface

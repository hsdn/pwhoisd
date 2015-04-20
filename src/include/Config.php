<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd;

use pWhoisd\Application;
use pWhoisd\Config\ConfigAbstract;
use InvalidArgumentException;
use RuntimeException;

/**
 * Config class.
 */
class Config extends Config\ConfigAbstract {

	/**
	 * Returns instance of Config.
	 *
	 * @return Config
	 */
	public static function factory()
	{
		return new self;
	}

	/**
	 * Assigning class properties.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->load(realpath(Application::$arguments['config']));
	}

	/**
	 * Load a configuration file.
	 *
	 * @throws \InvalidArgumentException  If $file is not a valid file.
	 * @throws \RuntimeException          If $file does not return an array.
	 * @param  string  $file  Path to php file which returns an array.
	 * @return self
	 */
	public function load($file)
	{
		if (empty($file) OR !file_exists($file))
		{
			throw new InvalidArgumentException('Configuration file must be a valid file.');
		}

		$data = include $file;

		if (!is_array($data))
		{
			throw new RuntimeException('Configuration file did not return an array.');
		}

		return $this->set($data);
	}

} // end of class Config

<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd;

/**
 * Autoloader class.
 */
class Autoloader {

	/**
	 * Register the autoload callback
	 *
	 * @return  void
	 */
	static public function register()
	{
		ini_set('unserialize_callback_func', 'spl_autoload_call');

		spl_autoload_register(array(__CLASS__, 'autoload'));
	}

	/**
	 * Autoload callback
	 *
	 * @param   string  $class  Class name to loading
	 * @return  void
	 */
	static public function autoload($class)
	{
		$class_pos  = -strpos(strrev($class), '\\');
		$class_name = substr($class, $class_pos);
		$namespace  = substr($class, 0, $class_pos);

		if (strpos($namespace, __NAMESPACE__) !== 0)
		{
			return;
		}

		$namespace  = substr($namespace, strlen(__NAMESPACE__) + 1);
		$class_file = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $namespace.$class_name).'.php';
		$class_path = INCLUDE_PATH.DIRECTORY_SEPARATOR.$class_file;

		if(!is_readable($class_path))
		{
			echo 'Unable to load file: '.$class_path."\n";
			exit;
		}

		require_once $class_path;
	}

} // end of class Autoloader

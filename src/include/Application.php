<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd;

use pWhoisd\Console;
use pWhoisd\Log;
use pWhoisd\Config;
use pWhoisd\Server;
use RuntimeException;
use ErrorException;

define('PWHOISD_VERSION', '0.1.1b');

/**
 * Application class.
 */
class Application extends Daemon {

	/*
	 * @var  object  Instance of Config class
	 */
	public static $config;

	/*
	 * @var  object  Instance of Log class
	 */
	public static $log;

	/*
	 * @var  object  Instance of Security class
	 */
	public static $security;

	/*
	 * @var  object  Instance of Server class
	 */
	public static $server;


	/**
	 * Returns instance of Application.
	 *
	 * @return Application
	 */
	public static function factory()
	{
		return new self;
	}

	/**
	 * Loads command-line arguments, configuration and Server class.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->initialize();

		self::$config   = new Config;
		self::$log      = new Log;
		self::$security = new Security;
		self::$server   = new Server;

		if (!self::$arguments['daemon'])
		{
			Console::head();
		}

		self::$log->debug('Configuration loaded');
	}

	/**
	 * Initialize application
	 *
	 * @return  void
	 */
	private function initialize()
	{
		set_time_limit(0);

		$this->set_exception_handlers();
		$this->test_dependencies();
		$this->assign_arguments();

		if (!self::$arguments['daemon'])
		{
			Console::help();
		}

		if (self::$arguments['help'])
		{
			exit;
		}

		$this->set_identifiers();
		$this->set_signal_handlers();
	}

	/**
	 * Sets exception and error handlers
	 *
	 * @return  void
	 */
	private function set_exception_handlers()
	{
		set_exception_handler([$this, 'exception_handler']);
		set_error_handler([$this, 'error_handler']);
	}

	/**
	 * Test application dependencies.
	 *
	 * @throws  \RuntimeException  If require dependence
	 * @return  void
	 */
	private function test_dependencies()
	{
		version_compare(PHP_VERSION, '5.4', '<') and die('Requires PHP 5.4 or newer.');

		if (!extension_loaded('posix'))
		{
			throw new RuntimeException('Requires POSIX functions support (https://php.net/manual/en/posix.installation.php)');
		}

		if (!extension_loaded('pcntl'))
		{
			throw new RuntimeException('Requires PCNTL extension (http://www.php.net/manual/en/pcntl.installation.php)');
		}

		if (!extension_loaded('filter'))
		{
			throw new RuntimeException('Requires filter extension (http://www.php.net/manual/en/filter.installation.php)');
		}

		if (!extension_loaded('gmp') AND !extension_loaded('bcmath'))
		{
			throw new RuntimeException('Requires GMP or BCMATH extension (http://www.php.net/manual/en/gmp.installation.php)');
		}

		if (!extension_loaded('sockets'))
		{
			throw new RuntimeException('Requires sockets extension (http://www.php.net/manual/en/sockets.installation.php)');
		}
	}

	/**
	 * Exception handler method.
	 *
	 * @return  void
	 */
	public function exception_handler(\Exception $exception)
	{
		$message = get_class($exception).': '.$exception->getMessage();
		$log     = Application::$log;

		if (is_object($log))
		{
			$log->error($message);
		}
		else
		{
			Console::log($message, 'red');
		}

		// Terminate process if exception called before server loop
		if (is_null(Application::$server) OR Application::$server->listen_loop === FALSE)
		{
			Application::terminate(FALSE);
		}
	}

	/**
	 * Error handler method.
	 *
	 * @return  void
	 */
	public function error_handler($severity, $message, $file, $line)
	{
		if (!(error_reporting() & $severity))
		{
			return;
		}

		throw new ErrorException('PHP Error: '.$message.'; file: '.$file.'; line: '.$line);
	}

} // end of class Application

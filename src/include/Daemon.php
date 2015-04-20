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
use RuntimeException;
use ErrorException;

/**
 * Abstract Daemon class.
 */
abstract class Daemon {

	/*
	 * @var  array    Command-line arguments defaults
	 */
	public static $arguments =
	[
		'uid'     => FALSE,
		'gid'     => FALSE,
		'pidfile' => FALSE,
		'daemon'  => FALSE,
		'help'    => FALSE,
		'config'  => 'config.php',
	];

	/*
	 * @var  int     Process identifier
	 */
	protected static $pid;


	/**
	 * Run and listen the server.
	 *
	 * @return  void
	 */
	public function run()
	{
		Application::$log->debug('Process run as UID/GID: '.posix_getuid().'/'.posix_getgid());
		Application::$server->initialize();

		// Transfer process in the background mode
		$this->fork();
		$this->write_pid();

		Application::$server->loop();
	}

	/**
	 * Set the UID/GID of the current process
	 *
	 * @throws  \RuntimeException  If error while changing identifiers
	 * @return  void
	 */
	protected function set_identifiers()
	{
		if (is_string(self::$arguments['uid']))
		{
			if (!posix_setuid(self::$arguments['uid']))
			{
				throw new RuntimeException('Can\'t change UID for process');
			}
		}

		if (is_string(self::$arguments['gid']))
		{
			if (!posix_setuid(self::$arguments['gid']))
			{
				throw new RuntimeException('Can\'t change GID for process');
			}
		}
	}

	/**
	 * Sets signal processign handlers
	 *
	 * @return  void
	 */
	protected function set_signal_handlers()
	{
		pcntl_signal(SIGTERM, [__CLASS__, 'terminate']);
		pcntl_signal(SIGINT, [__CLASS__, 'terminate']);
	}

	/**
	 * Parse and assigning command-line arguments.
	 *
	 * @return  void
	 */
	protected function assign_arguments()
	{
		if (isset($GLOBALS['argv']) AND is_array($GLOBALS['argv']))
		{
			foreach (array_slice($GLOBALS['argv'], 1) as $argument)
			{
				@list($param, $value) = explode('=', $argument);

				if (substr($param, 0, 2) !== '--')
				{
					continue;
				}

				$param = ltrim($param, '-');

				if (isset(self::$arguments[$param]))
				{
					self::$arguments[$param] = empty($value) ? TRUE : $value;
				}
			}
		}
	}

	/**
	 * Writes pid-file to specified path.
	 *
	 * @throws  \RuntimeException  If error while creating pid-file
	 * @return  void
	 */
	protected function write_pid()
	{
		if (!self::$pid = posix_getpid())
		{
			throw new RuntimeException('Can\'t get process ID');
		}

		if (is_string(self::$arguments['pidfile']))
		{
			if (!@file_put_contents(self::$arguments['pidfile'], self::$pid.PHP_EOL))
			{
				throw new RuntimeException('Can\'t create pid-file for process');
			}

			self::$arguments['pidfile'] = realpath(self::$arguments['pidfile']);
		}
	}

	/**
	 * Delete pid-file from specified path.
	 *
	 * @throws  \RuntimeException  If error while creating pid-file
	 * @return  void
	 */
	protected static function delete_pid()
	{
		if (is_string(self::$arguments['pidfile']) AND file_exists(self::$arguments['pidfile']))
		{
			@unlink(self::$arguments['pidfile']);
		}
	}

	/**
	 * Process fork function.
	 *
	 * @return  void
	 */
	protected function fork()
	{
		if (!self::$arguments['daemon'])
		{
			return;
		}

		switch ($pid = pcntl_fork())
		{
			case -1: throw new RuntimeException('Unable to fork process');
			case  0: break;
			default:
				Application::$log->info('Process running in background mode on PID: '.$pid);
				exit;
		}

		fclose(STDIN);
		fclose(STDOUT);
		fclose(STDERR);

		$GLOBALS['STDIN']  = fopen('/dev/null', 'r');
		$GLOBALS['STDOUT'] = fopen('/dev/null', 'w');
		$GLOBALS['STDERR'] = fopen('php://stdout', 'w');

		if (posix_setsid() === -1)
		{
			throw new RuntimeException('Could not set process ID');
		}
	}

	/**
	 * Terminate process
	 *
	 * @param   bool  $delete_pid  Set FALSE to keep pid-file
	 * @return  void
	 */
	public static function terminate($delete_pid = TRUE)
	{
		if ($delete_pid)
		{
			Application::delete_pid();
		}

		if (!is_null(Application::$server))
		{
			Application::$server->listen_loop = FALSE;
			Application::$server->close();
		}

		if (!is_null(Application::$log))
		{
			Application::$log->debug('Process terminated');
		}

		exit;
	}

	/**
	 * Process tick function.
	 *
	 * @return  void
	 */
	public static function tick()
	{
		pcntl_signal_dispatch();

		usleep(10000);
	}

} // end of class Daemon

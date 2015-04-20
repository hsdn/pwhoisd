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

/**
 * Log class.
 */
class Log extends Log\LogAbstract {

	/**
	 * Returns instance of Log.
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
		$this->severity = Application::$config->get('logging.severity', FALSE);
		$this->file     = Application::$config->get('logging.file', FALSE);
	}

	/**
	 * Adds any message to log.
	 *
	 * @param   string  $message   Message to write
	 * @param   int     $severity  Message severity
	 * @return  void
	 */
	public function add($message, $severity = self::info)
	{
		$message = '['.$this->severities[$severity][0].'] '.$message;

		if ($severity < self::debug OR $this->severity >= self::debug)
		{
			Console::log($message, $this->severities[$severity][1]);
		}

		if ($this->severity AND $this->file AND $severity <= $this->severity)
		{
			@file_put_contents($this->file, '['.date('Y-m-d H:i:s').'] '.$message.PHP_EOL, FILE_APPEND);
		}
	}

	/**
	 * Gets name of the calling method class
	 *
	 * @return  string
	 */
	public function get_calling_class()
	{
		$trace = debug_backtrace();

		$class = $trace[1]['class'];

		for ($i = 1; $i < count($trace); $i++)
		{
			if (isset($trace[$i]))
			{
				if ($class != $trace[$i]['class'])
				{
					return preg_replace('/^'.__NAMESPACE__.'\\\/', '', $trace[$i]['class']);
				}
			}
		}
	}

} // end of class Log

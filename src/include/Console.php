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
 * Console class.
 */
class Console {

	/*
	 * @var array  Color codes
	 */
	private static $color = 
	[
		'red'    => 31,
		'green'  => 32,
		'brown'  => 33,
		'blue'   => 34,
		'purple' => 35,
		'cyan'   => 36,
		'grey'   => 37,
		'yellow' => 33
	];

	/**
	 * Print help message.
	 *
	 * @return void
	 */
	public static function help()
	{
		$message =
			'Server version: pWhoisd/'.PWHOISD_VERSION.PHP_EOL.
			'Usage: '.$GLOBALS['argv'][0].' [--config=file] [--pidfile=file] [--uid=identifier] [--gid=identifier] [--daemon]'.PHP_EOL.
			'Options:                                   '.PHP_EOL.
			'  --config : main configuration file       '.PHP_EOL.
			'  --pidfile: file to save process ID       '.PHP_EOL.
			'  --uid    : specify an UID for process    '.PHP_EOL.
			'  --gid    : specify an GID for process    '.PHP_EOL.
			'  --daemon : run process in background mode'.PHP_EOL.
			'  --help   : show this help only           ';

		self::print_message($message);
	}

	/**
	 * Print header message.
	 *
	 * @return void
	 */
	public static function head()
	{
		$message =
			'    __          ___           _         _ '.PHP_EOL.
			'    \ \        / / |         (_)       | |'.PHP_EOL.
			'  _ _\ \  /\  / /| |__   ___  _ ___  __| |'.PHP_EOL.
			" | '_ \ \/  \/ / | '_ \ / _ \| / __|/ _` |".PHP_EOL.
			' | |_) \  /\  /  | | | | (_) | \__ \ (_| |'.PHP_EOL.
			' | .__/ \/  \/   |_| |_|\___/|_|___/\__,_|'.PHP_EOL.
			' | |                                      '.PHP_EOL.
			' |_|   HSDN PHP Whois Server Daemon       '.PHP_EOL;

		self::print_message($message);
	}

	/**
	 * Print any message.
	 *
	 * @param  string  Message text
	 * @param  string  Color name
	 * @return void
	 */
	public static function log($message, $color = 'green')
	{
		self::print_message($message, $color);
	}

	/**
	 * Output message to console.
	 *
	 * @param  string  Message text
	 * @param  string  Color name
	 * @return void
	 */
	private static function print_message($message, $color = NULL)
	{
		echo self::colorize($message, $color).PHP_EOL;
	}

	/**
	 * Adds bash color codes to string.
	 *
	 * @param  string  Message text
	 * @param  string  Color name
	 * @return string
	 */
	private static function colorize($string, $color = NULL)
	{
		if (isset(self::$color[$color]))
		{
			return "\033[".self::$color[$color].'m'.$string."\033[0m";
		}

		return $string;
	}

} // end of class Console

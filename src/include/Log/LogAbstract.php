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
 * Abstract Log implementing LogInterface.
 */
abstract class LogAbstract implements LogInterface {

	/*
	 * @const  int   Error messages level
	 */
	const error   = 1;

	/*
	 * @const  int   Warning messages level
	 */
	const warning = 2;

	/*
	 * @const  int   Info messages level
	 */
	const info   = 3;

	/*
	 * @const  int   Debug messages level
	 */
	const debug   = 4;

	/*
	 * @var  int     Logging severity
	 */
	protected $severity;

	/*
	 * @var  string  Logging file path
	 */
	protected $path;

	/*
	 * @var  array   Severity names
	 */
	protected $severities =
	[
		self::debug   => ['debug',   'cyan'],
		self::info    => ['info',    'green'],
		self::warning => ['warning', 'yellow'],
		self::error   => ['error',   'red'],
	];


	/**
	 * {@inheritdoc}
	 */
	public function debug($message)
	{
		$this->add($this->get_calling_class().': '.$message, self::debug);
	}

	/**
	 * {@inheritdoc}
	 */
	public function info($message)
	{
		$this->add($message, self::info);
	}

	/**
	 * {@inheritdoc}
	 */
	public function warning($message)
	{
		$this->add($message, self::warning);
	}

	/**
	 * {@inheritdoc}
	 */
	public function error($message)
	{
		$this->add($message, self::error);
	}

} // end of abstract class LogAbstract

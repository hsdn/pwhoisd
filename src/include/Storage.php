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

/**
 * Storage class.
 */
class Storage {

	/*
	 * @var  object  Instance of Client class
	 */
	private $client;

	/*
	 * @var  object  Instance of Storage Provider class
	 */
	private $provider;

	/*
	 * @var  array   Storage configuration segment
	 */
	private $storage;


	/**
	 * Assigning class properties.
	 *
	 * @param   object  $client   Instance of Client class
	 * @param   array   $storage  Storage configuration segment
	 * @return  void
	 */
	public function __construct(Client $client, $storage)
	{
		$this->client  = $client;
		$this->storage = $storage;

		$this->load_provider();
	}

	/**
	 * Gets response data from storage provider.
	 *
	 * @param   string  $request  Requested search string
	 * @return  array
	 */
	public function get($request)
	{
		$result = [];

		if (!is_null($this->provider))
		{
			$result = $this->provider->get($request);
		}

		return $result;
	}

	/**
	 * Loads storage provider class.
	 *
	 * @throws  \RuntimeException  If storage provider class does not exists
	 * @return  void
	 */
	private function load_provider()
	{
		if (!isset($this->storage['type']) OR empty($this->storage['type']))
		{
			return;
		}

		$type  = $this->storage['type'];
		$class = __NAMESPACE__.'\\Storage\\'.ucfirst($type).'Provider';

		if (!class_exists($class))
		{
			throw new RuntimeException('Storage provider class "'.$class.'" does not exists');
		}

		$this->provider = new $class($this->client, $this->storage);

		Application::$log->debug('Storage provider "'.$type.'" is loaded');
	}

} // end of class Storage

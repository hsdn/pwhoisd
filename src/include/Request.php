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
use pWhoisd\Storage;

/**
 * Request class.
 */
class Request extends Response {

	/*
	 * @var  object  Instance of Client class
	 */
	protected $client;


	/**
	 * Assigning class properties.
	 *
	 * @param   object  $client    Instance of Client class
	 * @return  void
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}

	/**
	 * Parse and process request and response.
	 *
	 * @return  void
	 */
	public function process()
	{
		if ($message = Application::$security->get_message())
		{
			$this->response = $this->process_message($message);
		}

		if (Application::$security->get_action() == 'deny')
		{
			Application::$log->warning('Access denied by security');

			return;
		}

		$this->define_data_section();
		$this->define_data_formats();

		Application::$log->debug('Request processed');

		if (isset($this->data_section['storage']) AND is_array($this->data_section['storage']))
		{
			if (!empty($this->request))
			{
				$storage = new Storage($this->client, $this->data_section['storage']);

				$this->response_array = $storage->get($this->request);
			}
		}

		$this->process_response_array();
		$this->process_response();

		Application::$log->debug('Response processed');
	}

	/**
	 * Search and define section data.
	 *
	 * @return  void
	 */
	private function define_data_section()
	{
		$data = Application::$config->get('data');

		foreach ($data as $section)
		{
			if (!isset($section['flag']) OR !isset($section['fields']))
			{
				continue;
			}

			if (!empty($section['flag']) AND $this->find_flag($section['flag']))
			{
				$this->data_section = $section;
			}
		}

		if (is_null($this->data_section))
		{
			$this->data_section = array_shift($data);
		}
	}

	/**
	 * Search and define data formats.
	 *
	 * @return  void
	 */
	private function define_data_formats()
	{
		if (!isset($this->data_section['format']) OR !is_array($this->data_section['format']))
		{
			return;
		}

		$data_formats = [];

		foreach ($this->data_section['format'] as $key => $section)
		{
			if (!isset($section[2]) OR empty($section[2]))
			{
				$data_formats[$key] = $section;
			}
			else if ($this->find_flag($section[2]))
			{
				$data_formats[$key] = $section;
			}
		}

		if (!empty($data_formats))
		{
			$this->data_formats = $data_formats;
		}
	}

	/**
	 * Find flag in request string.
	 *
	 * @param   string  $flag  Flag string to search
	 * @return  bool
	 */
	private function find_flag($flag)
	{
		if (preg_match('/\s+'.preg_quote($flag).'\s+/', ' '.$this->request.' '))
		{
			$this->request = preg_replace('/\s+'.preg_quote($flag).'\s+/', ' ', ' '.$this->request.' ', 1);
			$this->request = trim($this->request);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Sets current request string
	 *
	 * @param   string  $request  Request string to set
	 * @return  void
	 */
	public function set_request($request)
	{
		$this->request = $this->complete_request = trim($request);
	}

} // end of class Request

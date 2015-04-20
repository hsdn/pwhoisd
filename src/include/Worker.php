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
use pWhoisd\Request;
use RuntimeException;

/**
 * Worker class.
 */
class Worker {

	/*
	 * @var  object  Instance of Client class
	 */
	private $client;

	/*
	 * @var  object  Instance of Request class
	 */
	private $request;


	/**
	 * Assigning class properties.
	 *
	 * @param   object  $client    Instance of Client class
	 * @return  void
	 */
	public function __construct(Client $client)
	{
		$this->client   = $client;
		$this->request  = new Request($client);
	}

	/**
	 * Runs a worker to client request processing.
	 *
	 * @return  bool
	 */
	public function loop()
	{
		$time = time() + 3;

		while ($time > time())
		{
			$read = $this->client->read();

			if ($read === NULL)
			{
				continue;
			}

			try
			{
				if (Application::$security->get_action() == 'drop')
				{
					Application::$log->warning('Connection dropped by security');

					return TRUE;
				}

				$this->request->set_request($read);
				$this->request->process();

				if ($response = $this->request->get_response())
				{
					$this->client->send($response);
				}
			}
			catch (\Exception $e)
			{
				$this->client->send('Internal error! Please try again later.');

				throw new RuntimeException($e->getMessage());
			}

			return TRUE;
		}

		Application::$log->warning('Request is not readed from client socket');

		return FALSE;
	}

	/**
	 * Waits on or returns the status of a forked worker process.
	 *
	 * @return  int
	 */
	public function wait()
	{
		return pcntl_waitpid(-1, $status, WNOHANG);
	}

	/**
	 * Forks the currently running worker process.
	 *
	 * @return  int
	 */
	public function fork()
	{
		$pid = pcntl_fork();

		if ($pid == -1)
		{
			$this->client->close();

			exit;
		}

		return $pid;
	}

} // end of class Worker

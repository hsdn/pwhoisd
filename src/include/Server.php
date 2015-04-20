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
use pWhoisd\Socket;
use pWhoisd\Client;
use pWhoisd\Worker;
use RuntimeException;

/**
 * Server class.
 */
class Server {

	/*
	 * @var  object    Instance of Socket class for IPv4 connections
	 */
	private $socket;

	/*
	 * @var  object    Instance of Socket class for IPv6 connections
	 */
	private $socket_ipv6;

	/*
	 * @var  string    Server is now processing
	 */
	public $listen_loop;


	/**
	 * Assigning class properties and create socket.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->listen_loop = FALSE;

		$listen_port = Application::$config->get('daemon.listen_port', 43);

		$this->socket      = new Socket(AF_INET,  Application::$config->get('daemon.listen_address', FALSE), $listen_port);
		$this->socket_ipv6 = new Socket(AF_INET6, Application::$config->get('daemon.listen_address_ipv6', FALSE), $listen_port);
	}

	/**
	 * Initialize server.
	 *
	 * @return  void
	 */
	public function initialize()
	{
		$this->socket->initialize();
		$this->socket_ipv6->initialize();
	}

	/**
	 * Runs a main loop for accepting server requests.
	 *
	 * @throws  \RuntimeException  If error while accept connections
	 * @throws  \RuntimeException  If error called in Worker
	 * @return  void
	 */
	public function loop()
	{
		Application::$log->info('Server ready to accept connections');

		$this->listen_loop = TRUE;

		$worker_processes = [];

		while ($this->listen_loop)
		{
			Application::tick();

			if (($client_socket = $this->accept()) === FALSE)
			{
				continue;
			}

			Application::$log->debug('Server socket accept new connection');
			Application::$log->debug('Client socket created');

			$client = new Client($client_socket);
			$worker = new Worker($client);

			while ($pid = $worker->wait())
			{
				if ($pid == -1)
				{
					$worker_processes = [];

					break;
				}

				unset($worker_processes[$pid]);

				// Forces collection of any existing garbage cycles
				gc_collect_cycles();
			}

			if (count($worker_processes) > Application::$config->get('daemon.workers'))
			{
				Application::$log->warning('Workers limit exceded');

				$client->close();

				continue;
			}

			try
			{
				Application::$security->initialize($client);

				if ($pid = $worker->fork())
				{
					$worker_processes[$pid] = TRUE;

					Application::$log->debug('Worker process created');

					continue;
				}

				$worker->loop();
				$client->close();

				Application::$log->debug('Worker process terminated');

				exit;
			}
			catch (\Exception $e)
			{
				$client->close();

				throw new RuntimeException($e->getMessage());
			}
		}
	}

	/**
	 * Closes a server socket resource.
	 *
	 * @return  void
	 */
	public function close()
	{
		$this->socket->close();
		$this->socket_ipv6->close();
	}

	/**
	 * Accept connection
	 *
	 * @return  resource|bool
	 */
	private function accept()
	{
		if (($socket = $this->socket->accept()) === FALSE)
		{
			if (($socket = $this->socket_ipv6->accept()) === FALSE)
			{
				return FALSE;
			}
		}

		return $socket;
	}

} // end of class Server

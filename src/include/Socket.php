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
 * Socket class.
 */
class Socket {

	/*
	 * @var  resource  Server socket resource
	 */
	private $socket;

	/*
	 * @var  string    Listen IP address
	 */
	private $listen_address;

	/*
	 * @var  int       Listen port
	 */
	private $listen_port;

	/*
	 * @var  int       Socket domain
	 */
	private $domain;


	/**
	 * Assigning class properties and create socket.
	 *
	 * @return  void
	 */
	public function __construct($domain, $listen_address, $listen_port)
	{
		$this->domain         = $domain;
		$this->listen_address = $listen_address;
		$this->listen_port    = $listen_port;
	}

	/**
	 * Initialize server.
	 *
	 * @return  void
	 */
	public function initialize()
	{
		if ($this->domain AND $this->listen_address AND $this->listen_port)
		{
			$this->create();
			$this->bind();
			$this->listen();
		}
	}

	/**
	 * Accept a socket connection.
	 *
	 * @return  bool
	 */
	public function accept()
	{
		if (is_resource($this->socket))
		{
			return @socket_accept($this->socket);
		}

		return FALSE;
	}

	/**
	 * Closes a server socket resource.
	 *
	 * @return  void
	 */
	public function close()
	{
		if (is_resource($this->socket))
		{
			@socket_close($this->socket);

			Application::$log->debug('Server socket closed');
		}
	}

	/**
	 * Creates a server socket resource and set socket options.
	 *
	 * @throws  \RuntimeException  If error while creating socket
	 * @return  void
	 */
	protected function create()
	{
		$this->socket = @socket_create($this->domain, SOCK_STREAM, SOL_TCP);

		if ($this->socket === FALSE)
		{
			throw new RuntimeException('Can\'t create socket: '.socket_strerror(socket_last_error()));
		}

		socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO,
		[
			'sec'  => 3,
			'usec' => 0
		]);

		Application::$log->debug('Server socket created');
	}

	/**
	 * Binds a server socket.
	 *
	 * @throws  \RuntimeException  If error while binding socket
	 * @return  void
	 */
	protected function bind()
	{
		if (is_resource($this->socket))
		{
			if (@socket_bind($this->socket, $this->listen_address, $this->listen_port) === FALSE)
			{
				throw new RuntimeException('Can\'t bind socket: '.socket_strerror(socket_last_error($this->socket)));
			}

			Application::$log->debug('Server socket binded');
		}
	}

	/**
	 * Listens for a connection on a server socket.
	 *
	 * @throws  \RuntimeException  If error while listening socket
	 * @return  bool
	 */
	protected function listen()
	{
		if (is_resource($this->socket))
		{
			if(@socket_listen($this->socket, 5) === FALSE)
			{
				throw new RuntimeException('Can\'t listen: '.socket_strerror(socket_last_error($this->socket)));
			}

			@socket_set_nonblock($this->socket);

			Application::$log->info('Server listening on '.$this->listen_address.':'.$this->listen_port.'...');
		}
	}

} // end of class Socket

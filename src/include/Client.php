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

/**
 * Client class.
 */
class Client {

	/*
	 * @var  resource  Worker socket resource
	 */
	private $socket;

	/*
	 * @var  string    Current client IP address
	 */
	private $address;

	/*
	 * @var  int       Current client port
	 */
	private $port;


	/**
	 * Assigning class properties.
	 *
	 * @param   object  $socket  Worker socket resource
	 * @return  void
	 */
	public function __construct($socket)
	{
		$this->socket = $socket;

		@socket_getsockname($this->socket, $this->address, $this->port);

		Application::$log->debug('Socket assigned for client '.$this->address.':'.$this->port);
		Application::$log->info('['.$this->address.'] Connected at port '.$this->port);
	}

	/**
	 * Writes message to socket
	 *
	 * @param   string  $message  Message to write
	 * @return  void
	 */
	public function send($message)
	{
		$message = str_replace(["\r", "\n"], ['', "\r\n"], $message)."\r\n";

		@socket_write($this->socket, $message, strlen($message));

		Application::$log->info('['.$this->address.'] Response sended (see debug)');
		Application::$log->debug('Message writed to client socket: '.PHP_EOL.$message);
	}

	/**
	 * Reads data from socket
	 *
	 * @param   int    $len  Read data length
	 * @return  string
	 */
	public function read($len = 1024)
	{
		if (($buffer = @socket_read($this->socket, $len, PHP_BINARY_READ)) === FALSE)
		{
			return NULL;
		}

		Application::$log->info('['.$this->address.'] Request Recieved: '.trim($buffer));
		Application::$log->debug('Request readed from client socket: '.$buffer);

		return $buffer;
	}

	/**
	 * Shutdown and Closes Worker socket
	 *
	 * @return  void
	 */
	public function close()
	{
		@socket_shutdown($this->socket);
		@socket_close($this->socket);

		Application::$log->debug('Client socket closed');
		Application::$log->info('['.$this->address.'] Disconnected');
	}

	/**
	 * Gets current client IP address
	 *
	 * @return  string
	 */
	public function get_address()
	{
		return $this->address;
	}

	/**
	 * Gets current client port
	 *
	 * @return  int
	 */
	public function get_port()
	{
		return $this->port;
	}

} // end of class Client

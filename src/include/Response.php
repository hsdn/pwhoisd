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
 * Abstract Response class.
 */
abstract class Response {

	/*
	 * @var  string  Request string
	 */
	protected $request;

	/*
	 * @var  string  Complete request string
	 */
	protected $complete_request;

	/*
	 * @var  array   Array of assigned data configuration section
	 */
	protected $data_section;

	/*
	 * @var  array   Array of assigned formats configuration
	 */
	protected $data_formats;

	/*
	 * @var  string  Response string
	 */
	protected $response;

	/*
	 * @var  array   Response array
	 */
	protected $response_array;


	/**
	 * Process response.
	 *
	 * @return  void
	 */
	protected function process_response()
	{
		if (!is_array($this->data_section['fields']) OR empty($this->data_section['fields']))
		{
			return;
		}

		$hide_empty = TRUE;

		if (isset($this->data_section['hide_empty']))
		{
			$hide_empty = $this->data_section['hide_empty'];
		}

		$response_array = $response_array_names_len = [];

		foreach ($this->data_section['fields'] as $field)
		{
			if (!is_array($field))
			{
				continue;
			}

			if (empty($field))
			{
				$response_array[] = '';

				continue;
			}

			$field[0] = $this->process_response_macro($field[0]);

			if ($field[0] === FALSE)
			{
				continue;
			}

			if (sizeof($field) > 1)
			{
				$field_flag = isset($field[2]) ? $field[2] : NULL;

				if (isset($this->response_array[$field[1]]) AND $this->response_array[$field[1]] !== NULL)
				{
					if (!empty($this->response_array[$field[1]]) OR $hide_empty === FALSE)
					{
						if ($field_flag === NULL)
						{
							$values = explode("\n", str_replace("\r\n", "\n", $this->response_array[$field[1]]));

							if (!preg_match('/\:$/', trim($field[0])))
							{
								$field[0] .= ':';
							}

							foreach ($values as $value)
							{
								$response_array[] = [$field[0], $value];
							}

							$response_array_names_len[] = strlen($field[0]);
						}
						elseif ($field_flag === TRUE)
						{
							$response_array[] = $field[0];
						}
					}
				}
				elseif ($field_flag === FALSE)
				{
					$response_array[] = $field[0];
				}
			}
			else
			{
				$response_array[] = $field[0];
			}
		}

		$max_len = 0;

		if (!empty($response_array_names_len))
		{
			$max_len = max($response_array_names_len);
		}

		$response = [];

		foreach ($response_array as $field)
		{
			if (is_array($field))
			{
				if (isset($this->data_section['spacing']) AND $this->data_section['spacing'])
				{
					$response[] = $field[0].' '.str_repeat(' ', $max_len - strlen($field[0])).$field[1];
				}
				else
				{
					$response[] = $field[0].$field[1];
				}
			}
			else
			{
				$response[] = $field;
			}
		}

		// Show invalid_request message
		if ((empty($this->complete_request) AND empty($this->response_array)) OR empty($response))
		{
			$invalid_request = ['Invalid request.'];

			if (isset($this->data_section['invalid_request']))
			{
				$invalid_request = $this->data_section['invalid_request'];
			}

			$response = [$this->process_message($invalid_request)];
		}

		$this->response = implode("\n", $response);
	}

	/**
	 * Process response array.
	 *
	 * @return  void
	 */
	protected function process_response_array()
	{
		if (!is_array($this->response_array) OR empty($this->response_array))
		{
			return;
		}

		if (is_array($this->data_formats) AND !empty($this->data_formats))
		{
			foreach ($this->data_formats as $data_format)
			{
				if (sizeof($data_format) < 2)
				{
					continue;
				}

				@list($field, $format) = $data_format;

				if (($eval = $this->process_response_macro($format, TRUE)) === FALSE)
				{
					continue;
				}

				if (!empty($eval))
				{
					eval("\$format = $eval;");
				}

				if (isset($this->response_array[$field]) AND $this->response_array[$field] == '0000-00-00 00:00:00')
				{
					$this->response_array[$field] = '';
				}
				else
				{
					$this->response_array[$field] = trim($format);
				}
			}
		}
	}

	/**
	 * Process message.
	 *
	 * @param   string  $message    Message string to process
	 * @param   string  $recursion  Enables recursions in 'process_response_macro' method
	 * @return  string
	 */
	protected function process_message($message, $quote = FALSE, $recursion = TRUE)
	{
		if (!is_array($message))
		{
			$message = [$message];
		}

		$lines = [];

		foreach ($message as $line)
		{
			$line = $this->process_response_macro($line, $quote, $recursion);

			if ($line !== FALSE)
			{
				$lines[] = $line;
			}
		}

		return implode("\n", $lines);
	}

	/**
	 * Process macro in response string.
	 *
	 * @param   string  $string     Response string to process
	 * @param   bool    $quote      Quoting macro value
	 * @param   string  $recursion  Enables recursions
	 * @return  string|bool
	 */
	protected function process_response_macro($string, $quote = FALSE, $recursion = TRUE)
	{
		// System macro
		$macros = [
			'_request_'     => $this->request,
			'_client_ip_'   => $this->client->get_address(),
			'_client_port_' => $this->client->get_port(),
		];

		// Messages based macro
		foreach (Application::$config->get('messages') as $message_key => $message_value)
		{
			$macros['%'.$message_key.'%'] = $message_value;
		}

		// Storage response macro
		if (is_array($this->response_array) AND !empty($this->response_array))
		{
			$macros += $this->response_array;
		}

		foreach ($macros as $macro => $value)
		{
			if ($value !== NULL AND strpos($string, '{'.$macro.'}') !== FALSE)
			{
				// Recursion
				if (is_array($value))
				{
					if ($recursion === FALSE)
					{
						continue;
					}

					$value = $this->process_message($value, $quote, FALSE);
				}

				if ($quote)
				{
					$value = '"'.$value.'"';
				}

				$string = str_replace('{'.$macro.'}', $value, $string);
			}
		}

		return !preg_match('/\{[\%\w]+\}/', $string) ? $string : FALSE;
	}

	/**
	 * Gets response string.
	 *
	 * @return  string
	 */
	public function get_response()
	{
		return $this->response;
	}

} // end of class Response

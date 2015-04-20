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
use pWhoisd\Inet;

/**
 * Security class.
 */
class Security {

	/*
	 * @const  int  One second period
	 */
	const interval_second = 1;

	/*
	 * @const  int  One minute period
	 */
	const interval_minute = 60;

	/*
	 * @const  int  One hour period
	 */
	const interval_hour   = 3600;

	/*
	 * @const  int  One day period
	 */
	const interval_day    = 86400;

	/*
	 * @var  array   Interval pools [[interval, time(), key, counter], ...]
	 */
	private $interval_pool = [];

	/*
	 * @var  object  Instance of Client class
	 */
	private $client;

	/*
	 * @var  array   Access control rules
	 */
	private $rules;

	/*
	 * @var  string  Message sent to client
	 */
	private $message;

	/*
	 * @var  string  Last assigned action
	 */
	private $action;


	/**
	 * Assigning class properties and parse rules configuration.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->rules = Application::$config->get('security.rules');

		$this->parse_rules();
	}

	/**
	 * Initialize security by client request.
	 *
	 * @param   object  $client  Instance of Client class
	 * @return  void
	 */
	public function initialize(Client $client)
	{
		$this->client = $client;

		$this->process_rules();
	}

	/**
	 * Process defined access control rules.
	 *
	 * @return  void
	 */
	private function process_rules()
	{
		$this->message   = NULL;
		$this->action    = NULL;
		$this->intervals = [];

		foreach (array_reverse($this->rules) as $rule)
		{
			if ($this->process_conditions($rule['conditions']))
			{
				$this->message = Application::$config->get('messages.'.$rule['message'], FALSE);
				$this->action  = $rule['action'];
			}
		}

		// Update presented intervals
		foreach ($this->intervals as $key => $interval)
		{
			$this->interval_check($interval, $key, TRUE);
		}
	}

	/**
	 * Process rule condition.
	 *
	 * @return  bool  TRUE if all rules is accepted
	 */
	private function process_conditions($conditions)
	{
		if (empty($conditions))
		{
			return TRUE;
		}

		$client_address = $this->client->get_address();

		$expressions = [TRUE];

		foreach (['client_ip', 'requests', 'rate'] as $matched_condition)
		{
			foreach ($conditions as $condition)
			{
				$expression = NULL;

				if ($condition['l'] != $matched_condition)
				{
					continue;
				}

				switch ($condition['l'])
				{
					// Match client IP policy
					case 'client_ip':
						$expression =  $this->compare_client_ip($condition['r']);
						break;

					// Client-based interval policy
					case 'requests':
						$expression = $this->compare_interval
						(
							$condition['op'],
							$condition['r'],
							$client_address
						);
						break;

					// Global interval policy
					case 'rate':
						$expression = $this->compare_interval
						(
							$condition['op'],
							$condition['r']
						);
						break;
				}

				if ($expression !== NULL)
				{
					$expressions[] = $expression;

					if ($expression === FALSE)
					{
						break 2; // stop conditions checking
					}
				}
			}
		}

		return (bool) min($expressions);
	}

	/**
	 * Compares interval method.
	 *
	 * @param   string  Compare operator
	 * @param   string  Variable parameter (count/type)
	 * @param   string  Interval key
	 * @return  bool  TRUE if condition is accepted
	 */
	private function compare_interval($op, $variable, $key = NULL)
	{
		@list($variable, $interval) = explode('/', $variable, 2);

		switch ($interval)
		{
			case 'sec': $seconds = self::interval_second; break;
			case 'min': $seconds = self::interval_minute; break;
			case 'hr' : $seconds = self::interval_hour;   break;
			case 'day': $seconds = self::interval_day;    break;
		}

		if (!isset($seconds) OR empty($variable))
		{
			return FALSE;
		}

		$counter = $this->interval_check($seconds, $key);

		$this->intervals[$key] = $seconds;

		$variable = (float) $variable;

		switch ($op)
		{
			case '==': return $counter == $variable;
			case '!=': return $counter != $variable;
			case '>=': return $counter >= $variable;
			case '<=': return $counter <= $variable;
			case '>' : return $counter >  $variable;
			case '<' : return $counter <  $variable;
		}

		return FALSE;
	}

	/**
	 * Compares IP address method.
	 *
	 * @param   string|array  Variable parameter
	 * @return  bool  TRUE if condition is accepted
	 */
	private function compare_client_ip($variable)
	{
		return Inet::ip_in_subnets($this->client->get_address(), $variable);
	}

	/**
	 * Checks all intervals and returns current counter value.
	 *
	 * @param   int     Interval type
	 * @param   string  Interval key
	 * @return  int
	 */
	private function interval_check($interval = self::interval_second, $key = NULL, $update = FALSE)
	{
		$counter = FALSE;

		foreach ($this->interval_pool as $id => $element)
		{
			if ($element[1] <= time() - $element[0])
			{
				$this->interval_pool[$id] = NULL;

				unset($this->interval_pool[$id]);

				continue;
			}

			if ($element[0] == $interval AND $element[2] == $key)
			{
				if ($update)
				{
					$counter = ++$this->interval_pool[$id][3];
				}
				else
				{
					$counter = $element[3];
				}
			}
		}

		if ($counter === FALSE)
		{
			$counter = 1;

			if ($update)
			{
				$this->interval_pool[] = [$interval, time(), $key, ++$counter];
			}
		}

		return $counter;
	}

	/**
	 * Parse access control rules configuration.
	 *
	 * @return  void
	 */
	private function parse_rules()
	{
		$rules = [];

		foreach ($this->rules as $id => $rule)
		{
			if (!is_array($rule))
			{
				continue;
			}

			$rules[$id]['action']     = NULL;
			$rules[$id]['message']    = NULL;
			$rules[$id]['conditions'] = [];

			foreach ($rule as $rule_part)
			{
				// Assigning action
				if (is_null($rules[$id]['action']) AND ($rule_part == 'allow' OR $rule_part == 'deny' OR $rule_part == 'drop'))
				{
					$rules[$id]['action'] = $rule_part;
				}

				// Assigning message
				elseif (is_null($rules[$id]['message']) AND is_string($rule_part))
				{
					$rules[$id]['message'] = $rule_part;
				}

				// Assugning conditions
				elseif (is_array($rule_part) AND sizeof($rule_part) >= 2)
				{
					$rules[$id]['conditions'][] = $this->parse_condition($rule_part);
				}
			}

			if (is_null($rules[$id]['action']))
			{
				unset($rules[$id]);
			}
		}

		$this->rules = $rules;
	}

	/**
	 * Parse conditions in rule configuration.
	 *
	 * @param   array  Raw conditions array
	 * @return  array
	 */
	private function parse_condition($condition_part)
	{
		$condition = [];

		if (sizeof($condition_part) >= 3)
		{
			$condition['l']  = $condition_part[0];
			$condition['op'] = $condition_part[1];
			$condition['r']  = $condition_part[2];
		}
		else
		{
			$condition['l']  = $condition_part[0];
			$condition['r']  = $condition_part[1];
		}

		if (!isset($condition['op']) OR !in_array($condition['op'], ['==', '!=', '=<', '=>', '<', '>']))
		{
			$condition['op'] = '==';
		}

		return $condition;
	}

	/**
	 * Gets message sent to client.
	 *
	 * @return  string|NULL
	 */
	public function get_message()
	{
		return $this->message;
	}

	/**
	 * Gets last assigned action.
	 *
	 * @return  string
	 */
	public function get_action()
	{
		return strtolower($this->action);
	}

} // end of class Security

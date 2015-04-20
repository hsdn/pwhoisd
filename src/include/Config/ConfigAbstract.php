<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd\Config;

use pWhoisd\Config\ConfigInterface;
use InvalidArgumentException;
use RuntimeException;

/**
 * Abstract Config implementing ConfigInterface.
 */
abstract class ConfigAbstract implements ConfigInterface {

	/**
	 * @var array Configuration data.
	 */
	protected $data = [];

	/**
	 * {@inheritdoc}
	 */
	public function set($key, $value = NULL)
	{
		if (is_array($key)) 
		{
			foreach ($key as $k => $v)
			{
				$this->set($k, $v);
			}
		}
		elseif (is_string($key))
		{
			if (strpos($key, '.') !== FALSE)
			{
				$this->setDotNotationKey($key, $value);
			}
			else
			{
				if (is_array($value) AND $this->containsOnlyStringKeys($value)) 
				{
					foreach ($value as $k => $v)
					{
						$this->set($key.'.'.$k, $v);
					}
				} 
				else 
				{
					$this->data[$key] = $value;
				}
			}
		}
		else
		{
			throw new InvalidArgumentException('Key must be a string or an associative array');
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has($key = NULL)
	{
		if (is_null($key)) {
			return !empty($this->data);
		}

		$segs = explode('.', $key);
		$root = $this->data;

		// nested case
		foreach ($segs as $part)
		{
			if (!array_key_exists($part, $root))
			{
				return FALSE;
			}

			$root = $root[$part];
		}

		return TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($key = NULL, $default = NULL)
	{
		if (is_null($key))
		{
			return $this->data;
		}

		if (!$this->has($key))
		{
			if ($default === NULL)
			{
				throw new RuntimeException('Specified key not found in configuration');
			}

			return $default;
		}

		$segs = explode('.', $key);
		$root = $this->data;

		foreach ($segs as $part)
		{
			$root = $root[$part];
		}

		return $root;
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove($key)
	{
		if ($this->has($key))
		{
			$segs = explode('.', $key);
			$root = &$this->data;

			foreach ($segs as $part) 
			{
				$parent = &$root;
				$root   = &$root[$part];
			}

			unset($parent[$part]);
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		$this->data = [];
	}

	/**
	 * Handle setting a configuration value with a dot notation key.
	 *
	 * @param string $key   Dot notation key.
	 * @param mixed  $value Configuration value.
	 */
	protected function setDotNotationKey($key, $value)
	{
		$splitKey = explode('.', $key);
		$root	 = &$this->data;

		while ($part = array_shift($splitKey)) 
		{
			if (!isset($root[$part]) AND count($splitKey)) 
			{
				$root[$part] = [];
			}

			$root = &$root[$part];
		}

		$root = $value;
	}

	/**
	 * Check if an array contains only string keys.
	 *
	 * @param  array $array  Array to check.
	 * @return bool  TRUE if array only contains string keys, FALSE if not.
	 */
	protected function containsOnlyStringKeys(array $array)
	{
		return count($array) === count(array_filter(array_keys($array), 'is_string'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		$this->set($offset, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}

	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}

} // end of abstract class ConfigAbstract

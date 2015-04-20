<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd;

/**
 * Inet class.
 */
class Inet {

	/*
	 * @const  int  Number of bits in the IPv4 address
	 */
	const IPV4_BITS = 32;

	/*
	 * @const  int  Number of bits in the IPv6 address
	 */
	const IPV6_BITS = 128;


	/**
	 * Convert an IP address to a long string
	 *
	 * @param   string
	 * @return  string|bool
	 */
	public static function ip2long($ip)
	{
		if (!filter_var($ip, FILTER_VALIDATE_IP))
		{
			return FALSE;
		}

		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
		{
			$bin = self::ip2bin($ip);

			if (function_exists('gmp_init'))
			{
				return gmp_strval(gmp_init($bin, 2), 10);
			}
			elseif (function_exists('bcadd'))
			{
				$dec = '0';

				for ($i = 0; $i < strlen($bin); $i++)
				{
					$dec = bcadd(bcmul($dec, '2', 0), $bin[$i], 0);
				}

				return $dec;
			}

			trigger_error('GMP or BCMATH extension not installed!', E_USER_ERROR);

			return FALSE;
		}

		return @ip2long($ip);
	}

	/**
	 * Converts an IP address to binary
	 *
	 * @param   string
	 * @param   bool
	 * @return  string|bool
	 */
	public static function ip2bin($ip)
	{
		if (!filter_var($ip, FILTER_VALIDATE_IP))
		{
			return FALSE;
		}

		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
		{
			if(($ip_n = inet_pton($ip)) === FALSE) 
			{
				return FALSE;
			}

			$bin = '';

			for ($bit = 15; $bit >= 0; $bit--)
			{
				$bin = sprintf('%08b', ord($ip_n[$bit])).$bin;
			}

			return $bin;
		}

		return base_convert(ip2long($ip), 10, 2);
	}

	/**
	 * Returns an array containing the IP address in the subnet
	 *
	 * @param   string
	 * @param   array
	 * @param   bool     If you specify TRUE, returns the full list of entries
	 * @return  array|bool
	 */
	public static function ip_in_subnets($ip, $subnets, $return_all = FALSE)
	{
		if (!is_array($subnets))
		{
			$subnets = [$subnets];
		}

		$ip = self::ip2long($ip);
		$all = [];

		foreach ($subnets as $subnet)
		{
			$subnet = self::cidr2range($subnet);

			if (!$ip OR !$subnet)
			{
				continue;
			}

			list($low, $high) = $subnet;

			if ($ip <= self::ip2long($high) AND self::ip2long($low) <= $ip)
			{
				if (!$return_all)
				{
					return $subnet; 
					
					echo 1;
				}

				$all[] = $subnet;
			}
			elseif ($return_all)
			{
				$all[] = FALSE;
			}
		}

		if ($return_all)
		{
			return $all;
		}

		return FALSE;
	}

	/**
	 * Converts a CIDR block to range of addresses
	 *
	 * @param   string
	 * @return  array|bool
	 */
	public static function cidr2range($cidr)
	{
		$cidr = explode('/', $cidr, 2);
		$ip = $cidr[0];

		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
		{
			$prefix_len = isset($cidr[1]) ? $cidr[1] : self::IPV6_BITS;

			if ($prefix_len > self::IPV6_BITS) 
			{
				return FALSE;
			}

			$bits = self::IPV6_BITS - $prefix_len;

			$low_bin = inet_pton($ip);
			$low_hex = unpack('H*', $low_bin);
			$high_hex = reset($low_hex);
			$pos = 31;

			while ($bits > 0)
			{
				$val = hexdec(substr($high_hex, $pos, 1)) | (pow(2, min(4, $bits)) - 1);
				$high_hex = substr_replace($high_hex, dechex($val), $pos, 1);

				$bits -= 4;
				$pos--;
			}

			$low = inet_ntop($low_bin);
			$high = inet_ntop(pack('H*', $high_hex));

			return [$low, $high];
		}
		elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
		{
			$prefix_len = isset($cidr[1]) ? $cidr[1] : self::IPV4_BITS;

			if ($prefix_len > self::IPV4_BITS) 
			{
				return FALSE;
			}

			$bits = self::IPV4_BITS - $prefix_len;

			$low = long2ip(ip2long($ip) & (-1 << $bits));
			$high = long2ip(ip2long($ip) + pow(2, $bits) - 1);

			return [$low, $high];
		}

		return FALSE;
	}

} // end of class Inet

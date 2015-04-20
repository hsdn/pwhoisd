<?php

// -------------------- USER CONFIGURABLE --------------------

$whois_server_ip = "127.0.0.1";
$whois_server_port = 43;

# Set the variable below to 1 to print the replies to the screen otherwise
# zero.
$print_replies = FALSE;
$query_for = "ya.ru";

// ----------------END OF USER CONFIGURABLE ------------------

$cnt = 1;
$s_time = time();
$reply = '';

while ($cnt < 150) 
{
	if (!$fp = fsockopen($whois_server_ip, $whois_server_port, $errnum, $errstr, 10))
	{
		die('connect: '.$errstr);
	}

	fputs($fp, $query_for."\n");

	while (!feof($fp))
	{
		$reply .= fgets($fp, 1024);
	}

	if (strlen($reply))
	{
		if ($print_replies)
		{
			echo '['.$cnt."]\n".$reply;
		}
		else
		{
			echo '.';
		}

		$reply = '';
	}
	else
	{
		if((time() - $s_time) == 0) 
		{
			$rate = 0;
		}
		else
		{
			$rate = $cnt / (time() - $s_time);
		}

		echo "\n";
		echo 'Stopped at query number '.$cnt."\n";
		echo 'Average rate is '.$rate." queries per second\n";
		echo "Time: ".(time() - $s_time)."\n";

		exit;
	}

	fclose($fp);

	$cnt++;
}

$rate = $cnt / (time() - $s_time);

echo "\n";
echo 'Stopped at query number '.$cnt."\n";
echo 'Average rate is '.$rate." queries per second\n";

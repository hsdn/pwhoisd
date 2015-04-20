<?php
chdir(realpath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'));

$phar = new Phar('bin/pwhoisd.phar', 0, 'pwhoisd.phar' );
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->startBuffering();

$rd = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));

$files = [];

foreach($rd as $file)
{
	if ($file->getFilename() != '..' AND 
		$file->getFilename() != '.' AND 
		$file->getFilename() != __FILE__)
	{
		if (preg_match('|^\.\/src/include|', $file->getPath()))
		{
			$files[substr($file->getPath().DIRECTORY_SEPARATOR.$file->getFilename(), 2)] = 
				$file->getPath().DIRECTORY_SEPARATOR.$file->getFilename();
		}
	}
}

$phar->buildFromIterator(new ArrayIterator($files));

print_r($files);

$phar->setStub( <<<EOB
#!/usr/bin/env php
<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2015 Information Networks Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
Phar::mapPhar();
(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') and die('This program cannot be run in Windows environment.');
(PHP_SAPI === 'cli') or die('This program running available in CLI mode only.');
define('INCLUDE_PATH', 'phar://pwhoisd.phar/src/include');
require_once INCLUDE_PATH.DIRECTORY_SEPARATOR.'Autoloader.php';
\pWhoisd\Autoloader::register();
\pWhoisd\Application::factory()->run();
__HALT_COMPILER();
?>
EOB
);

$phar->stopBuffering();
$phar->compressFiles(Phar::GZ);

<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd\Storage;

use pWhoisd\Application;
use RuntimeException;

class FileProvider implements StorageInterface
{

    /*
     * @var  object  Instance of \pWhoisd\Client class
     */
    private $client;

    /*
     * @var  array   Storage configuration segment
     */
    private $storage;

    /*
     * @var  string  Request string
     */
    private $request;

    /*
     * @var  array   Array of SQL queries
     */
    private $queries;

    /*
     * @var  array   SQL queries result array
     */
    private $result_array = [];


    /**
     * Assigning class properties and connect to Database.
     *
     * @throws  \RuntimeException  If MySQL Connection error
     * @param   object  $client   Instance of \pWhoisd\Client class
     * @param   array   $storage  Storage configuration segment
     * @return  void
     */
    public function __construct(\pWhoisd\Client $client, $storage)
    {
        $this->client  = $client;
        $this->queries = $storage['queries'];
        $this->path = $storage['storage'];

        if (!file_exists($this->path))
        {
            throw new RuntimeException('Path to files does not exist');
        }

        Application::$log->debug('Path find');
    }

    /**
     * Closes Database connection.
     *
     * @return  void
     */
    public function __destruct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get($request)
    {
        if (!$this->path)
        {
            return FALSE;
        }

        $this->request = $request;

        foreach ($this->queries as $query)
        {
            if ($result = $this->query($query))
            {
                $this->result_array += $result;
            }
        }

        return $this->result_array;
    }

    /**
     * Query database
     *
     * @param   string  $table   Database table name
     * @return  void
     */
    private function query($query)
    {
        $query = $this->process_query_string($query);
        if ($query === false) {
            return [];
        }

        $md5 = md5(mb_strtolower($query));

        $local_path = $this->colculatePath($md5);
        $path = $this->path . DIRECTORY_SEPARATOR . $local_path;
        if (!file_exists($this->path . DIRECTORY_SEPARATOR . $local_path)) {
            return [];
        }

        return json_decode(file_get_contents($this->path . DIRECTORY_SEPARATOR . $local_path), true);
    }

    /**
     * Process SQL query string.
     *
     * @param   string  $string   SQL query string
     * @return  string|bool
     */
    private function process_query_string($string)
    {
        // System macro
        $macros = [
            '_request_'     => str_replace(array('%', '_'), '', $this->request),
            '_client_ip_'   => $this->client->get_address(),
            '_client_port_' => $this->client->get_port(),
        ];

        // Storage response macro
        if (is_array($this->result_array) AND !empty($this->result_array))
        {
            $macros += $this->result_array;
        }

        foreach ($macros as $macro => $value)
        {
            if (strpos($string, '{'.$macro.'}') !== FALSE)
            {
                $string = str_replace('{'.$macro.'}', $value, $string);
            }
        }

        return !preg_match('/\{\w+\}/', $string) ? $string : FALSE;
    }

    private function colculatePath($md5)
    {
        return substr($md5, 0, 1) . DIRECTORY_SEPARATOR . substr($md5, 1, 1) . DIRECTORY_SEPARATOR . $md5;
    }

}

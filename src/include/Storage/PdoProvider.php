<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @author      HiQDev
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd\Storage;

use pWhoisd\Application;
use RuntimeException;
use PDO;
use PDOException;
use PDOStatement;

class PdoProvider implements StorageInterface {

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
     * @param   object  $client   Instance of \pWhoisd\Client class
     * @param   array   $storage  Storage configuration segment
     * @return  void
     */
    public function __construct(\pWhoisd\Client $client, $storage)
    {
        $this->client  = $client;
        $this->queries = $storage['queries'];
        $this->storage = $storage;
        $this->connect();

        Application::$log->debug('Database connected');
    }

    /**
     * Closes Database connection.
     *
     * @return  void
     */
    public function __destruct()
    {
        if ($this->db)
        {
            $this->db = null;
            Application::$log->debug('Database connection closed');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get($request)
    {
        if (!$this->db)
        {
            return false;
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
     * Connect to DataBase
     *
     * @return self
     * @throws RuntimeException
     */
    private function connect()
    {
        try {
            $this->db = new PDO($this->storage['dsn'], $this->storage['db_user'], $this->storage['db_pass']);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection error');
        }

        return $this;
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

        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);
        try {
            $sth = $this->db->query($query);
            /***
            if ($sth->errorCode() !== '00000') {
                throw new RuntimeException("Error in sql statement");
            }
            ***/
        } finally {
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);
        }

        Application::$log->debug('Database '.__FUNCTION__.' query was successful: '. $query);

        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Process SQL query string.
     *
     * @param   string  $string   SQL query string
     * @return  array
     */
    private function process_query_string($string)
    {
        // System macro
        $macros = [
            '_request_'     => str_replace(array('%', '_'), '', $this->request),
            '_client_ip_'   => $this->client->get_address(),
            '_client_port_' => $this->client->get_port(),
        ];
        $params = [];

        // Storage response macro
        if (is_array($this->result_array) AND !empty($this->result_array)) {
            $macros += $this->result_array;
        }

        foreach ($macros as $macro => $value) {
            if (strpos($string, "{{$macro}}") !== false) {
                $value = mb_strtolower($value);
                $string = str_replace("{{$macro}}", $this->db->quote($value), $string);
                $params[":{$macro}"] = $value;
            }
        }

        return !preg_match('/\{\w+\}/', $string) ? $string : false;
    }

}

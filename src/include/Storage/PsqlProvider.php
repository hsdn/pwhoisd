<?php
/**
 * HSDN PHP Whois Server Daemon
 *
 * @author      HSDN Team
 * @author      HiQDev Team
 * @copyright   (c) 2015, Information Networks Ltd.
 * @link        http://www.hsdn.org
 */

namespace pWhoisd\Storage;

use pWhoisd\Application;
use RuntimeException;

class PsqlProvider implements StorageInterface {

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
     * @throws  \RuntimeException  If PgSQL Connection error
     * @param   object  $client   Instance of \pWhoisd\Client class
     * @param   array   $storage  Storage configuration segment
     * @return  void
     */
    public function __construct(\pWhoisd\Client $client, $storage)
    {
        $this->client  = $client;
        $this->queries = $storage['queries'];

        $this->db = @pg_connect(implode(" ", [
            "dbname={$storage['db_name']}",
            "user={$storage['db_user']}",
            "password={$storage['db_pass']}",
            "host={$storage['db_host']}",
            "port={$storage['db_port']}",
        ]));

        if (empty($this->db))
        {
            throw new RuntimeException('Database connection error: '.$this->db->connect_error);
        }

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
            pg_close($this->db);

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
        $start = time();
        $query = $this->process_query_string($query);

        $array = [];

        if ($query !== false) {
            $result = pg_query($this->db, $query);
            if (!$result) {
                throw new RuntimeException('Database '.__FUNCTION__.' qyery error: '. pg_last_error($this->db));
            }

            if (pg_numrows($result)) {

                $array = pg_fetch_assoc($result);
            }

            Application::$log->debug('Database '.__FUNCTION__.' query was successful: '.$query);
        }
        $end = time();
        Application::$log->debug('Execution time is ' . ($end - $start) . 's');
        return $array;
    }

    /**
     * Process SQL query string.
     *
     * @param   string  $string   SQL query string
     * @return  string|bool
     */
    private function process_query_string($string)
    {
        $start = time();
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
                $string = str_replace('{'.$macro.'}', pg_escape_string($this->db, mb_strtolower($value)), $string);
            }
        }

        return !preg_match('/\{\w+\}/', $string) ? $string : FALSE;
    }
}

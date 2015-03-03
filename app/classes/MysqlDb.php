<?php

class MysqlDb
{
    const MYSQLI_PCONNECTION_PREFIX = 'p:';

    private static $instance;
    protected $connection;

    private $sqlTxtOutput = '';
    private $sqlCountQueries = 0;
    private $queriesLog = array();
    private $numQueries = 0;
    private $totalQtime = 0;
    private $logQueries = false;
    private $logFile = '';
    private $host;
    private $port = 3601;
    private $user;
    private $password = false;
    private $db;
    private $pconnect = false;

    private function __clone() {}

    /**
     * @param array $params
     */
    private function __construct(array $params = array())
    {
        $this->setParams($params);

        $prefix = $this->pconnect ? self::MYSQLI_PCONNECTION_PREFIX : '';
        $this->connection = new mysqli($prefix . $this->host, $this->user, $this->password, $this->db);

        $this->connection->set_charset("utf8");
    }

    /**
     * @param array $params
     * @return MysqlDb
     */
    public static function getInstance(array $params = array())
    {
        if (empty(self::$instance)) {
            self::$instance = new MysqlDb($params);
        }

        return self::$instance;
    }

    /**
     * @return mysqli
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param $query
     * @return bool|mysqli_result
     */
    public function query($query)
    {
        $this->controlQuery($query);

        $start = microtime();
        $result = $this->connection->query($query) or $this->error($query, $this->connection->errno, $this->connection->error);

        $end = microtime();
        $time = (($end - $start) * 1000000);
        $this->totalQtime += $time;
        if ($this->logQueries) {
            $this->logQuery($query, $time);
        }

        return $result;
    }

    /**
     * @param $query
     * @param $errno
     * @param $error
     */
    public function error($query, $errno, $error)
    {
        if (APP_ENV == "acceptatie" && $this->logQueries) {
            die(sprintf(
                    '<font color="#000000"><b>%s - %s<br><br>%s<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b></font>',
                    $errno, $error, $query
                )
            );
        } else {
            $isFileExsists = is_file($this->logFile);
            if ($isFileExsists) {
                $fp = fopen($this->logFile, 'a');
                fwrite($fp, date("Y-m-d H:m:s") . " - " . print_r($_SERVER, true) . "\n");
                fwrite($fp, date("Y-m-d H:m:s") . " - " . $errno . ' - ' . $error . " - " . $query . "\n");
                fwrite($fp, date("Y-m-d H:m:s") . print_r(debug_backtrace(100), true) . "\n");
                fclose($fp);
            }
        }

        die('Er is een fout opgetreden, de verantwoordelijke is op de hoogte gesteld. Indien de fout morgen nog optreedt gelieve contact op te nemen met klantenservice@deonlinedrogist.nl');
    }

    /**
     * @param array $params
     */
    private function setParams(array $params)
    {
        foreach ($params as $key => $value) {
            switch ($key) {
                case 'host':
                    $this->host = $value;
                    break;
                case 'port':
                    $this->port = $value;
                    break;
                case 'user':
                    $this->user = $value;
                    break;
                case 'password':
                    $this->password = $value;
                    break;
                case 'db':
                    $this->db = $value;
                    break;
                case 'sql_txt_output':
                    $this->sqlTxtOutput = $value;
                    break;
                case 'log_queries':
                    $this->logQueries = $value;
                    break;
                case 'log_file':
                    $this->logFile = $value;
                    break;
                case 'pconnect':
                    $this->pconnect = $value;
                    break;
                default:
                    break;
            }
        }
        $this->checkParams();
    }

    /**
     * @param $query
     */
    private function controlQuery($query)
    {
        $this->numQueries++;

        if (count($this->queriesLog) > 100)
            array_shift($this->queriesLog);
        if ($this->logQueries) {
            $this->queriesLog[] = $query;

            if (
                $this->numQueries > 10000 &&
                $_SERVER['REQUEST_URI'] != "" &&
                $_SERVER['REQUEST_URI'] != "/ext/modules/order_total/sa/index.php" &&
                $_SERVER['REQUEST_URI'] != "/producten_zonder_plaatje.php" &&
                $_SERVER['REQUEST_URI'] != "/producten_zonder_plaatje.php?export=csv"
            ) {
                mail(
                    "mail@joachim.pro",
                    "PAGE " . $_SERVER['REQUEST_URI'] . " STOPPED, TOO MANY QUERIES",
                    implode("\n", $this->queriesLog) . "\n\n" . print_r($_SERVER, true)
                );
                die();
            }
        }
    }

    /**
     * @param $query
     * @param $time
     */
    private function logQuery($query, $time)
    {
        $valuesStr = sprintf(
            '"%s", "%s", "%s", "%s"',
            $this->connection->real_escape_string($query),
            $_SERVER['REQUEST_URI'],
            $time,
            $this->connection->real_escape_string(print_r(debug_backtrace(100), true))
        );

        $sql = sprintf('INSERT INTO sql_log VALUES (NULL, %s, 0)', $valuesStr);
        $this->sqlTxtOutput .= $valuesStr . PHP_EOL;
        $this->sqlCountQueries++;

        $this->connection->query($sql);
        print_r(array('sql' => $query, 'error' => $this->connection->error));
    }

    /**
     * @return bool|string
     */
    private function trySetHost()
    {
        $this->host = defined('DB_SERVER') ? DB_SERVER : false;
        return $this->host;
    }

    /**
     * @return bool|string
     */
    private function trySetUser()
    {
        $this->user = defined('DB_SERVER_USERNAME') ? DB_SERVER_USERNAME: false;
        return $this->user;
    }

    /**
     * @return bool
     */
    private function trySetPassword()
    {
        $this->password = defined('DB_SERVER_PASSWORD') ? DB_SERVER_PASSWORD : false;
        return $this->password !== false;
    }

    /**
     * @return bool|string
     */
    private function trySetDb()
    {
        $this->db = defined('DB_DATABASE') ? DB_DATABASE : false;
        return $this->db;
    }

    private function checkParams()
    {
        $errors = array();

        if (!$this->host && !$this->trySetHost()) {
            $errors[] = 'Not defined mysql server';
        }
        if (!$this->user && !$this->trySetUser()) {
            $errors[] = 'Not defined mysql user';
        }
        if ($this->password === false && !$this->trySetPassword()) {
            $errors[] = 'Not defined mysql password';
        }
        if (!$this->db && !$this->trySetDb()) {
            $errors[] = 'Not defined mysql db';
        }
        if (!empty($errors)) {
            throw new \Exception(implode(PHP_EOL, $errors));
        }
    }
}
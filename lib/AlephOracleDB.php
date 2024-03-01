<?php

class AlephOracleDB
{
    public $conn;
    public $hostname;
    public $username;
    public $password;
    public $serviceName;
    public $port;

    public function __construct($hostname, $username, $password, $serviceName, $port)
    {
        $this->conn = null;
        $this->hostname = $hostname;
        $this->port = $port;
        $this->serviceName = $serviceName;
        $this->username = $username;
        $this->password = $password;
    }

    public function connect()
    {
        $username = $this->username;
        $password = $this->password;
        $hostname = $this->hostname;
        $serviceName = $this->serviceName;
        $port = $this->port;

        $this->conn = oci_connect($username, $password, "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$hostname)(PORT=$port))(CONNECT_DATA=(SERVICE_NAME=$serviceName)))", 'AL32UTF8');

        if (!$this->conn) {
            $e = oci_error();
            return (new Exception(htmlentities($e['message'], ENT_QUOTES)));
        }

        return true;
    }

    public function executeQuery($query, $bindVars = [])
    {
        $stid = oci_parse($this->conn, $query);

        foreach ($bindVars as $var => $val) {
            oci_bind_by_name($stid, $var, $val);
        }

        if (!oci_execute($stid)) {
            $e = oci_error($stid);
            return (new Exception(htmlentities($e['message'], ENT_QUOTES)));
        }

        $nrows = oci_fetch_all($stid, $res);
        return $res;
    }

    /**
     * Generate Z308_REC_KEY from Z303_NAME and Z303_BIRTH_DATE
     * @param string $z303name
     * @param string $z303birthDate
     * @return string
     */
    public static function generateZ308RecKey($z303name, $z303birthDate)
    {
        $name = str_replace(' ', '', iconv('UTF-8', 'ASCII//TRANSLIT', $z303name));

        return strtoupper('08' . $name . $z303birthDate);
    }

    /**
     * Generate Z308_REC_KEY from Z303_NAME for INSERT
     * @param string $z303name
     * @param string $z303birthDate
     * @return string
     */
    public static function generateZ308RecKeyInsert($z303name, $z303birthDate)
    {
        $name = str_replace(' ', '', iconv('UTF-8', 'ASCII//TRANSLIT', $z303name));
        $name = str_replace("'", "''", $name);

        return strtoupper($name . $z303birthDate);
    }

    public function close()
    {
        oci_close($this->conn);
    }
}
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

        $this->conn = oci_connect($username, $password, "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$hostname)(PORT=$port))(CONNECT_DATA=(SERVICE_NAME=$serviceName)))");

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

    public function isPatron($fname, $lname, $birthDate)
    {
        $z308RecKeyNames = $this->prepareZ308RecKeyNames($fname, $lname);
        $Z308_REC_KEY = '08' . strtoupper($z308RecKeyNames) . date('Ymd', strtotime($birthDate));

        $query = "SELECT COUNT(A.Z308_REC_KEY) AS count FROM KNA50.Z308 A WHERE A.Z308_REC_KEY LIKE :key";
        $bindVars = [':key' => $Z308_REC_KEY . '%'];
        $result = $this->executeQuery($query, $bindVars);

        return (!empty($result) && $result['COUNT'][0] == 1);
    }

    public static function prepareZ308RecKeyNames($fname, $lname)
    {
        $fname = str_replace(' ', '', iconv('UTF-8', 'ASCII//TRANSLIT', $fname));
        $lname = str_replace(' ', '', iconv('UTF-8', 'ASCII//TRANSLIT', $lname));

        return $lname . $fname;
    }

    public function close()
    {
        oci_close($this->conn);
    }
}
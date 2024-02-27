<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/AlephOracleDB.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Oracle DB
$oracleDbUser = $_ENV['ORACLE_DB_USER'];
$oracleDbPassword = $_ENV['ORACLE_DB_PASSWORD'];
$oracleDbHost = $_ENV['ORACLE_DB_HOST'];
$oracleDbPort = $_ENV['ORACLE_DB_PORT'];
$oracleDbService = $_ENV['ORACLE_DB_SERVICE'];

try {
    $oracleDb = new AlephOracleDB($oracleDbHost, $oracleDbUser, $oracleDbPassword, $oracleDbService, $oracleDbPort);
    $oracleDb->connect();
    $query = "SELECT COUNT(*) AS count FROM KNA50V.Z303 WHERE Z303_REC_KEY LIKE :key";
    $bindVars = [':key' => 'KNAV%'];
    $res = $oracleDb->executeQuery($query, $bindVars);
    var_dump($res);
    $oracleDb->close();
} catch (Exception $exception) {
    echo "Error: " . $exception->getMessage();
}

// Mysql DB
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];
$dbHost = $_ENV['DB_HOST'];
$dbPort = $_ENV['DB_PORT'];
$dbName = $_ENV['DB_NAME'];

try {
    $connection = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPassword);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $query = "SELECT COUNT(*) AS count FROM Z303 WHERE Z303_REC_KEY LIKE 'KNAV%'";
    $result = $connection->query($query);
    var_dump($result->fetch());
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
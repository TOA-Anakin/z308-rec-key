<?php

// error_reporting(E_ALL);
// ini_set('display_errors', '1');
// setlocale(LC_ALL, 'C.UTF-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../lib/AlephOracleDB.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$oracleDbUser = $_ENV['ORACLE_DB_USER'];
$oracleDbPassword = $_ENV['ORACLE_DB_PASSWORD'];
$oracleDbHost = $_ENV['ORACLE_DB_HOST'];
$oracleDbPort = $_ENV['ORACLE_DB_PORT'];
$oracleDbService = $_ENV['ORACLE_DB_SERVICE'];

if (isset($_GET['download'])) {

    try {
        $oracleDb = new AlephOracleDB($oracleDbHost, $oracleDbUser, $oracleDbPassword, $oracleDbService, $oracleDbPort);
        $oracleDb->connect();

        $query = "SELECT A.Z303_REC_KEY, A.Z303_NAME, A.Z303_BIRTH_DATE FROM KNA50.z303 A WHERE A.Z303_REC_KEY LIKE :key";
        $bindVars = [':key' => 'KNAV%'];
        $result = $oracleDb->executeQuery($query, $bindVars);
        $oracleDb->close();
    } catch (Exception $exception) {
        echo "Error: " . $exception->getMessage();
        die();
    }

    if ($_GET['download'] == 'csv') {

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="output.csv"');

        $file = fopen('php://output', 'w');

        $header = ['Z303_REC_KEY', 'Z303_NAME', 'Z303_BIRTH_DATE', 'Z308_REC_KEY'];
        fputcsv($file, $header);

        foreach ($result['Z303_REC_KEY'] as $index => $z303recKey) {
            $z303name = $result['Z303_NAME'][$index];
            $z303birthDay = $result['Z303_BIRTH_DATE'][$index];
            $modifiedRow = [
                'Z303_REC_KEY' => $z303recKey,
                'Z303_NAME'=> $z303name,
                'Z303_BIRTH_DATE'=> $z303birthDay,
                'Z308_REC_KEY'=> AlephOracleDB::generateZ308RecKey($z303name, $z303birthDay)
            ];
            fputcsv($file, $modifiedRow);
        }

        fclose($file);

    } elseif ($_GET['download'] == 'sql') {

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="insert_statements.sql"');

        $insertStatements = "";

        $countRes = count($result['Z303_REC_KEY']);

        foreach ($result['Z303_REC_KEY'] as $index => $z303recKey) {
            $z303name = $result['Z303_NAME'][$index];
            $z303birthDay = $result['Z303_BIRTH_DATE'][$index];

            $z308recKey = "'08' || rpad('" . AlephOracleDB::generateZ308RecKeyInsert($z303name, $z303birthDay) . "', 255) || 'KNA50'";

            $insertStatements .= "INSERT INTO KNA50.mar308 VALUES (" . 
                $z308recKey . ", " . 
                "rpad(' ', 40), " . 
                "'00', " . 
                "'" . $z303recKey . "', " . 
                "'AC', " . 
                "'N', " . 
                "'202401011200000'" . 
            ");\n";
        }

        echo $insertStatements;
        exit;

    }

} else {

    try {
        $oracleDb = new AlephOracleDB($oracleDbHost, $oracleDbUser, $oracleDbPassword, $oracleDbService, $oracleDbPort);
        $oracleDb->connect();

        $query = "SELECT COUNT(*) AS count FROM KNA50.Z303 WHERE Z303_REC_KEY LIKE :key";
        $bindVars = [':key' => 'KNAV%'];
        $result = $oracleDb->executeQuery($query, $bindVars);
        
        echo "<p>Number of rows in KNA50.Z303 where Z303_REC_KEY equals 'KNAV%':</p>";
        echo "<p>" . $result['COUNT'][0] . "</p><br><br>";

        $oracleDb->close();
    } catch (Exception $exception) {
        echo "Error: " . $exception->getMessage();
    }

    echo '<a href="?download=csv">Download CSV with Z308_REC_KEY names</a><br><br>';
    echo '<a href="?download=sql">Download SQL for import into Z308</a>';

}
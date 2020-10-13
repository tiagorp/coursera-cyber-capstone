<?php

namespace System\Database;

use \PDO;
use \DateTime;

class Database
{

    function __construct()
    {

        if (!isset($GLOBALS['database_connection'])) {

            $db_details = $this->getDatabase();

            $serverName = $db_details['host'];
            $username = $db_details['user'];
            $password = $db_details['password'];
            $databaseName = $db_details['database'];

            $pdo = new PDO("mysql:host=$serverName;dbname=$databaseName", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $GLOBALS['database_connection'] = $pdo;
        }
    }

    function getDatabase()
    {

        return array(
            'host' => 'localhost',
            'user' => 'capstone',
            'password' => '4Eex0Y8S6Wusl!',
            'database' => 'capstone'
        );

        /*
        return array(
            'host' => getenv('DATABASE_HOST'),
            'user' => getenv('DATABASE_USER'),
            'password' => getenv('DATABASE_PASSWORD'),
            'database' => getenv('DATABASE_NAME')
        );*/
    }

    function getConnection()
    {
        return $GLOBALS['database_connection'];
    }

    function getFetchNum()
    {
        return PDO::FETCH_NUM;
    }

    function convertToDateTimeString(DateTime $datetime): string
    {
        return $datetime->format('Y-m-d H:i:s');
    }

    function convertFromDateTimeString($datetime_string): DateTime
    {

        return date_create_from_format('Y-m-d H:i:s', $datetime_string);
    }
}

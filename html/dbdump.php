<?php

require './system/Database.php';

use \System\Database\Database as Database;

function array_to_csv_download($array, $filename = "export.csv", $delimiter = ",")
{
    // open raw memory as file so no temp files needed, you might run out of memory though
    $f = fopen('php://memory', 'w');
    // loop over the input array
    foreach ($array as $line) {
        // generate csv lines from the inner arrays
        fputcsv($f, $line, $delimiter);
    }
    // reset the file pointer to the start of the file
    fseek($f, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: application/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}

if (isset($_GET['command'])) {

    if ($_GET['command'] == 'users') {

        // download users table
        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        SELECT
            username,
            password
        FROM user
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute();

        $returnArray = [];
        $returnArray[0] = ['username' => 'username', 'password' => 'password'];
        $i = 1;

        while ($row = $statement->fetch($db->getFetchNum())) {
            $returnArray[$i] = [
                'username' => $row[0],
                'password' => $row[1]
            ];
            $i++;
        }

        array_to_csv_download($returnArray, "users.csv");
        die();
    }

    if ($_GET['command'] == 'user_session') {

        // download users table
        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        SELECT
            token,
            username,
            created_at,
            valid_until
        FROM user_session
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute();

        $returnArray = [];
        $returnArray[0] = [
            'token' => 'token',
            'username' => 'username',
            'created_at' => 'created_at',
            'valid_until' => 'valid_until'
        ];

        $i = 1;

        while ($row = $statement->fetch($db->getFetchNum())) {
            $returnArray[$i] = [
                'token' => $row[0],
                'username' => $row[1],
                'created_at' => $row[2],
                'valid_until' => $row[3]
            ];
            $i++;
        }

        array_to_csv_download($returnArray, "user_session.csv");
        die();
    }

    if ($_GET['command'] == 'messages') {

        // download users table
        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        SELECT
            user_from,
            user_to,
            sent_at,
            content
        FROM message
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute();

        $returnArray = [];
        $returnArray[0] = [
            'user_from' => 'user_from',
            'user_to' => 'user_to',
            'sent_at' => 'sent_at',
            'content' => 'content'
        ];

        $i = 1;

        while ($row = $statement->fetch($db->getFetchNum())) {
            $returnArray[$i] = [
                'user_from' => $row[0],
                'user_to' => $row[1],
                'sent_at' => $row[2],
                'content' => $row[3]
            ];
            $i++;
        }

        array_to_csv_download($returnArray, "messages.csv");
        die();
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Capstone project intro page." />
    <meta name="robots" content="index,follow" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;1,400;1,500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="./styles/style.css" />
    <title>Coursera Capstone Project</title>
</head>

<body>

    <div class="dbdump">
        <p>Download database tables (csv format):</p>
        <a href="dbdump.php?command=users">Users</a>
        <a href="dbdump.php?command=user_session">User Session</a>
        <a href="dbdump.php?command=messages">Messages</a>
        <a class="dbdump-back-link" href="index.php">Back to login page</a>
    </div>
</body>

</html>
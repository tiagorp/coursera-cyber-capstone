<?php

namespace System\User;

use \System\Database\Database as Database;

class User
{

    function getUsers()
    {

        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        SELECT
            username
        FROM user
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute();

        $returnArray = [];
        $i = 0;

        while ($row = $statement->fetch($db->getFetchNum())) {
            $returnArray[$i] = [
                'username' => $row[0]
            ];
            $i++;
        }

        return $returnArray;
    }

    function getUserByUsername($username)
    {

        $returnArray = [];

        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        SELECT
            username
        FROM user
        WHERE username = :username
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute([':username' => $username]);

        $row = $statement->fetch($db->getFetchNum());

        if (!$row) {
            return $returnArray;
        }

        $returnArray = [
            'username' => $row[0]
        ];

        return $returnArray;
    }

    function isValidCredentials($username, $password)
    {

        if ($username == '' || $password == '') {
            return ['user_found' => 'false'];
        }

        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        SELECT password
        FROM user
        WHERE username = :username
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute([':username' => $username]);

        $row = $statement->fetch($db->getFetchNum());

        if (!$row) {
            return ['user_found' => 'false'];
        }

        // If we are here, then user is found.

        $passwordMatch = password_verify($password, $row[0]);

        if (!$passwordMatch) {
            return ['user_found' => 'true', 'correct_password' => 'false'];
        }

        // If we are here, then user is found and password is correct.

        return ['user_found' => 'true', 'correct_password' => 'true'];
    }

    function createNewUser($username, $password)
    {

        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        INSERT INTO user (username, password)
        VALUES (?, ?)
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
    }

    function changeUserPassword($username, $newPassword)
    {

        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        UPDATE user SET password = :newpassword WHERE username = :username
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute(['newpassword' => password_hash($newPassword, PASSWORD_DEFAULT), 'username' => $username]);
    }
}

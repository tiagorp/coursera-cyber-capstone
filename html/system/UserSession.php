<?php

namespace System\UserSession;

use \System\Database\Database as Database;

use \DateTimeZone;

class UserSession
{

    function createNewSession($username): string
    {

        $db = new Database();
        $connection = $db->getConnection();

        // Delete any possible existing session.

        $sql = <<<EOD
        DELETE
        FROM user_session
        WHERE username = :username
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute([':username' => $username]);

        // Now, create a new user session.

        $token = $this->generateToken();

        $sql = <<<EOD
        INSERT INTO user_session (token, username, created_at, valid_until)
        Values (?, ?, ?, ?)
        EOD;

        $currDateTime = date_create('now', new DateTimeZone(date_default_timezone_get()));
        $validUntil = date_create('now', new DateTimeZone(date_default_timezone_get()));
        date_add($validUntil, date_interval_create_from_date_string('60 minutes'));

        $statement = $connection->prepare($sql);

        $statement->execute([$token, $username, $db->convertToDateTimeString($currDateTime), $db->convertToDateTimeString($validUntil)]);

        // Create cookie with 60 minutes.
        $this->setClientCookie($token);

        return $token;
    }


    public function refreshSession($token): array
    {

        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        SELECT username
        FROM user_session
        WHERE token = :token
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute([':token' => $token]);

        $row = $statement->fetch($db->getFetchNum());

        if (!$row) {
            return [];
        }

        $sql = <<<EOD
        UPDATE user_session SET valid_until = :newValid, created_at = :newCreatedAt
        WHERE token = :token
        EOD;

        $currDateTime = date_create('now', new DateTimeZone(date_default_timezone_get()));
        $validUntil = date_create('now', new DateTimeZone(date_default_timezone_get()));
        date_add($validUntil, date_interval_create_from_date_string('60 minutes'));

        $statement = $connection->prepare($sql);
        $statement->execute([
            ':newValid' => $db->convertToDateTimeString($validUntil),
            ':newCreatedAt' => $db->convertToDateTimeString($currDateTime),
            ':token' => $token
        ]);

        $this->setClientCookie($token);

        return ['username' => $row[0]];
    }

    public function terminateSession($token)
    {

        if ($token == '')
            return;

        $db = new Database();
        $connection = $db->getConnection();

        // Delete any possible existing session.

        $sql = <<<EOD
        DELETE
        FROM user_session
        WHERE token = :token
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute(['token' => $token]);

        $this->removeClientCookie();
    }

    public function getUserBySessionToken($token)
    {

        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
        SELECT
            user.username
        FROM User
        INNER JOIN user_session
            ON user_session.username = user.username
        WHERE user_session.token = :token
        EOD;

        $statement = $connection->prepare($sql);
        $statement->execute(['token' => $token]);

        $row = $statement->fetch($db->getFetchNum());

        if (!$row) {
            return [];
        }

        $returnArray = [
            'username' => $row[0]
        ];

        return $returnArray;
    }

    private function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    private function setClientCookie($token)
    {
        // Create cookie with 60 minutes.
        $cookieName = 'capstone_fgi_ssid';
        setcookie($cookieName, $token, mktime() . time() + 60 * 60, '/');
    }

    private function removeClientCookie()
    {
        if (isset($_COOKIE['capstone_fgi_ssid'])) {
            unset($_COOKIE['capstone_fgi_ssid']);
            setcookie('capstone_fgi_ssid', null, -1, '/');
        }
    }

    public function getClientToken()
    {
        if (isset($_COOKIE['capstone_fgi_ssid'])) {
            return $_COOKIE['capstone_fgi_ssid'];
        } else {
            return '';
        }
    }

    public function isClientTokenValid()
    {

        $token = $this->getClientToken();

        if ($token === '')
            return false;

        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
            SELECT username, valid_until
            FROM user_session
            WHERE token = :token
            EOD;

        $statement = $connection->prepare($sql);
        $statement->execute([':token' => $token]);

        $row = $statement->fetch($db->getFetchNum());

        if (!$row)
            return false;

        $valid_until = $db->convertFromDateTimeString($row[1]);

        $currDateTime = date_create('now', new DateTimeZone(date_default_timezone_get()));

        if ($currDateTime > $valid_until) {
            $this->terminateSession($token);
            return false;
        }

        return true;
    }

    public function getUsername()
    {
        $token = $this->getClientToken();

        if ($token === '')
            return '';

        $db = new Database();
        $connection = $db->getConnection();

        $sql = <<<EOD
            SELECT username
            FROM user_session
            WHERE token = :token
            EOD;

        $statement = $connection->prepare($sql);
        $statement->execute([':token' => $token]);

        $row = $statement->fetch($db->getFetchNum());

        if (!$row)
            return '';

        return $row[0];
    }
}

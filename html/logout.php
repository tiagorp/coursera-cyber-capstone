<?php


require './system/User.php';
require './system/Database.php';
require './system/UserSession.php';

use \System\UserSession\UserSession as UserSession;
    
$user_session = new UserSession();
$user_session->terminateSession($user_session->getClientToken());
header("Location: index.php");
die();

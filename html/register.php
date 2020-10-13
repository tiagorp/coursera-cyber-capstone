<?php

include 'utils.php';
require './system/User.php';
require './system/Database.php';
require './system/UserSession.php';

use \System\User\User as User;
use \System\UserSession\UserSession as UserSession;

// Just in case; terminate any existing session.
$user_session = new UserSession();
$user_session->terminateSession($user_session->getClientToken());

$username = '';
$password = '';
$is_valid = true;
$script = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $username = sanitize_input($_POST["username"]);
  $password = sanitize_input($_POST["password"]);

  $script = $script .
    <<<EOD
      el = document.querySelector(".form-username input");
      el.value = "$username";
    EOD;


  //if (strlen($username) < 3 || strlen($username) > 30) {
  if (preg_match("~^[a-zA-Z0-9]{3,30}$~", $username) == 0) {
    $is_valid = false;

    $script = $script .
      <<<EOD
        el = document.querySelector(".username-error");
        el.innerHTML = "Username has to be at least 3 characters (letters and digits) long and maximum 30."
        el.classList.remove("hidden");
      EOD;
  }

  if (strlen($password) < 15 || strlen($password) > 50) {
    $is_valid = false;

    $script = $script .
      <<<EOD
        el = document.querySelector(".password-error");
        el.innerHTML = "Password must be at least 15 characters (max. 50) long. E.g. five different words."
        el.classList.remove("hidden");
      EOD;
  }

  if ($is_valid) {

    $user_handler = new User();

    $existing_user = $user_handler->getUserByUsername($username);

    if (!empty($existing_user)) {

      $script = $script .
        <<<EOD
          el = document.querySelector(".username-error");
          el.innerHTML = "There is already an user with that username."
          el.classList.remove("hidden");
        EOD;
    } else {

      $user_handler->createNewUser($username, $password);
      header("Location: register_sucess.php");
      die();
    }
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

  <main class="register-page">

    <div class="main-content">

      <div class="form-header">
        <h2>New user</h2>
      </div>

      <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-username">
          <input placeholder="username" id="username" type="text" name="username" maxlength=30 />
          <p class="username-error hidden"></p>
        </div>
        <div class="form-password">
          <input placeholder="password" id="password" type="password" name="password" maxlength=50 />
          <p class="password-error hidden">
          </p>
        </div>
        <div class="button-container">
          <button type="submit">Register</button>
          <a class="back-link" href="index.php">Back to login page</a>
        </div>
      </form>

    </div>

  </main>

</body>


<?php

echo "<script>";
echo $script;
echo "</script>";

?>

</html>
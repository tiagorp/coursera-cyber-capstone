<?php

include 'utils.php';
include 'encrypt.php';
require './system/User.php';
require './system/Database.php';
require './system/UserSession.php';

use \System\User\User as User;
use \System\UserSession\UserSession as UserSession;
use \System\Database\Database as Database;

$script = '';

// Check for valid user session;

$user_session = new UserSession();
$is_valid_token = $user_session->isClientTokenValid();

if (!$is_valid_token) {
    header("Location: index.php");
    die();
} else {
    $user_session->refreshSession($user_session->getClientToken());
}

$myusername = $user_session->getUsername();

$script = $script .
    <<<EOD
  el = document.querySelector(".welcome-username");
  el.innerHTML = "$myusername";
  EOD;

$is_valid = true;

// this post is when user sends a message.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = sanitize_input($_POST["username"]);
    $content = sanitize_input($_POST["content"]);

    // save username the user typed
    $script = $script .
        <<<EOD
        el = document.querySelector(".form-username input");
        el.value = "$username";
        EOD;

    // save msg content the user typed
    $script = $script .
        <<<EOD
        el = document.querySelector(".form-message textarea");
        el.innerHTML = "$content";
        EOD;

    if ($username == '' || strlen($username) > 30) {
        $is_valid = false;

        $script = $script .
            <<<EOD
              el = document.querySelector(".username-error");
              el.innerHTML = "Username cannot be empty or greater than 30 chars."
              el.classList.remove("hidden");
            EOD;
    } else if ($username === $myusername) {
        $is_valid = false;

        $script = $script .
            <<<EOD
              el = document.querySelector(".username-error");
              el.innerHTML = "Please do not send messages to yourself."
              el.classList.remove("hidden");
            EOD;
    }

    if ($content == '' || strlen($content) > 200) {
        $is_valid = false;

        $script = $script .
            <<<EOD
              el = document.querySelector(".content-error");
              el.innerHTML = "Write something nice. Also, keep it short (200 char max)."
              el.classList.remove("hidden");
            EOD;
    }


    // Message is valid.
    if ($is_valid) {

        $user_handler = new User();
        $to_user = $user_handler->getUserByUsername($username);

        if (empty($to_user)) {

            $script = $script .
                <<<EOD
              el = document.querySelector(".username-error");
              el.innerHTML = "User not found."
              el.classList.remove("hidden");
            EOD;
        } else {

            // set the username equal to the one from the db. this is in order to avoid differences
            // case size (small caps vs big caps)
            $username = $to_user['username'];

            // Save message
            $db = new Database();
            $connection = $db->getConnection();

            $sql = <<<EOD
            INSERT INTO message (user_from, user_to, sent_at, content)
            VALUES (?, ?, ?, ?)
            EOD;

            $content_encrypted = safeEncrypt($content);
            $currDateTime = date_create('now', new DateTimeZone(date_default_timezone_get()));
            $statement = $connection->prepare($sql);
            $statement->execute([$myusername, $username, $db->convertToDateTimeString($currDateTime), $content_encrypted]);
            // End of save message


            // clear field (due to the script before to remember field)
            $script = $script .
                <<<EOD
            el = document.querySelector(".form-username input");
            el.value = "";
            EOD;

            // clear field (due to the script before to remember field)
            $script = $script .
                <<<EOD
                el = document.querySelector(".form-message textarea");
                el.innerHTML = "";
                EOD;
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

    <header class="inbox-head">

        <div class="welcome">
            <h4>Welcome, <span class="welcome-username"></span>.</h4>
        </div>

        <div class="logout-link"><a href="logout.php">
                <h4>Logout</h4>
            </a></div>
    </header>

    <main class="inbox-page">

        <section class="send-message">

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

                <div class="form-username">
                    <input placeholder="type the username..." id="username" type="text" name="username" maxlength=30 />
                    <p class="username-error hidden"></p>
                </div>
                <div class="form-message">
                    <textarea placeholder="type your message here... max 200 char" id="content" type="text" name="content" rows=5 maxlength=200></textarea>
                    <p class="content-error hidden">
                    </p>
                </div>
                <button type="Send Message">Send message</button>

            </form>

        </section>

        <section class="message-list">
            <h3>Message list</h3>
            <a class="refresh-link" href="inbox.php">Refresh page</a>


            <?php
            // Compute message list.

            $db = new Database();
            $connection = $db->getConnection();

            $sql = <<<EOD
            SELECT
                user_from, user_to, sent_at, content
            FROM message
            WHERE
                user_from = :userfrom OR user_to = :userto
            ORDER BY
                sent_at DESC

            EOD;

            $statement = $connection->prepare($sql);
            $statement->execute([':userfrom' => $myusername, ':userto' => $myusername]);
            $statement->execute();

            while ($row = $statement->fetch($db->getFetchNum())) {

                $message_decrypted = safeDecrypt($row[3]);
                $sent_at = $row[2];
                $message_to = $row[1];
                $message_from = $row[0];

                if ($message_from === $myusername) { // its message I've sent

                    echo <<<EOD
                    
                    <div class="message-outter-wrapper">
                        <div class="message-wrapper messsage-sent">
                            <p class="message-title">Sent to $message_to on $sent_at<p>
                            <p class="message-text">$message_decrypted<p>
                        </div>
                    </div>

                    EOD;
                } else { // its message I've received

                    echo <<<EOD

                    <div class="message-outter-wrapper">
                        <div class="message-wrapper messsage-received">
                            <p class="message-title">Received from $message_from on $sent_at<p>
                            <p class="message-text">$message_decrypted<p>
                        </div>
                    </div>

                    EOD;
                }


                $i++;
            }



            ?>




        </section>

    </main>


    <?php

    echo "<script>";
    echo $script;
    echo "</script>";

    ?>

</body>

</html>
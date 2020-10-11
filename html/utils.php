<?php

    function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
    }

    function is_password_valid($password) {
        return true;
    }

?>
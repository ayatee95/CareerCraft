<?php

require_once __DIR__ . "/functions.php";

secure_session_start();


if(!isset($_SESSION['user_id'])){

    redirect_to("login.php");

}

?>
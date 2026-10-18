<?php

require_once __DIR__ . "/includes/functions.php";

secure_session_start();

// Logging out changes the session, so it needs a POST request with a valid CSRF token.
// (A link or image on another website cannot log the user out.)
if($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()){

    redirect_to(isset($_SESSION['user_id']) ? "dashboard.php" : "login.php");

}

end_session();

redirect_to("login.php");

?>
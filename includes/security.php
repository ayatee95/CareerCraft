<?php

/*
 * CareerCraft - security helpers: output escaping, sessions, CSRF, basic security headers.
 * Loaded through includes/functions.php.
 */

/* ------------------------------------------------------------
   Output escaping (prevents XSS)
------------------------------------------------------------ */
function e($value){

    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

}

/* ------------------------------------------------------------
   Sessions
------------------------------------------------------------ */
function secure_session_start(){

    if(session_status() !== PHP_SESSION_NONE){
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_name('CCSESSID');

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    if(PHP_VERSION_ID >= 70300){

        session_set_cookie_params(array(
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => $https,
            'httponly' => true,
            'samesite' => 'Lax'
        ));

    }else{

        session_set_cookie_params(0, '/; samesite=Lax', '', $https, true);

    }

    session_start();

}

// Ends the login session completely (data + cookie).
function end_session(){

    secure_session_start();

    $_SESSION = array();

    if(ini_get("session.use_cookies")){

        $params = session_get_cookie_params();

        setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);

    }

    session_destroy();

}


/* ------------------------------------------------------------
   CSRF protection
------------------------------------------------------------ */
function random_hex($bytes){

    if(function_exists('random_bytes')){
        return bin2hex(random_bytes($bytes));
    }

    return bin2hex(openssl_random_pseudo_bytes($bytes));

}

function csrf_token(){

    secure_session_start();

    if(empty($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = random_hex(32);
    }

    return $_SESSION['csrf_token'];

}

function csrf_field(){

    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';

}

function csrf_check(){

    if(empty($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || !is_string($_POST['csrf_token'])){
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);

}

function csrf_error_message(){

    return "Your session expired or the request was invalid. Please reload the page and try again.";

}

/* ------------------------------------------------------------
   Basic security headers
------------------------------------------------------------ */

if(!headers_sent()){

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Content-Security-Policy: only this site's own files may be loaded or run.
    // 'unsafe-inline' is kept for style only (small inline style attributes); no inline scripts are used.
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; "
         . "img-src 'self' data: blob:; font-src 'self'; object-src 'none'; base-uri 'self'; "
         . "form-action 'self'; frame-ancestors 'self'");

}

<?php

/*
 * CareerCraft - input cleaning and validation helpers.
 * Loaded through includes/functions.php.
 */

/* ------------------------------------------------------------
   Input cleaning and validation
------------------------------------------------------------ */
function clean_input($value){

    if(!is_string($value)){
        return "";
    }

    $value = str_replace("\r\n", "\n", $value);
    $value = str_replace("\r", "\n", $value);

    // remove invisible control characters (keeps normal new lines)
    $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

    return trim($value);

}

function text_length($value){

    if(function_exists('mb_strlen')){
        return mb_strlen($value, 'UTF-8');
    }

    return strlen($value);

}

function to_lower($value){

    if(function_exists('mb_strtolower')){
        return mb_strtolower($value, 'UTF-8');
    }

    return strtolower($value);

}

// Adds a message to $errors when the text is invalid. Returns true when it is fine.
function check_length($value, $label, $min, $max, &$errors){

    if(preg_match('//u', $value) !== 1){
        $errors[] = $label . " contains invalid characters.";
        return false;
    }

    $length = text_length($value);

    if($min > 0 && $length < $min){

        if($length === 0){
            $errors[] = $label . " is required.";
        }else{
            $errors[] = $label . " must be at least " . $min . " characters.";
        }

        return false;
    }

    if($length > $max){
        $errors[] = $label . " must be at most " . $max . " characters.";
        return false;
    }

    return true;

}

// Returns a clean http/https URL (adds https:// when missing), "" when empty, or false when invalid.
function normalize_url($url){

    $url = trim((string)$url);

    if($url === ""){
        return "";
    }

    if(!preg_match('#^[a-z][a-z0-9+.\-]*:#i', $url)){
        $url = "https://" . $url;
    }

    $parts = parse_url($url);

    if($parts === false || empty($parts['scheme']) || empty($parts['host'])){
        return false;
    }

    if(!in_array(strtolower($parts['scheme']), array('http', 'https'), true)){
        return false;
    }

    if(filter_var($url, FILTER_VALIDATE_URL) === false){
        return false;
    }

    return $url;

}

// For printing a stored link: returns a safe URL or "" (blocks javascript:, data:, etc.)
function safe_url($url){

    $clean = normalize_url($url);

    return ($clean === false) ? "" : $clean;

}

function post_too_large(){

    return (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST'
        && empty($_POST) && empty($_FILES)
        && !empty($_SERVER['CONTENT_LENGTH']));

}

// A positive whole number, or 0 when the value is not valid
function positive_int($value){

    if(!is_string($value) && !is_int($value)){
        return 0;
    }

    $number = filter_var($value, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

    return ($number === false) ? 0 : (int)$number;

}

// "PHP,  MySQL ,JS" -> "PHP, MySQL, JS"
function normalize_tags($text){

    $parts = preg_split('/[,\n]+/', (string)$text);

    $clean = array();

    foreach($parts as $part){

        $part = trim(preg_replace('/\s+/', ' ', $part));

        if($part !== ""){
            $clean[] = $part;
        }

    }

    return implode(", ", $clean);

}

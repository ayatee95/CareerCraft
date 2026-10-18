<?php

/*
 * CareerCraft - page helpers: alerts, flash messages, redirects, small display helpers.
 * Loaded through includes/functions.php.
 */

/* ------------------------------------------------------------
   Flash messages (shown once after a redirect)
------------------------------------------------------------ */
function flash_add($type, $text){

    secure_session_start();

    if(!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])){
        $_SESSION['flash'] = array();
    }

    $_SESSION['flash'][] = array('type' => $type, 'text' => $text);

}

function flash_render(){

    if(empty($_SESSION['flash']) || !is_array($_SESSION['flash'])){
        return;
    }

    foreach($_SESSION['flash'] as $item){

        render_alert($item['type'] === 'success' ? 'success' : 'error', $item['text']);

    }

    unset($_SESSION['flash']);

}


/* ------------------------------------------------------------
   Shared page helpers
------------------------------------------------------------ */

// One alert box. $type: 'success' | 'error' | 'info'. The text is always escaped here.
function render_alert($type, $text){

    $icons = array('success' => 'check-circle', 'error' => 'alert-circle', 'info' => 'info');

    $type = isset($icons[$type]) ? $type : 'info';

    $role = ($type === 'error') ? 'alert' : 'status';

    echo '<div class="alert alert-' . $type . '" role="' . $role . '">' . icon($icons[$type]) . '<div>' . e($text) . '</div></div>' . "\n";

}

// Redirects to another page and stops the script.
function redirect_to($page){

    header("Location: " . $page);

    exit();

}

// Prints error messages as one alert box (a bullet list when there are several).
function render_errors($errors){

    if(empty($errors)){
        return;
    }

    if(count($errors) === 1){
        render_alert('error', reset($errors));
        return;
    }

    echo '<div class="alert alert-error" role="alert">' . icon('alert-circle') . '<div><ul>';

    foreach($errors as $err){
        echo '<li>' . e($err) . '</li>';
    }

    echo '</ul></div></div>' . "\n";

}

// Returns the row of $table with this id ONLY if it belongs to $user_id, otherwise null.
// This is the single place that enforces "a user can only open his own skills/projects".
function find_owned_row($conn, $table, $id, $user_id){

    $allowedTables = array('skills', 'projects');

    if(!in_array($table, $allowedTables, true) || $id < 1){
        return null;
    }

    return db_one(
        $conn,
        "SELECT * FROM " . $table . " WHERE id = ? AND user_id = ? LIMIT 1",
        "ii",
        array($id, $user_id)
    );

}

// "PHP, MySQL ,JS" -> array("PHP", "MySQL", "JS")  (for showing technologies as chips)
function tech_list($text){

    $items = array();

    foreach(explode(",", (string)$text) as $part){

        $part = trim($part);

        if($part !== ""){
            $items[] = $part;
        }

    }

    return $items;

}

// First letter of a name, upper-case (used when a user has no profile picture)
function initial_letter($name){

    $name = trim((string)$name);

    if($name === ""){
        return "?";
    }

    if(function_exists('mb_substr') && function_exists('mb_strtoupper')){
        return mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
    }

    return strtoupper(substr($name, 0, 1));

}

// A short plain-text summary (for the page description of a public portfolio)
function text_excerpt($text, $max){

    $text = trim(preg_replace('/\s+/', ' ', (string)$text));

    if(function_exists('mb_strlen') && function_exists('mb_substr')){

        return (mb_strlen($text, 'UTF-8') > $max) ? rtrim(mb_substr($text, 0, $max - 1, 'UTF-8')) . "..." : $text;

    }

    return (strlen($text) > $max) ? rtrim(substr($text, 0, $max - 1)) . "..." : $text;

}

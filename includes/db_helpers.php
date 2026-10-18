<?php

/*
 * CareerCraft - database helpers (prepared statements = no SQL injection).
 * Loaded through includes/functions.php. The connection itself is created in includes/database.php.
 */

$GLOBALS['db_last_errno'] = 0;
$GLOBALS['db_affected_rows'] = 0;


/* ------------------------------------------------------------
   Database helpers (prepared statements = no SQL injection)
------------------------------------------------------------ */
function db_run($conn, $sql, $types, $params){

    $GLOBALS['db_last_errno'] = 0;

    $stmt = mysqli_prepare($conn, $sql);

    if(!$stmt){
        $GLOBALS['db_last_errno'] = mysqli_errno($conn);
        error_log("CareerCraft DB prepare error (" . mysqli_errno($conn) . "): " . mysqli_error($conn));
        return false;
    }

    if($types !== ""){
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }

    if(!mysqli_stmt_execute($stmt)){
        $GLOBALS['db_last_errno'] = mysqli_stmt_errno($stmt);
        error_log("CareerCraft DB execute error (" . mysqli_stmt_errno($stmt) . "): " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }

    return $stmt;

}

// One row (array) or null
function db_one($conn, $sql, $types = "", $params = array()){

    $stmt = db_run($conn, $sql, $types, $params);

    if(!$stmt){
        return null;
    }

    $result = mysqli_stmt_get_result($stmt);

    $row = $result ? mysqli_fetch_assoc($result) : null;

    mysqli_stmt_close($stmt);

    return $row ? $row : null;

}

// All rows (array of arrays)
function db_all($conn, $sql, $types = "", $params = array()){

    $rows = array();

    $stmt = db_run($conn, $sql, $types, $params);

    if(!$stmt){
        return $rows;
    }

    $result = mysqli_stmt_get_result($stmt);

    if($result){
        while($row = mysqli_fetch_assoc($result)){
            $rows[] = $row;
        }
    }

    mysqli_stmt_close($stmt);

    return $rows;

}

// INSERT / UPDATE. Returns true/false. On failure the MySQL error number is in $GLOBALS['db_last_errno'].
function db_write($conn, $sql, $types = "", $params = array()){

    $GLOBALS['db_affected_rows'] = 0;

    $stmt = db_run($conn, $sql, $types, $params);

    if(!$stmt){
        return false;
    }

    $GLOBALS['db_affected_rows'] = mysqli_stmt_affected_rows($stmt);

    mysqli_stmt_close($stmt);

    return true;

}


/* ------------------------------------------------------------
   Schema check (lets the code work before/after an optional migration)
------------------------------------------------------------ */
function db_has_column($conn, $table, $column){

    static $cache = array();

    $key = $table . "." . $column;

    if(!isset($cache[$key])){

        $row = db_one(
            $conn,
            "SELECT COUNT(*) AS c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            "ss",
            array($table, $column)
        );

        $cache[$key] = ($row && (int)$row['c'] > 0);

    }

    return $cache[$key];

}

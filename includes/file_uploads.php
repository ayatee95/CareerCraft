<?php

/*
 * CareerCraft - secure file upload and stored-file helpers.
 * Loaded through includes/functions.php.
 */

define('MAX_IMAGE_BYTES', 2 * 1024 * 1024);   // 2 MB
define('MAX_PDF_BYTES', 5 * 1024 * 1024);     // 5 MB


/* ------------------------------------------------------------
   Secure file uploads
------------------------------------------------------------ */
function upload_error_message($code){

    switch($code){

        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return "The file is too large.";

        case UPLOAD_ERR_PARTIAL:
            return "The file was only partly uploaded. Please try again.";

        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
        case UPLOAD_ERR_EXTENSION:
            return "The server could not save the file. Please try again later.";

        default:
            return "The file could not be uploaded.";

    }

}

// Returns: null = no file chosen, string = stored file name, false = error (see $error)
function save_uploaded_image($file, $dir, &$error){

    $error = "";

    if(!is_array($file) || !isset($file['error'])){
        return null;
    }

    if(is_array($file['error'])){
        $error = "Invalid upload.";
        return false;
    }

    if($file['error'] === UPLOAD_ERR_NO_FILE){
        return null;
    }

    if($file['error'] !== UPLOAD_ERR_OK){
        $error = upload_error_message($file['error']);
        return false;
    }

    if(!is_uploaded_file($file['tmp_name'])){
        $error = "Invalid upload.";
        return false;
    }

    if($file['size'] <= 0){
        $error = "The image file is empty.";
        return false;
    }

    if($file['size'] > MAX_IMAGE_BYTES){
        $error = "The image must be smaller than 2 MB.";
        return false;
    }

    // Look at the real content of the file, not at its name.
    $info = @getimagesize($file['tmp_name']);

    if($info === false){
        $error = "The file is not a valid image.";
        return false;
    }

    $allowed = array(
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif'
    );

    if(defined('IMAGETYPE_WEBP')){
        $allowed[IMAGETYPE_WEBP] = 'webp';
    }

    if(!isset($allowed[$info[2]])){
        $error = "Only JPG, PNG, GIF or WEBP images are allowed.";
        return false;
    }

    if($info[0] < 1 || $info[1] < 1 || $info[0] > 8000 || $info[1] > 8000){
        $error = "The image dimensions are not supported.";
        return false;
    }

    // The server chooses the name and the extension (never the visitor).
    $name = random_hex(16) . "." . $allowed[$info[2]];

    $target = rtrim($dir, "/\\") . "/" . $name;

    if(!move_uploaded_file($file['tmp_name'], $target)){
        $error = "The server could not save the image. Please try again.";
        return false;
    }

    @chmod($target, 0644);

    return $name;

}

// Returns: null = no file chosen, string = stored file name, false = error (see $error)
function save_uploaded_pdf($file, $dir, &$error){

    $error = "";

    if(!is_array($file) || !isset($file['error'])){
        return null;
    }

    if(is_array($file['error'])){
        $error = "Invalid upload.";
        return false;
    }

    if($file['error'] === UPLOAD_ERR_NO_FILE){
        return null;
    }

    if($file['error'] !== UPLOAD_ERR_OK){
        $error = upload_error_message($file['error']);
        return false;
    }

    if(!is_uploaded_file($file['tmp_name'])){
        $error = "Invalid upload.";
        return false;
    }

    $originalName = isset($file['name']) ? (string)$file['name'] : "";

    if(strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'pdf'){
        $error = "Only PDF files are allowed.";
        return false;
    }

    if($file['size'] <= 0){
        $error = "The PDF file is empty.";
        return false;
    }

    if($file['size'] > MAX_PDF_BYTES){
        $error = "The PDF must be smaller than 5 MB.";
        return false;
    }

    // Check the real content: a PDF starts with the "%PDF-" signature.
    $head = "";
    $handle = @fopen($file['tmp_name'], 'rb');

    if($handle){
        $head = (string)fread($handle, 1024);
        fclose($handle);
    }

    if(strpos($head, '%PDF-') === false){
        $error = "The file is not a valid PDF.";
        return false;
    }

    if(class_exists('finfo')){

        $mime = false;

        try{

            $finfo = new finfo(FILEINFO_MIME_TYPE);

            $mime = $finfo->file($file['tmp_name']);

        }catch(Throwable $ex){

            $mime = false;   // fileinfo not usable on this server: the %PDF- check above still applies

        }

        if($mime && !in_array($mime, array('application/pdf', 'application/x-pdf'), true)){
            $error = "The file is not a valid PDF.";
            return false;
        }

    }

    // Keep a readable part of the original name, but only safe characters.
    $base = preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $base = trim((string)$base, '_');
    $base = substr($base, 0, 60);

    if($base === ""){
        $base = "resume";
    }

    $name = time() . "_" . random_hex(4) . "_" . $base . ".pdf";

    $target = rtrim($dir, "/\\") . "/" . $name;

    if(!move_uploaded_file($file['tmp_name'], $target)){
        $error = "The server could not save the file. Please try again.";
        return false;
    }

    @chmod($target, 0644);

    return $name;

}


/* ------------------------------------------------------------
   Stored files (uploads folder)
------------------------------------------------------------ */
function upload_dir($sub){

    return dirname(__DIR__) . "/uploads/" . $sub;

}

// Full path of a stored file, or "" when the name is unsafe or the file does not exist
function stored_file_path($sub, $name){

    $name = (string)$name;

    if($name === "" || $name !== basename($name) || strpos($name, "\0") !== false){
        return "";
    }

    $path = upload_dir($sub) . "/" . $name;

    return is_file($path) ? $path : "";

}

function stored_file_exists($sub, $name){

    return stored_file_path($sub, $name) !== "";

}

function delete_stored_file($sub, $name){

    $path = stored_file_path($sub, $name);

    if($path === ""){
        return false;
    }

    return @unlink($path);

}

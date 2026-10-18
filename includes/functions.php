<?php

/*
 * CareerCraft - shared helper loader.
 *
 * Every page loads this file (through includes/auth.php or includes/database.php).
 * The helpers are split by topic, so each file stays small and easy to read:
 *
 *   security.php      escaping (XSS), sessions, CSRF, security headers
 *   validation.php    input cleaning and validation
 *   file_uploads.php  secure image/PDF uploads and stored files
 *   db_helpers.php    prepared-statement helpers
 *   helpers.php       alerts, flash messages, redirects, small page helpers
 *   icons.php         inline SVG icons
 */

require_once __DIR__ . "/security.php";
require_once __DIR__ . "/validation.php";
require_once __DIR__ . "/file_uploads.php";
require_once __DIR__ . "/db_helpers.php";
require_once __DIR__ . "/helpers.php";
require_once __DIR__ . "/icons.php";

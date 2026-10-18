<?php

/*
 * CareerCraft database settings - EXAMPLE FILE (safe to publish, contains no real credentials).
 *
 * How to use it:
 *   1. Copy this file and name the copy  config.php  (same folder).
 *   2. Replace the placeholder values with your own database settings.
 *   3. NEVER publish config.php (it is listed in .gitignore).
 *
 * Settings used by includes/database.php:
 *   host      MySQL server name. On AppServ this is normally "localhost".
 *   username  MySQL user.
 *   password  MySQL password (AppServ: the root password you chose during installation).
 *   database  Database name. database/schema.sql creates one called "careercraft".
 *   charset   Optional. Use "utf8mb4" for a database created with database/schema.sql.
 *             If you already have an older database with data, leave this line out.
 */

return array(
    'host'     => 'localhost',
    'username' => 'your_mysql_user',
    'password' => 'your_mysql_password',
    'database' => 'careercraft',
    'charset'  => 'utf8mb4',
);

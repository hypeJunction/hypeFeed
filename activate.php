<?php

require_once __DIR__ . '/autoloader.php';

// Setup MySQL databases
run_sql_script(__DIR__ . '/install/mysql.sql');

<?php

require_once('utils/db_mysqli.php');
require_once('utils/db_mysqli_logic.php');

use yeticave\db\functions as db_func;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$con = db_func\get_connection();

if ($con) {
    db_func\set_charset($con);
} else {
    die("Ошибка подключения: " . mysqli_connect_error());
}

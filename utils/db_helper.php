<?php

require_once('utils/db_mysqli.php');
require_once('utils/db_mysqli_logic.php');

use yeticave\db\functions as db_func;
$con = db_func\get_connection();

if ($con) {
    db_func\set_charset($con);
} else {
    die("Ошибка подключения: " . mysqli_connect_error());
}

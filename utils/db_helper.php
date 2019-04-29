<?php

const DB_CON_TYPE = 'mysqli'; // pdo

if (DB_CON_TYPE === 'pdo') {
    require_once('utils/db_pdo.php');
    //use yeticave\db\pdo_functions as db_func;
} else if (DB_CON_TYPE === 'mysqli') {
    require_once('utils/db_mysqli.php');
    //use yeticave\db\mysqli_functions as db_func;
}

use yeticave\db\functions as db_func;
$con = db_func\get_connection();

if ($con) {
    db_func\set_charset($con);
}
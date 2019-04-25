<?php

namespace yeticave\db\functions;

require_once(dirname(__FILE__) . '/../db_config.php');

use yeticave\db\config as db_conf;
use PDO;

// Возвращает подключение к БД.
// function get_connection()
// {
//     $con = mysqli_connect(db_conf\HOST, db_conf\USER, db_conf\PASSWORD, db_conf\DB_NAME);

//     return $con;
// }

function get_connection() 
{
    $connection_string = sprintf("mysql:host=%s;dbname=%s", db_conf\HOST, db_conf\DB_NAME);
    $con = new PDO($connection_string, db_conf\USER, db_conf\PASSWORD);

    return $con;
}

// Установить кодировки соединения с БД.
// function set_charset($con)
// {
//     mysqli_set_charset($con, "utf8");
// }

function set_charset($con)
{
    $con->exec('set names utf8');
}

// Возвращает список категорий лотов.
// function get_stuff_categories($con)
// {
//     $sql = 'SELECT * FROM stuff_category';
//     $query_result = mysqli_query($con, $sql);

//     $result = null;
//     $error = null;

//     if (!$query_result) {
//         // Как вернуть нормально ошибку? возвращать массив? (первое это значение, второе это ошибка?)
//         // $result = mysqli_error($con);
//         $error = get_lats_db_error($con);
//     } else {
//         $result = mysqli_fetch_all($query_result, MYSQLI_ASSOC);
//     }

//     return ['result' => $result, 'error' => $error];
// }

function get_stuff_categories($con)
{
    $sql = 'SELECT * FROM stuff_category';
    $query_result = $con->query($sql);

    $result = null;
    $error = null;

    if (!$query_result) {
        $error = get_lats_db_error($con);
    } else {
        $result = $query_result->fetchAll();
    }

    $query_result = null;

    return ['result' => $result, 'error' => $error];
}

// Возвращает список лотов. 
// function get_lots($con)
// {
//     $sql = 'SELECT  l.name,
//                     l.start_price, 
//                     l.image_url, 
//                     l.creation_date,
//                     l.end_date,
//                     cat.name category, 
//                     l.description
//                 FROM lot as l
//                 JOIN stuff_category as cat on l.category_id = cat.id
//                 ORDER BY l.creation_date DESC';

//     $query_result = mysqli_query($con, $sql);

//     $result = null;
//     $error = null;

//     if (!$query_result) {
//         $error = get_lats_db_error($con);
//     } else {
//         $result = mysqli_fetch_all($query_result, MYSQLI_ASSOC);
//     }

//     return ['result' => $result, 'error' => $error];
// }

function get_lots($con)
{
    $sql = 'SELECT  l.name,
                    l.start_price, 
                    l.image_url, 
                    l.creation_date,
                    l.end_date,
                    cat.name category, 
                    l.description
                FROM lot as l
                JOIN stuff_category as cat on l.category_id = cat.id
                ORDER BY l.creation_date DESC';

    $query_result = $con->query($sql);

    $result = null;
    $error = null;

    if (!$query_result) {
        $error = get_lats_db_error($con);
    } else {
        $result = $query_result->fetchAll();
    }

    $query_result = null;

    return ['result' => $result, 'error' => $error];
}

// Получить последнюю ошибку при работе с БД.
// function get_lats_db_error($con)
// {
//     return mysqli_error($con);
// }

function get_lats_db_error($con)
{
    return $con->errorInfo()[2];
}

// Вспомогательная функция получения записей.
function db_fetch_data($link, $sql, $data = [])
{
    $result = [];
    $stmt = db_get_prepare_stmt($link, $sql, $data);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res) {
        $result = mysqli_fetch_all($res, MYSQLI_ASSOC);
    }

    return $result;
}

// ToDo
// Написать аналог db_fetch_data для PDO

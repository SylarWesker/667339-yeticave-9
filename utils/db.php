<?php

namespace yeticave\db\functions;

require_once(dirname(__FILE__) . '/../db_config.php');

use yeticave\db\config as db_conf;

// Возвращает подключение к БД.
function get_connection()
{
    $con = mysqli_connect(db_conf\HOST, db_conf\USER, db_conf\PASSWORD, db_conf\DB_NAME);

    return $con;
}

// Возвращает список категорий лотов.
function get_stuff_categories($con)
{
    $sql = 'SELECT * FROM stuff_category';
    $query_result = mysqli_query($con, $sql);

    $result = null;
    $error = null;

    if (!$query_result) {
        // Как вернуть нормально ошибку? возвращать массив? (первое это значение, второе это ошибка?)
        // $result = mysqli_error($con);
        $error = mysqli_error($con);
    } else {
        $result = mysqli_fetch_all($query_result, MYSQLI_ASSOC);
    }

    return ['result' => $result, 'error' => $error];
}

// Возвращает список лотов. 
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

    $query_result = mysqli_query($con, $sql);

    $result = null;
    $error = null;

    if (!$result) {
        $error = mysqli_error($con);
    } else {
        $result = mysqli_fetch_all($query_result, MYSQLI_ASSOC);
    }

    return ['result' => $result, 'error' => $error];
}
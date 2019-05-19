<?php

namespace yeticave\db\functions;

use PDO;

// ToDo
// Доделать как все будет готово до уровня db_mysqli.php

// Возвращает подключение к БД.
function get_connection() 
{
    $db_params = require_once(dirname(__FILE__) . '/../db_config.php');

    $connection_string = sprintf("mysql:host=%s;dbname=%s", $db_params['host'], $db_params['db_name']);
    $con = new PDO($connection_string, $db_params['user'], $db_params['password']);

    return $con;
}

// Установить кодировки соединения с БД.
function set_charset($con)
{
    $con->exec('set names utf8');
}

// Возвращает список категорий лотов.
function get_stuff_categories($con)
{
    $sql = 'SELECT * FROM stuff_category';
    $query_result = $con->query($sql);

    $result = null;
    $error = null;

    if (!$query_result) {
        $error = get_last_db_error($con);
    } else {
        $result = $query_result->fetchAll();
    }

    $query_result = null;

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

    $query_result = $con->query($sql);

    $result = null;
    $error = null;

    if (!$query_result) {
        $error = get_last_db_error($con);
    } else {
        $result = $query_result->fetchAll();
    }

    $query_result = null;

    return ['result' => $result, 'error' => $error];
}

// Получить последнюю ошибку при работе с БД.
function get_last_db_error($con)
{
    return $con->errorInfo()[2];
}

// Вспомогательная функция получения записей.
function db_fetch_data($link, $sql, $data = [])
{
    $result = [];
    
    $prepared_query = $link->prepare($sql);
    $res = $prepared_query->execute($data);

    if ($res) {
        $result = $res->fetchAll();
    }

    $res = null;

    return $result;
}

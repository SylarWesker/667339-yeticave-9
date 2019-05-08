<?php

namespace yeticave\db\functions;

// ToDo
// Вынести бы все sql запросы в одно место...
// 1) Хранимые процедуры
// 2) отдельный файл.

// Возвращает подключение к БД.
function get_connection()
{
    $db_params = require_once(dirname(__FILE__) . '/../db_config.php');

    $con = mysqli_connect($db_params['host'], 
                          $db_params['user'], 
                          $db_params['password'], 
                          $db_params['db_name']);

    return $con;
}

// Установить кодировки соединения с БД.
function set_charset($con)
{
    mysqli_set_charset($con, "utf8");
}

// Возвращает список категорий лотов.
function get_stuff_categories($con)
{
    $sql = 'SELECT * FROM stuff_category';
    $result_data = db_fetch_data($con, $sql);

    return $result_data;
}

// Возвращает список лотов. 
// $id_list - список лотов. Если ни одного не передано, то возвращаем все лоты.
function get_lots($con, $id_list = [])
{
    $sql_where_id_part = ' ';
    if (isset($id_list)) {
        if (count($id_list) != 0) {
            // список id пока не проверяю. предполагаю, что он коректный и проверен извне (на sql инъекции)
            // $id_str = implode(', ', $id_list);

            // что-то как-то сложно и странно. Можно ли проще?
            $query_placeholders = array_fill(0,  count($id_list), '?');
            $id_placeholders = implode(', ', $query_placeholders);
            $sql_where_id_part = ' AND l.id IN (' . $id_placeholders . ') ';
        }
    }

    $sql = 'SELECT  l.*,
                    cat.name category, 
                    IFNULL(max(b.price), l.start_price) current_price
            FROM lot as l
            LEFT JOIN stuff_category as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id
            WHERE l.end_date IS NOT NULL 
            AND l.end_date > NOW() 
            AND l.winner_id IS NULL'
            . $sql_where_id_part .
            'GROUP BY l.id,
                      cat.name
            ORDER BY l.creation_date DESC';

    $result_data = db_fetch_data($con, $sql, $id_list);

    return $result_data;
}

// Получить последнюю ошибку при работе с БД.
function get_lats_db_error($con)
{
    return mysqli_error($con);
}

// Вспомогательная функция получения записей.
function db_fetch_data($link, $sql, $data = [])
{
    $result = null;
    $error = null;

    $stmt = db_get_prepare_stmt($link, $sql, $data);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res) {
        $result = mysqli_fetch_all($res, MYSQLI_ASSOC);
    } else {
        // Слабое место. до этого выполнялось несколько функций и при их выполнении тоже могли быть ошибки, а мы записываем только последнюю.
        $error = get_lats_db_error($link);
    }

    return ['result' => $result, 'error' => $error];
}

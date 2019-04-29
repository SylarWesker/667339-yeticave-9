<?php

namespace yeticave\db\functions;

require_once(dirname(__FILE__) . '/../db_config.php');

use yeticave\db\config as db_conf;

// Возвращает подключение к БД.
function get_connection()
{
    $con = mysqli_connect(db_conf\HOST, 
                          db_conf\USER, 
                          db_conf\PASSWORD, 
                          db_conf\DB_NAME
                        );

    // $con = mysqli_connect(db_conf\$db_params['host'], 
    //                     db_conf\$db_params['user'], 
    //                     db_conf\$db_params['password'], 
    //                     db_conf\$db_params['db_name']
    //                   );

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
    $sql_where_part = ' ';
    if (isset($id_list)) {
        if (count($id_list) != 0) {
            // список id пока не проверяю. предполагаю, что он коректный и проверен извне.
            // возможно потом и добавлю проверку...
            // $id_str = implode(', ', $id_list);

            // что-то как-то сложно и странно. Можно ли проще?
            $query_placeholders = array_fill(0,  count($id_list), '?');
            $id_placeholders = implode(', ', $query_placeholders);
            $sql_where_part = ' WHERE l.id IN (' . $id_placeholders . ') ';
        }
    }

    $sql = 'SELECT  l.id,
                    l.name,
                    l.start_price, 
                    l.image_url, 
                    l.creation_date,
                    l.end_date,
                    cat.name category, 
                    l.description
            FROM lot as l
            JOIN stuff_category as cat on l.category_id = cat.id'
            . $sql_where_part .
            'ORDER BY l.creation_date DESC';

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

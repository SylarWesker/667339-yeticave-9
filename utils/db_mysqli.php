<?php

namespace yeticave\db\functions;

 // ToDo
 // Проверить все функции работы с БД на предмет поведения при ошибке (нет соединения, неверный sql запрос)

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
            // Этот кусок кода выполняется снова. функция?
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

// Возвращает id категории по ее названию.
function get_category_id($con, $category_name)
{
    $sql = 'SELECT id FROM stuff_category WHERE name = ?';
    $result_data = db_fetch_data($con, $sql, [ $category_name ]);

    $result = false;

    if ($result_data['error'] !== NULL) {
        $result = false;
    } else {
        $result = count($result_data['result']) > 0 ? $result_data['result'][0]['id'] : NULL;
    }

    return $result;
}

function get_data_by_field($con, $table_name, $field_name, $field_value, $limit = null) 
{
    $sql_limit_part = $limit ? " LIMIT $limit" : '';

    $sql = "SELECT * FROM `$table_name` WHERE `$field_name` = ? $sql_limit_part";
    $result_data = db_fetch_data($con, $sql, [ $field_value ]);

    return $result_data;
}

function filter($con, $table_name, $field_name, $field_value, $limit = null) 
{
    $sql_limit_part = $limit ? " LIMIT $limit" : '';

    $sql = "SELECT * FROM `$table_name` WHERE `$field_name` = ? $sql_limit_part";
    $result_data = db_fetch_data($con, $sql, [ $field_value ]);

    $result = false;

    if ($result_data['error'] !== NULL) {
        $result = false;
    } else {
        $result = !empty($result_data['result']);
    }

    return $result;
}

function has_email($con, $email)
{
    return filter($con, 'user', 'email', $email, 1);
}

function has_user($con, $user_name)
{
    return filter($con, 'user', 'name', $user_name, 1);
}

function get_userdata_by_email($con, $email) 
{
    $user_data = get_data_by_field($con, 'user', 'email', $email, 1);

    return ['error' => $user_data['error'], 'result' => $user_data['result'][0]];
}

// Добавляет пользователя в БД.
function add_user($con, $email, $user_name, $password, $contacts) 
{
    $params = [ $email, $user_name, $password, $contacts];

    $sql = 'INSERT INTO `user` (email, 
                                name, 
                                password, 
                                contacts) 
            VALUES (?, ?, ?, ?)';

    $result_data = db_fetch_data($con, $sql, $params);

    $insert_id = mysqli_insert_id($con);

    return $insert_id ?? null;
}

// Добавляет лот.
// Возвращает id лота в случае успеха, NULL - если нет.
function add_lot($con, $params) 
{
    $query_placeholders = array_fill(0,  count($params), '?');
    $query_placeholders_str = implode(', ', $query_placeholders);

    $sql = 'INSERT INTO lot (author_id, 
                            name, 
                            category_id, 
                            description, 
                            start_price, 
                            step_bet, 
                            end_date, 
                            image_url) 
            VALUES (' . $query_placeholders_str . ')';

    // Порядок параметров важен!
    // - можно попробовать привязать параметры
    // - задать в массиве порядок по ключам.
    $keys_order = ['author_id', 'name', 'category_id', 'description', 'start_price', 'step_bet', 'end_date', 'image_url'];
    $ordered_params = array_order_by_key($params,  $keys_order);

    $result_data = db_fetch_data($con, $sql, $ordered_params);

    $insert_id = mysqli_insert_id($con);
    $added_lot_id = $insert_id  === 0 ? NULL : $insert_id;

    return $added_lot_id;
}

// Получить последнюю ошибку при работе с БД.
function get_last_db_error($con)
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
        $error = get_last_db_error($link);
    }

    return ['result' => $result, 'error' => $error];
}

<?php

namespace yeticave\db\functions;

// ToDo
// Разделить функции общей работы с БД и функции работы с конкретной БД (yeticave)
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
            'GROUP BY l.id
            ORDER BY l.creation_date DESC'; 
            // зачем-то групировал по категории... (cat.name)
            // проверить. если не нужно, то убрать

    $result_data = db_fetch_data($con, $sql, $id_list);

    return $result_data;
}

// Возвращает ставки пользователя. 
function get_bets($con, $user_id) 
{
    $sql = 'SELECT b.*, l.*, cat.name as category_name, u.contacts FROM `bet` as b 
            JOIN `lot` as l on b.lot_id = l.id
            JOIN `stuff_category` as cat on l.category_id = cat.id
            JOIN `user` as u on l.author_id = u.id
            WHERE b.user_id = ?
            ORDER BY b.create_date DESC';

    $result_data = db_fetch_data($con, $sql, [ $user_id ]);

    return $result_data;
}

// Возвращает историю ставок по лоту.
function get_bets_history($con, $lot_id)
{
    $sql = 'SELECT b.*, u.name FROM `bet` as b 
            JOIN `lot` as l on b.lot_id = l.id
            JOIN `user` as u on b.user_id = u.id
            WHERE l.id = ?
            ORDER BY b.create_date DESC';
    
    $result_data = db_fetch_data($con, $sql, [ $lot_id ]);

    return $result_data;
}

// Возвращает минимально возможную ставку для лота по его id.
function get_lot_min_bet($con, $lot_id) 
{
    $sql = 'SELECT l.id, b.lot_id, IF(ISNULL(b.price), l.start_price, MAX(b.price) + l.step_bet) as min_bet 
            FROM `bet` as b
            RIGHT JOIN `lot` as l
            ON l.id = b.lot_id
            GROUP BY l.id
            HAVING l.id = ?';

    $result_data = db_fetch_data($con, $sql, [ $lot_id ]);

    $result = NULL;

    if ($result_data['error'] !== NULL) {
        $result = NULL;
    } else {
        $result = count($result_data['result']) > 0 ? $result_data['result'][0]['min_bet'] : NULL;
    }

    return $result;
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
    $result_data = get_data_by_field($con, $table_name, $field_name, $field_value, $limit);

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

function has_lot($con, $lot_id)
{
    return filter($con, 'lot', 'id', $lot_id);
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

function add_bet($con, $user_id, $lot_id, $bet_cost) 
{
    $params = [ $user_id, $lot_id, $bet_cost];

    $query_placeholders = array_fill(0, count($params), '?');
    $query_placeholders_str = implode(', ', $query_placeholders);

    $sql = 'INSERT INTO bet (user_id, 
                            lot_id, 
                            price) 
            VALUES (' . $query_placeholders_str . ')';

    $result_data = db_fetch_data($con, $sql, $params);

    $insert_id = mysqli_insert_id($con);
    $added_bet_id = $insert_id  === 0 ? NULL : $insert_id;

    return $added_bet_id;
}

// Получить последнюю ошибку при работе с БД.
function get_last_db_error($con)
{
    return mysqli_error($con);
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = []) {
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
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

// Получаю список лотов с помощью полнотекстового поиска по названию и описанию лота.
function get_lots_by_fulltext_search($con, $search_query, $limit, $offset)
{
    $params = [$search_query, $limit, $offset];

    // ToDo
    // В некоторых запросах одни и те же куски логики... 
    // Тут например "активные лоты" - у которых нет победителя и дата завершения не прошла
    // Эта логика может поменяться и тогда придется искать все места, где она использовалась и менять ее
    // Как это можно изменить? (чтобы она была в одном месте) и чтобы к основной логике можно было добавлять что-то или менять "параметры"
    $sql =  'SELECT l.*, 
                    cat.name category, 
                    IFNULL(max(b.price), l.start_price) current_price,
                    COUNT(b.id) as bets_count
            FROM `lot` as l 
            LEFT JOIN `stuff_category` as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id
            WHERE MATCH(l.name, l.description) AGAINST(?) AND 
                    l.end_date IS NOT NULL AND 
                    l.end_date > NOW() AND
                    l.winner_id IS NULL
            GROUP BY l.id
            ORDER BY l.creation_date
            LIMIT ?
            OFFSET ?';

    $result_data = db_fetch_data($con, $sql, $params);

    return $result_data;
}

// ToDo
// Неужели это единственный способ (кроме кэша) узнать кол-во лотов подходящих под поисковый запрос. 
// Используется для пагинации.
function get_lots_count_with_fulltext_search($con, $search_query)
{
    $sql =  'SELECT COUNT(*) as count_lots FROM 
            (SELECT l.id
            FROM `lot` as l 
            LEFT JOIN `stuff_category` as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id
            WHERE MATCH(l.name, l.description) AGAINST(?) AND 
                    l.end_date IS NOT NULL AND 
                    l.end_date > NOW() AND
                    l.winner_id IS NULL
            GROUP BY l.id
            ORDER BY l.creation_date) as help';

    $result_data = db_fetch_data($con, $sql, [$search_query]); 

    //return $result_data;
    return ['result' => $result_data['result'][0]['count_lots'], 'error' => $result_data['error']];
}

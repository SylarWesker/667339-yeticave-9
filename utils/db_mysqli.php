<?php

namespace yeticave\db\functions;

// ToDo
// Разделить функции общей работы с БД и функции работы с конкретной БД (yeticave)

// ToDo
// Проверить все функции работы с БД на предмет поведения при ошибке (нет соединения, неверный sql запрос)

// ToDo
// Дата окончания торгов не может быть NULL. (а у меня может)
// Исправить запрос создания таблицы и во всех запросах убрать эту проверку.

// ToDo
// Еще раз продумать работу с датами. Дата окончания действия лота не содержит времени. Следовательно
// дата окончания аукциона это полночь или 23.59.59
// Пример
// Дата окончания торгов - 21/05/1991
// Это значит 21/05/1991 00.00 или 21/05/1991 23.59.59
// Уточнить и исправить.

// ToDo
// К предыдущему ToDo - обратить внимание что в запросах использую NOW(). now - возвращает текущую дату и время
// CURRENT_DATE() - только текущую дату.

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
// $show_active - показывать активные лоты? (активные это у которых не истекла дата окончания и пустой победитель)
function get_lots($con, $id_list = [], $show_active = true)
{
    $sql_where_id_part = '';
    if (!empty($id_list)) {
        $placeholders = create_placeholders_for_prepared_query(count($id_list));

        $sql_where_id_part = 'l.id IN (' . $placeholders . ')';
    }
    
    $sql_active_lots_where_part = '';
    if ($show_active) {
        $sql_active_lots_where_part = 'l.end_date > NOW() 
                                       AND l.winner_id IS NULL';
    }

    $parts = [ $sql_where_id_part, $sql_active_lots_where_part];
    $parts = array_filter($parts, function($p) { if(!empty($p)) return $p; });
    $sql_where_part = implode(' AND ', $parts);

    if (!empty($sql_where_part)) {
        $sql_where_part = ' WHERE ' . $sql_where_part . ' ';
    }

    $sql = 'SELECT  l.*,
                    cat.name category, 
                    IFNULL(max(b.price), l.start_price) current_price
            FROM lot as l
            LEFT JOIN stuff_category as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id ' .
            $sql_where_part .
            ' GROUP BY l.id
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

// ToDo
// Использовать функцию filter или get_data_by_field
// Возвращает id категории по ее названию.
function get_category_id($con, $category_name)
{
    $sql = 'SELECT id FROM stuff_category WHERE name = ?'; // ToDo Limit 1 ?
    $result_data = db_fetch_data($con, $sql, [ $category_name ]);

    $result = null;

    if ($result_data['error'] !== null) {
        $result = null;
    } else {
        $result = count($result_data['result']) > 0 ? $result_data['result'][0]['id'] : null;
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
    $inserted_user_id = $insert_id === 0 ? null : $insert_id;

    return $inserted_user_id;
}

// Добавляет лот.
// Возвращает id лота в случае успеха, NULL - если нет.
function add_lot($con, $params) 
{
    $placeholders = create_placeholders_for_prepared_query(count($params));

    $sql = 'INSERT INTO lot (author_id, 
                            name, 
                            category_id, 
                            description, 
                            start_price, 
                            step_bet, 
                            end_date, 
                            image_url) 
            VALUES (' . $placeholders . ')';

    // Порядок параметров важен!
    // - можно попробовать привязать параметры
    // - задать в массиве порядок по ключам.
    $keys_order = ['author_id', 'name', 'category_id', 'description', 'start_price', 'step_bet', 'end_date', 'image_url'];
    $ordered_params = array_order_by_key($params,  $keys_order);

    $result_data = db_fetch_data($con, $sql, $ordered_params);

    $insert_id = mysqli_insert_id($con);
    $added_lot_id = $insert_id === 0 ? null : $insert_id;

    return $added_lot_id;
}

function add_bet($con, $user_id, $lot_id, $bet_cost) 
{
    $params = [ $user_id, $lot_id, $bet_cost ];

    $placeholders = create_placeholders_for_prepared_query(count($params));

    $sql = 'INSERT INTO bet (user_id, lot_id, price) 
            VALUES (?, ?, ?)';

    $result_data = db_fetch_data($con, $sql, $params);

    $insert_id = mysqli_insert_id($con);
    $added_bet_id = $insert_id === 0 ? null : $insert_id;

    return $added_bet_id;
}

// Вспомогательная функция
// Формирует подстановочные знаки для параметров в подготовленных запросах.
function create_placeholders_for_prepared_query($count, $placeholder = '?')
{
    $query_placeholders = array_fill(0, $count, '?');
    $query_placeholders_str = implode(', ', $query_placeholders);

    return $query_placeholders_str;
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
    // ToDo
    // Влияют ли join на кол-во записей??? если нет то убрать
    $sql =  'SELECT COUNT(*) as count_lots FROM 
            (SELECT l.id
            FROM `lot` as l 
            LEFT JOIN `stuff_category` as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id
            WHERE MATCH(l.name, l.description) AGAINST(?) AND
                l.end_date > NOW() AND
                l.winner_id IS NULL
            GROUP BY l.id
            ORDER BY l.creation_date) as help';

    $result_data = db_fetch_data($con, $sql, [$search_query]); 

    return ['result' => $result_data['result'][0]['count_lots'], 'error' => $result_data['error']];
}

// Возвращает лоты без победителей на данный момент и пользователя чья ставка была последней.
function get_lots_without_winners($con)
{
    // Не верно возвращает winner_id (b.user_id) - потому что надо брать id пользователя из ставок с максимальной ставкой по данному лоту
    // $sql = 'SELECT l.id as lot_id, b.user_id as winner_id FROM `lot` l
    //         LEFT JOIN `bet` b on l.id = b.lot_id
    //         WHERE l.winner_id IS NULL AND
    //         l.end_date < CURRENT_DATE() AND
    //         b.user_id IS NOT NULL
    //         GROUP BY l.id';

    // Вроде даже как работает верно
    // тут подошел с другого конца и джойню лоты к ставкам
    // но есть подзапрос - не есть хорошо
    $sql = 'SELECT l.id as lot_id, l.name as lot_name, b1.user_id as winner_id, b1.price FROM `bet` b1
            JOIN `lot` l on l.id = b1.lot_id 
            WHERE price = (SELECT MAX(price) FROM `bet` b2 WHERE b1.lot_id = b2.lot_id) AND
            l.winner_id IS NULL AND
            l.end_date <= CURRENT_DATE() AND
            b1.user_id IS NOT NULL';

    $result_data = db_fetch_data($con, $sql); 

    return $result_data;
}

// Устанавливает победителей у лотов.
// $lot_winner - массив, где каждый элемент пара: id лота - id победителя
function set_lots_winners($con, $lot_winner_arr)
{
    $updated_id_winners = [];
    $errors = [];

    // Подготавливаем запрос.
    $stmt = $con->prepare("UPDATE `lot` SET winner_id = ? WHERE id = ?");
    if (!$stmt) {
        $errors[] = 'Не удалось подготовить запрос обновления id победителя лота.';

        return ['result' => $updated_id_winners, 'errors' => $errors];
    }

    // Выполняем все
    foreach ($lot_winner_arr as $lot_winner) {
        $lot_id = $lot_winner['lot_id'];
        $winner_id = $lot_winner['winner_id'];

        $res = $stmt->bind_param('ii', $winner_id, $lot_id );

        if (!$res) {
            $errors[] = "Не удалось привязать параметры (lot_id = $lot_id, winner_id = $winner_id)";
            continue;
        } 

        $res = $stmt->execute();

        if ($res) {
            $updated_id_winners[] = $winner_id;
        } else {
            $errors[] = "Не удалось выполнить обновление (lot_id = $lot_id, winner_id = $winner_id)";
        }      
    }

    $stmt->close();

    return ['result' => $updated_id_winners, 'errors' => $errors];
}

// Возвращает данные победителей (имя и email)
function get_winners_info($con, $winner_id_arr)
{
    $placeholders = create_placeholders_for_prepared_query(count($winner_id_arr));

    // Тут подошла бы filter
    // точнее ее нужно изменить, чтобы была возможность доставать не только все поля, но и определенные. 
    $sql = "SELECT u.name as 'user_name', u.email as 'user_email', l.name as 'lot_name', l.id as 'lot_id' 
            FROM `user` u
            LEFT JOIN `lot` l on l.winner_id = u.id
            WHERE u.id in (" . $placeholders . ")";

    $result_data = db_fetch_data($con, $sql, $winner_id_arr); 

    return $result_data;
}

// Кол-во лотов всего в определенной категории
function get_lots_count_by_category($con, $category_id)
{
    // ToDo
    // Если join не влияет на кол-в записей, то убрать
    $sql = 'SELECT COUNT(*) as count_lots FROM 
            (SELECT l.id
            FROM `lot` as l 
            LEFT JOIN `stuff_category` as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id
            WHERE cat.id = ? AND
                  l.end_date > NOW() AND
                  l.winner_id IS NULL
            GROUP BY l.id
            ORDER BY l.creation_date) as help';

    $result_data = db_fetch_data($con, $sql, [$category_id]); 

    return ['result' => $result_data['result'][0]['count_lots'], 'error' => $result_data['error']];
}

// Возвращает лоты определеной категории
function get_lots_by_category($con, $category_id, $limit, $offset)
{
    $params = [$category_id, $limit, $offset];

    // ToDo
    // практически тот же запрос, что используется для search.php
    $sql =  'SELECT l.*, 
                    cat.name category, 
                    IFNULL(max(b.price), l.start_price) current_price,
                    COUNT(b.id) as bets_count
            FROM `lot` as l 
            LEFT JOIN `stuff_category` as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id
            WHERE cat.id = ? AND
                  l.end_date > NOW() AND
                  l.winner_id IS NULL
            GROUP BY l.id
            ORDER BY l.creation_date
            LIMIT ?
            OFFSET ?';

    $result_data = db_fetch_data($con, $sql, $params);

    return $result_data;
}

// Кол-во лотов всего в определенной категории
// function get_lots_count_by_category($con, $category_name)
// {
//     $sql = 'SELECT COUNT(*) as count_lots FROM 
//             (SELECT l.id
//             FROM `lot` as l 
//             LEFT JOIN `stuff_category` as cat on l.category_id = cat.id
//             LEFT JOIN bet as b on l.id = b.lot_id
//             WHERE cat.name = ? AND
//                 l.end_date > NOW() AND
//                 l.winner_id IS NULL
//             GROUP BY l.id
//             ORDER BY l.creation_date) as help';

//     $result_data = db_fetch_data($con, $sql, [$category_name]); 

//     return ['result' => $result_data['result'][0]['count_lots'], 'error' => $result_data['error']];
// }

// // Возвращает лоты определеной категории
// function get_lots_by_category($con, $category_name, $limit, $offset)
// {
//     $params = [$category_name, $limit, $offset];

//     // ToDo
//     // практически тот же запрос, что используется для search.php
//     $sql =  'SELECT l.*, 
//                     cat.name category, 
//                     IFNULL(max(b.price), l.start_price) current_price,
//                     COUNT(b.id) as bets_count
//             FROM `lot` as l 
//             LEFT JOIN `stuff_category` as cat on l.category_id = cat.id
//             LEFT JOIN bet as b on l.id = b.lot_id
//             WHERE cat.name = ? AND
//                   l.end_date > NOW() AND
//                   l.winner_id IS NULL
//             GROUP BY l.id
//             ORDER BY l.creation_date
//             LIMIT ?
//             OFFSET ?';

//     $result_data = db_fetch_data($con, $sql, $params);

//     return $result_data;
// }

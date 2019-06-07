<?php

namespace yeticave\db\functions;

// ToDo
// Проверить все функции работы с БД на предмет поведения при ошибке (нет соединения, неверный sql запрос)
// ToDo
// Разобраться и определится буду ли запоминать ошибки при работе с БД и пробрасывать их наверх
// или просто буду die()

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
function get_lots($con, $id_list = [], $show_active = true, $limit = null)
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

    $limit_part = '';
    if (!empty($limit)) {
        $limit_part = 'LIMIT ' . $limit;
    }

    $sql = 'SELECT l.*,
                   cat.name category, 
                   IFNULL(max(b.price), l.start_price) current_price
            FROM lot as l
            JOIN stuff_category as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id ' .
            $sql_where_part .
            ' GROUP BY l.id
            ORDER BY l.creation_date DESC '
            . $limit_part;

    $result_data = db_fetch_data($con, $sql, $id_list);

    return $result_data;
}

// Возвращает ставки пользователя. 
function get_bets($con, $user_id) 
{
    $sql = 'SELECT b.*, l.name, l.winner_id, l.image_url, l.end_date, cat.name as category_name, u.contacts 
            FROM `bet` as b 
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
    $sql = 'SELECT b.*, u.name 
            FROM `bet` as b 
            JOIN `user` as u on b.user_id = u.id
            WHERE b.lot_id = ?
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
    $sql = 'SELECT id FROM stuff_category WHERE name = ? LIMIT 1';
    $result_data = db_fetch_data($con, $sql, [ $category_name ]);

    $result = null;

    if (is_null($result_data['error'])) {
        $result = count($result_data['result']) > 0 ? $result_data['result'][0]['id'] : null;
    }

    return $result;
}

// Возвращает id пользователя сделавшего последнюю ставку.
function get_last_bet_user_id($con, $lot_id)
{
    $sql = 'SELECT b.user_id
            FROM `bet` as b
            JOIN `lot` as l on b.lot_id = l.id
            WHERE l.id = ?
            ORDER BY b.create_date DESC
            LIMIT 1';
    
    $result_data = db_fetch_data($con, $sql, [ $lot_id ]);

    $user_id = null;
    if (!empty($result_data['result'])) {
        $user_id = $result_data['result'][0]['user_id'];
    }

    return [ 'error' => $result_data['error'], 'result' => $user_id ];
}

// Получаю список лотов с помощью полнотекстового поиска по названию и описанию лота и их кол-во всего.
function get_lots_by_fulltext_search($con, $search_query, $limit, $offset)
{
    $params = [$search_query, $limit, $offset];

    $sql =  'SELECT SQL_CALC_FOUND_ROWS l.id, l.name, l.image_url, l.end_date, l.start_price, 
                    cat.name category, 
                    IFNULL(max(b.price), l.start_price) current_price,
                    COUNT(b.id) as bets_count
            FROM `lot` as l 
            JOIN `stuff_category` as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id
            WHERE MATCH(l.name, l.description) AGAINST(?) AND
                  l.end_date > NOW() AND
                  l.winner_id IS NULL
            GROUP BY l.id
            ORDER BY l.creation_date
            LIMIT ?
            OFFSET ?';

    $result_data = db_fetch_data($con, $sql, $params);
    $lots = $result_data['result'];

    $sql = 'SELECT FOUND_ROWS() as rows_count';

    $result_data = db_fetch_data($con, $sql);
    $total_lots_count = $result_data['result'][0]['rows_count'];

    // плохо наверно, что функция выполняет сразу два действия, но в данном случае думаю это приемлимо
    return ['lots' => $lots, 'total_count' => $total_lots_count];
}

// Возвращает лоты определеной категории и их кол-во всего.
function get_lots_by_category($con, $category_id, $limit, $offset)
{
    $params = [$category_id, $limit, $offset];

    $sql =  'SELECT SQL_CALC_FOUND_ROWS l.id, l.name, l.image_url, l.end_date, l.start_price, 
                    cat.name category, 
                    IFNULL(max(b.price), l.start_price) current_price,
                    COUNT(b.id) as bets_count
            FROM `lot` as l 
            JOIN `stuff_category` as cat on l.category_id = cat.id
            LEFT JOIN bet as b on l.id = b.lot_id
            WHERE cat.id = ? AND
                  l.end_date > NOW() AND
                  l.winner_id IS NULL
            GROUP BY l.id
            ORDER BY l.creation_date
            LIMIT ?
            OFFSET ?';

    $result_data = db_fetch_data($con, $sql, $params);
    $lots = $result_data['result'];

    $sql = 'SELECT FOUND_ROWS() as rows_count';

    $result_data = db_fetch_data($con, $sql);
    $total_lots_count = $result_data['result'][0]['rows_count'];

    return ['lots' => $lots, 'total_count' => $total_lots_count];
}

// Возвращает лоты без победителей на данный момент и пользователя чья ставка была последней.
function get_lots_without_winners($con)
{
    $sql = 'SELECT l.id as lot_id, 
                   l.name as lot_name, 
                   m.user_id as winner_id, 
                   m.max_price as price
            FROM `lot` l
            JOIN max_bet_by_lot as m ON m.lot_id = l.id
            WHERE l.end_date <= NOW()
            AND l.winner_id IS NULL';

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
            JOIN `lot` l on l.winner_id = u.id
            WHERE u.id in (" . $placeholders . ")";

    $result_data = db_fetch_data($con, $sql, $winner_id_arr); 

    return $result_data;
}

function get_userdata_by_email($con, $email) 
{
    $user_data = get_data_by_field($con, 'user', 'email', $email, 1);

    $result = !empty($user_data['result']) ? $user_data['result'][0] : null;

    return ['error' => $user_data['error'], 'result' => $result];
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
    $keys_order = ['author_id', 'name', 'category_id', 'description', 'start_price', 'step_bet', 'end_date', 'image_url'];
    $ordered_params = array_order_by_key($params,  $keys_order);

    $result_data = db_fetch_data($con, $sql, $ordered_params);

    $insert_id = mysqli_insert_id($con);
    $added_lot_id = $insert_id === 0 ? null : $insert_id;

    return $added_lot_id;
}

// Добавляет ставку на лот.
function add_bet($con, $user_id, $lot_id, $bet_cost) 
{
    $params = [ $user_id, $lot_id, $bet_cost ];

    $sql = 'INSERT INTO bet (user_id, lot_id, price) 
            VALUES (?, ?, ?)';

    $result_data = db_fetch_data($con, $sql, $params);

    $insert_id = mysqli_insert_id($con);
    $added_bet_id = $insert_id === 0 ? null : $insert_id;

    return $added_bet_id;
}


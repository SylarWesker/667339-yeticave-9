<?php

namespace yeticave\db\functions;

require_once('utils/db_mysqli.php');

/**
 * get_stuff_categories - Возвращает список категорий лотов.
 *
 * @param mixed $con - подключение к БД.
 *
 * @return array
 */
function get_stuff_categories($con)
{
    $sql = 'SELECT * FROM stuff_category';
    $result_data = db_fetch_data($con, $sql);

    return $result_data;
}

/**
 * get_lots - Возвращает список лотов.
 *
 * @param mixed $con - подключение к БД.
 * @param array $id_list - список id лотов. Если ни одного не передано, то возвращаем все лоты.
 * @param bool $show_active - показывать активные лоты? (активные это у которых не истекла дата окончания и пустой победитель).
 * @param int|null $limit - лимит кол-ва лотов (если null - без ограничений).
 *
 * @return array
 */
function get_lots($con, $id_list = [], bool $show_active = true, $limit = null)
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

    $parts = [$sql_where_id_part, $sql_active_lots_where_part];
    $parts = array_filter($parts, function ($p) {
        if (!empty($p)) {
            return $p;
        }
    });
    $sql_where_part = implode(' AND ', $parts);

    if (!empty($sql_where_part)) {
        $sql_where_part = ' WHERE ' . $sql_where_part . ' ';
    }

    $limit_part = '';
    if (!empty($limit)) {
        $limit_part = ' LIMIT ' . $limit;
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

/**
 * get_bets - Возвращает ставки пользователя.
 *
 * @param mixed $con - подключение к БД.
 * @param int $user_id - id пользователя.
 *
 * @return array
 */
function get_bets($con, int $user_id)
{
    $sql = 'SELECT b.*, l.name, l.winner_id, l.image_url, l.end_date, cat.name as category_name, u.contacts 
            FROM `bet` as b 
            JOIN `lot` as l on b.lot_id = l.id
            JOIN `stuff_category` as cat on l.category_id = cat.id
            JOIN `user` as u on l.author_id = u.id
            WHERE b.user_id = ?
            ORDER BY b.create_date DESC';

    $result_data = db_fetch_data($con, $sql, [$user_id]);

    return $result_data;
}

/**
 * get_bets_history - Возвращает историю ставок по лоту.
 *
 * @param mixed $con - подключение к БД.
 * @param int $lot_id - id лота.
 *
 * @return array
 */
function get_bets_history($con, $lot_id)
{
    $sql = 'SELECT b.*, u.name 
            FROM `bet` as b 
            JOIN `user` as u on b.user_id = u.id
            WHERE b.lot_id = ?
            ORDER BY b.create_date DESC';

    $result_data = db_fetch_data($con, $sql, [$lot_id]);

    return $result_data;
}

/**
 * get_lot_min_bet - Возвращает минимально возможную ставку для лота по его id.
 *
 * @param mixed $con - подключение к БД.
 * @param int $lot_id - id лота.
 *
 * @return int
 */
function get_lot_min_bet($con, int $lot_id): int
{
    $sql = 'SELECT l.id, b.lot_id, IF(ISNULL(b.price), l.start_price, MAX(b.price) + l.step_bet) as min_bet 
            FROM `bet` as b
            RIGHT JOIN `lot` as l
            ON l.id = b.lot_id
            GROUP BY l.id
            HAVING l.id = ?';

    $result_data = db_fetch_data($con, $sql, [$lot_id]);

    $result = null;

    if ($result_data['error'] !== null) {
        $result = null;
    } else {
        $result = count($result_data['result']) > 0 ? $result_data['result'][0]['min_bet'] : null;
    }

    return $result;
}

/**
 * get_category_id - Возвращает id категории по ее названию.
 *
 * @param mixed $con - подключение к БД.
 * @param mixed $category_name - название категории.
 *
 * @return array
 */
function get_category_id($con, $category_name)
{
    $sql = 'SELECT id FROM stuff_category WHERE name = ? LIMIT 1';
    $result_data = db_fetch_data($con, $sql, [$category_name]);

    $result = null;

    if (is_null($result_data['error'])) {
        $result = count($result_data['result']) > 0 ? $result_data['result'][0]['id'] : null;
    }

    return $result;
}

/**
 * get_last_bet_user_id - Возвращает id пользователя сделавшего последнюю ставку.
 *
 * @param mixed $con
 * @param int $lot_id - id лот.
 *
 * @return array $arr
 * $arr['result'] - id пользователя.
 * $arr['error'] - текст ошибки.
 */
function get_last_bet_user_id($con, int $lot_id)
{
    $sql = 'SELECT b.user_id
            FROM `bet` as b
            JOIN `lot` as l on b.lot_id = l.id
            WHERE l.id = ?
            ORDER BY b.create_date DESC
            LIMIT 1';

    $result_data = db_fetch_data($con, $sql, [$lot_id]);

    $user_id = null;
    if (!empty($result_data['result'])) {
        $user_id = $result_data['result'][0]['user_id'];
    }

    return ['error' => $result_data['error'], 'result' => $user_id];
}

/**
 * get_lots_by_fulltext_search - Получаю список лотов с помощью полнотекстового поиска по названию и описанию лота и их кол-во всего.
 *
 * @param mixed $con
 * @param string $search_query - поисковый запрос пользователя.
 * @param int $limit - лимит записей.
 * @param int $offset - на сколько записей нужно сместить в результирующем наборе строк.
 *
 * @return array $arr
 * $arr['total_count'] - всего лотов (число записей, которое вернет запрос без указания лимита).
 * $arr['lots'] - массив лотов.
 */
function get_lots_by_fulltext_search($con, $search_query, $limit, $offset)
{
    $params = [$search_query, $limit, $offset];

    $sql = 'SELECT SQL_CALC_FOUND_ROWS l.id, l.name, l.image_url, l.end_date, l.start_price, 
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

/**
 * get_lots_by_category - Возвращает лоты определеной категории и их кол-во всего.
 *
 * @param mixed $con
 * @param int $category_id - id категории лота.
 * @param int $limit - лимит записей.
 * @param int $offset - на сколько записей нужно сместить в результирующем наборе строк.
 *
 * @return array $arr
 * $arr['total_count'] - всего лотов (число записей, которое вернет запрос без указания лимита).
 * $arr['lots'] - массив лотов.
 */
function get_lots_by_category($con, $category_id, $limit, $offset)
{
    $params = [$category_id, $limit, $offset];

    $sql = 'SELECT SQL_CALC_FOUND_ROWS l.id, l.name, l.image_url, l.end_date, l.start_price, 
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

/**
 * get_lots_without_winners -  Возвращает лоты без победителей на данный момент и пользователя чья ставка была последней.
 *
 * @param mixed $con
 *
 * @return array
 */
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

/**
 * set_lots_winners - Устанавливает победителей аукционов у лотов.
 *
 * @param mixed $con
 * @param mixed $lot_winner_arr - массив, где каждый элемент пара: id лота - id победителя
 *
 * @return array $arr
 * $arr['result'] - массив id обработанных лотов.
 * $arr['errors'] - массив с сообщениями об ошибках.
 */
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

        $res = $stmt->bind_param('ii', $winner_id, $lot_id);

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

/**
 * get_winners_info - Возвращает данные победителей (имя и email)
 *
 * @param mixed $con
 * @param array $winner_id_arr - массив id победивших пользователей.
 *
 * @return array
 */
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

/**
 * get_userdata_by_email - получение данных пользователя по его электронной почте.
 *
 * @param mixed $con
 * @param string $email - электронная почта пользователя.
 *
 * @return array
 */
function get_userdata_by_email($con, string $email)
{
    $user_data = get_data_by_field($con, 'user', 'email', $email, 1);

    $result = !empty($user_data['result']) ? $user_data['result'][0] : null;

    return ['error' => $user_data['error'], 'result' => $result];
}

/**
 * has_email - Возвращает истину, если в БД есть пользователь с указанной почтой.
 *
 * @param mixed $con
 * @param string $email - электронная почта пользователя.
 *
 * @return bool
 */
function has_email($con, string $email): bool
{
    return filter($con, 'user', 'email', $email, 1);
}

/**
 * has_user - Возвращает истину, если в БД есть пользователь с указанным именем.
 *
 * @param mixed $con
 * @param string $user_name - имя пользователя.
 *
 * @return bool
 */
function has_user($con, string $user_name): bool
{
    return filter($con, 'user', 'name', $user_name, 1);
}

/**
 * has_lot - Возвращает истину, если в БД есть лот с указанным id.
 *
 * @param mixed $con
 * @param int $lot_id - id лота
 *
 * @return bool
 */
function has_lot($con, int $lot_id): bool
{
    return filter($con, 'lot', 'id', $lot_id);
}

/**
 * add_user - Добавляет пользователя в БД.
 *
 * @param mixed $con
 * @param string $email - электронная почта.
 * @param string $user_name - имя пользователя.
 * @param string $password - хэш от пароля пользователя.
 * @param string $contacts - контактные данные.
 *
 * @return int|null $inserted_user_id - id добавленного пользователя (null - если не удалось добавить).
 */
function add_user($con, string $email, string $user_name, string $password, string $contacts): int
{
    $params = [$email, $user_name, $password, $contacts];

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

/**
 * add_lot - Добавляет лот.
 *
 * @param mixed $con
 * @param array $params - параметры лота.
 *
 * @return int|null $added_lot_id - id лота в случае успеха, NULL - если нет.
 */
function add_lot($con, $params): int
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
    $keys_order = [
        'author_id',
        'name',
        'category_id',
        'description',
        'start_price',
        'step_bet',
        'end_date',
        'image_url'
    ];
    $ordered_params = array_order_by_key($params, $keys_order);

    $result_data = db_fetch_data($con, $sql, $ordered_params);

    $insert_id = mysqli_insert_id($con);
    $added_lot_id = $insert_id === 0 ? null : $insert_id;

    return $added_lot_id;
}

/**
 * add_bet - Добавляет ставку на лот.
 *
 * @param mixed $con
 * @param int $user_id - id пользователя, сделавшего ставку.
 * @param int $lot_id - id лота на которого сделали ставку.
 * @param int $bet_cost - размер ставки.
 *
 * @return int|null $added_bet_id - id добавленной ставки (null - если не удалось сделать ставку).
 */
function add_bet($con, int $user_id, int $lot_id, int $bet_cost): int
{
    $params = [$user_id, $lot_id, $bet_cost];

    $sql = 'INSERT INTO bet (user_id, lot_id, price) 
            VALUES (?, ?, ?)';

    $result_data = db_fetch_data($con, $sql, $params);

    $insert_id = mysqli_insert_id($con);
    $added_bet_id = $insert_id === 0 ? null : $insert_id;

    return $added_bet_id;
}


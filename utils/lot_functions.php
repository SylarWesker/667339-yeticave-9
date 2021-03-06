<?php 

require_once('helpers.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// Функции для работы сценария lot.php

// 
/**
 * is_allow_add_bet - проверяет можно ли делать ставку на этот лот текущему пользователю.
 *
 * @param  bool $is_auth - авторизован ли пользователь?
 * @param  int $user_id - id пользователя.
 * @param  int $lot_author_id - id пользователя разместившего лот.
 * @param  string $lot_end_date - дата окончания аукциона по лоту.
 * @param  int|null $last_bet_user_id - id последнего сделавшего на этот лот ставку.
 *
 * @return bool
 */
function is_allow_add_bet(bool $is_auth, int $user_id, int $lot_author_id, string $lot_end_date, $last_bet_user_id) : bool
{
    // Вырезка из ТЗ
    // Ограничения
    //
    // Блок добавления ставки не показывается если:
    //  - пользователь не авторизован;
    //  - срок размещения лота истёк;
    //  - лот создан текущим пользователем;
    //  - последняя ставка сделана текущим пользователем.

    $now = new DateTime();
    $lot_end_date = new DateTime($lot_end_date);

    $last_bet_set_by_current_user = false;
    if (!is_null($last_bet_user_id)) {
        $last_bet_set_by_current_user = $last_bet_user_id === $user_id;
    }

    $allow_add_bet = $is_auth && 
                     $now <= $lot_end_date &&
                     $lot_author_id !== $user_id &&
                     !$last_bet_set_by_current_user;

    return $allow_add_bet;
}

/**
 * get_lot_min_price - расчитывает размер минимальной ставки на лот.
 *
 * @param  int $start_price
 * @param  int $current_price
 * @param  int $bet_step
 *
 * @return int
 */
function get_lot_min_price(int $start_price, int $current_price, int $bet_step) : int
{
    $lot_min_price = $current_price;

    if ($current_price !== $start_price) {
        $lot_min_price += $bet_step;
    }

    return $lot_min_price;
}

/**
 * get_lot_page_content - Возвращает контент страницы лота.
 *
 * @param  array $lot - ассоциативный массив с данными о лоте.
 * @param  mixed $con - соединение с БД.
 * @param  int $user_id - id текущего пользователя.
 * @param  bool $is_auth - авторизован ли пользователь.
 * @param  array $stuff_categories - массив категорий лотов на сайте.
 * @param  array $add_bet_errors - маccив с ошибками произошедших на форме добавления ставки.
 *
 * @return mixed
 */
function get_lot_page_content($lot, $con, int $user_id, bool $is_auth, $stuff_categories, $add_bet_errors)
{
    $lot_id = $lot['id'];
 
    // Расчет минимальной ставки на лот.
    $lot_min_price = get_lot_min_price($lot['start_price'], 
                                       $lot['current_price'], 
                                       $lot['step_bet']);

    // Получаю историю ставок.
    $func_result = db_func\get_bets_history($con, $lot_id);
    $bets_history = $func_result['result'] ?? [];

    if (!empty($func_result['error'])) {
        $errors['fatal'][] = 'Ошибка MySql при получении истории ставок лота: ' . $func_result['error'];  
    }

    // Последняя ставка была сделана текущим пользователем?
    // $bets_history[0] - мог бы использовать в качестве последней ставки т.к сортирую их по дате (сначала новые).
    // но решил использовать отдельный запрос (не очень оптимально, но логичнее)
    $func_result = db_func\get_last_bet_user_id($con, $lot_id);
    $last_bet_user_id = $func_result['result'];

    // Можно ли делать ставку на этот лот текущему пользователю.
    $allow_add_bet = is_allow_add_bet($is_auth, $user_id, $lot['author_id'], $lot['end_date'], $last_bet_user_id);

    $content = include_template('lot.php', [
                                            'stuff_categories'  => $stuff_categories,
                                            'lot'               => $lot,
                                            'lot_min_price'     => $lot_min_price,
                                            'is_auth'           => $is_auth,
                                            'user_id'           => $user_id,
                                            'allow_add_bet'     => $allow_add_bet,
                                            'bets_history'      => $bets_history,
                                            'add_bet_errors'    => $add_bet_errors
    ]);

    return $content;
}

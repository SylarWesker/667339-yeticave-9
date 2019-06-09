<?php 

require_once('helpers.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// Функции для работы сценария lot.php

// Проверяет можно ли делать ставку на этот лот текущему пользователю.
function is_allow_add_bet($is_auth, $user_id, $lot_author_id, $lot_end_date, $last_bet_user_id)
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

// Расчет минимальной ставки на лот.
function get_lot_min_price($start_price, $current_price, $bet_step)
{
    $lot_min_price = $current_price;

    if ($current_price !== $start_price) {
        $lot_min_price += $bet_step;
    }

    return $lot_min_price;
}

// Возвращает контент страницы лота.
function get_lot_page_content($lot, $con, $user_id, $is_auth, $stuff_categories, $add_bet_errors)
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

<?php

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$errors = [];
$id = null; 
$lot = null;

// Получение списка категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    $errors[] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
}

if (isset($_GET['id'])) {
    if (is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);

        // Берем лот по id.
        $func_result = db_func\get_lots($con, [ $id ]);
        if (!empty($func_result['result'])) {
            $lot = $func_result['result'][0];
        }

        if ($func_result['error'] !== null) {
            $errors[] = $func_result['error'];
        } else {
            if ($lot === null) {
                $errors[] = 'Не найден лот с id = ' . $id;
            } 
        }
    } else {
        $errors[] = 'Параметр id неверного формата. Должно быть целое число.';
    }
}

$content = null;
$title_page = 'Страница показа лота.';

if (count($errors) != 0) {
    $title_page = 'Страница не найдена.';
    $content = include_template('404.php', ['error_list' =>  $errors]);
} else {
    $title_page = $lot['name'];

    // Расчет минимальной ставки на лот.
    $lot_min_price = get_lot_min_price($lot['start_price'], 
                                       $lot['current_price'], 
                                       $lot['step_bet']);

    // Получаю историю ставок.
    // список категорий.
    $func_result = db_func\get_bets_history($con, $id);
    $bets_history = $func_result['result'] ?? [];

    // ToDo
    // и снова возвращаемся к теме обработки ошибок. как правильно?
    if ($func_result['error'] !== null) {
        print('Ошибка MySql при получении истории ставок лота: ' . $func_result['error']);  
    }

    // Ограничения
    //
    // Блок добавления ставки не показывается если:
    //  пользователь не авторизован;
    //  срок размещения лота истёк;
    //  лот создан текущим пользователем;
    //  последняя ставка сделана текущим пользователем.
    $now = new DateTime();
    $lot_end_date = new DateTime($lot['end_date']);

    // Последняя ставка была сделана текущим пользователем?
    // $bets_history[0] - использую в качестве последней ставки т.к сортирую их по дате (сначала новые).
    // в целом это слабое место. надежнее сделать отдельный запрос с логикой получения последней ставки.
    $last_bet_set_by_current_user = false;
    if (count($bets_history) !== 0) {
        $last_bet_set_by_current_user = $bets_history[0]['user_id'] === $user_id;
    } 

    $allow_add_bet = is_auth() && 
                     $now <= $lot_end_date &&
                     $lot['author_id'] !== $user_id &&
                     !$last_bet_set_by_current_user;
    
    $add_bet_content = NULL;

    if ($allow_add_bet) {
        $add_bet_content = include_template('add-bet.php', ['lot' => $lot,
                                                            'lot_min_price' => $lot_min_price
                                                           ]);
    }

    $content = include_template('lot.php', ['stuff_categories' => $stuff_categories,
                                            'lot' => $lot,
                                            'lot_min_price' => $lot_min_price,
                                            'is_auth' => is_auth(),
                                            'add_bet_content' => $add_bet_content,
                                            'bets_history' => $bets_history
                                            ]);
}

$con = null;

$layout = include_template('layout.php', [ 'title' => $title_page,
                                           'content' => $content, 
                                           'stuff_categories' => $stuff_categories, 
                                           'is_auth' => is_auth(), 
                                           'user_name' => $user_name
                                         ]);

print($layout);

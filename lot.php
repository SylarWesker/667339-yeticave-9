<?php

require_once('auth.php');
require_once('helpers.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$errors = [];
$id = null; 
$lot = null;

// категории заново получаем? ну вроде да...
$stuff_categories = [];

 // Получение списка категорий.
 $func_result = db_func\get_stuff_categories($con);
 $stuff_categories = $func_result['result'] ?? [];

 if ($func_result['error'] !== null) {
     $errors[] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
 }

if (isset($_GET['id'])) {
    // проверяю является ли числом (ну или просто сразу пытаюсь привести к числу)
    if (is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);

        // Берем лот по id.
        $func_result = db_func\get_lots($con, [ $id ]);
        if (!($func_result['result'] === null || count($func_result['result']) === 0)) {
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
} else {
    $errors[] = 'Не передан параметр id!';
}

$con = null;

$content = null;
$title_page = 'Страница показа лота.';

if (count($errors) != 0) {
    $title_page = 'Страница не найдена.';
    $content = include_template('404.php', ['error_list' =>  $errors]);
} else {
    // ToDo 
    // есть функция получения минимальной ставки из БД. 
    // дублирующий функционал получается. что буду делать?

    // Расчет минимальной ставки.
    $lot_min_price = $lot['current_price'];

    if ($lot['current_price'] !== $lot['start_price']) {
        $lot_min_price += $lot['step_bet'];
    }

    $add_bet_content = include_template('add-bet.php', ['lot' => $lot,
                                                        'lot_min_price' => $lot_min_price
                                                       ]);

    $title_page = $lot['name'];
    $content = include_template('lot.php', ['stuff_categories' => $stuff_categories,
                                            'lot' => $lot,
                                            'is_auth' => $is_auth,
                                            'add_bet_content' => $add_bet_content
                                            ]);
}

$layout = include_template('layout.php', [ 'title' => $title_page,
                                           'content' => $content, 
                                           'stuff_categories' => $stuff_categories, 
                                           'is_auth' => $is_auth, 
                                           'user_name' => $user_name
                                         ]);

print($layout);

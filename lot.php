<?php

require_once('utils/error_report.php');

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

require_once('utils/lot_functions.php');

use yeticave\db\functions as db_func;

$errors_lot = ['validation' => [], 'fatal' => []];
$errors_add_bet = ['validation' => [], 'fatal' => []];

// Получение списка категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if (!is_null($func_result['error'])) {
    $errors_lot['fatal'][] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];
}

$title_page = 'Страница показа лота.';
$lot_id = null;
$cost = null;
$lot = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Значит сделали ставку.
    $form_fields = [
        'cost' => ['error_messages' => ['zero_length' => 'Не задана ставка на лот.']],
        'lot_id' => ['error_messages' => ['zero_length' => 'Id лота не задан.']]
    ];

    // Сбор данных с формы.
    $form_data = get_form_data(array_keys($form_fields));

    // Валидация.                
    $validation_result = validate_form_data($form_data, $form_fields);
    $validated_data = $validation_result['data'];
    $errors_add_bet['validation'] = $validation_result['errors'];

    $cost = $validated_data['cost'];

    if (is_numeric($cost)) {
        $cost = intval($cost);
    } else {
        $errors_add_bet['validation']['cost'] = 'Ставка должна быть числом.';
    }

    $lot_id = $validated_data['lot_id'];

    $has_lot = db_func\has_lot($con, $lot_id);
    if ($has_lot) {
        // Получаем минимальную ставку для лота. 
        $min_lot_bet = db_func\get_lot_min_bet($con, $lot_id);

        if ($cost < $min_lot_bet) {
            $errors_add_bet['validation']['cost'] = 'Указанная цена меньше минимально возможной ставки.';
        }
    } else {
        $errors_add_bet['validation']['lot_id'] = 'Нет лота с указанным Id';
    }

    if (empty($errors_add_bet['validation'])) {
        // Добавляем ставку. 
        $added_bet_id = db_func\add_bet($con, $user_id, $lot_id, $cost);

        if (!is_null($added_bet_id)) {
            $lot_url = 'lot.php?id=' . $lot_id;

            header('Location: ' . $lot_url);
        } else {
            $errors_add_bet['fatal'][] = 'Ставка не сделана.';
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') { // просто запросили страничку.
    // Валидация
    if (isset($_GET['id'])) {
        $lot_id = $_GET['id'];

        if (is_numeric($lot_id)) {
            $lot_id = intval($lot_id);
        } else {
            $errors_lot['validation']['id'] = 'Параметр id неверного формата. Должно быть целое число.';
        }
    }
}

if (!empty($errors_lot['validation'])) {
    $errors_list = array_merge($errors_lot['fatal'] ?? [], $errors_lot['validation'] ?? []);

    show_500($errors_list, $stuff_categories, $is_auth, $user_name);
    return;
}

// Берем лот по id.
$func_result = db_func\get_lots($con, [$lot_id], false);
if (!empty($func_result['result'])) {
    $lot = $func_result['result'][0];

    $title_page = $lot['name'];
}

if (!empty($func_result['error'])) {
    $errors_lot['fatal'][] = $func_result['error'];
} else {
    if (is_null($lot)) {
        $errors_lot['fatal'][] = 'Не найден лот с id = ' . $lot_id;

        show_404($errors_lot['fatal'], $stuff_categories, $is_auth, $user_name);
        return;
    }
}

$content = get_lot_page_content($lot, $con, $user_id, $is_auth, $stuff_categories, $errors_add_bet['validation']);

$layout = include_template('layout.php', [
    'title' => $title_page,
    'content' => $content,
    'stuff_categories' => $stuff_categories,
    'is_auth' => $is_auth,
    'user_name' => $user_name
]);

print($layout);

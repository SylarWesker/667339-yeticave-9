<?php

require_once('auth.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$errors = ['validation' => [], 'fatal' => ''];
$cost = NULL;
$lot_id = NULL;

if (isset($_POST['submit'])) {

    $form_fields = [ 
                     'cost' => ['error_messages' => ['zero_length' => 'Не задана ставка на лот.']], 
                     'lot_id' => ['error_messages' => ['zero_length' => 'Id лота не задан.']]
                    ];

    // Сбор данных с формы.
    $form_data = get_form_data(array_keys($form_fields));

    // Валидация.                
    $validated_data = validate_form_data($form_data, $form_fields);
    $errors['validation'] = $validation_result['errors'];

    if (is_numeric($validated_data['cost'])) {
        $cost = intval($validated_data['cost']);
    } else {
        $errors['validation']['cost'] = 'Ставка должна быть числом.';
    }
       
    // ToDo
    // этот блок кода нет смысла выполнять если не указана цена
    // т.е получается снова нужно оборачивать код в if
    $has_lot = db_func\has_lot($con, $validated_data['lot_id']);
    if ($has_lot) {
        // Получаем минимальную ставку для лота. 
        $min_lot_bet = db_func\get_lot_min_bet($con, $lot_id);

        if ($cost < $min_lot_bet) {
            $errors['valiation']['cost'] = 'Указанная цена меньше минимально возможной ставки.';
        }
    } else {
        $errors['validation']['lot_id'] = 'Нет лота с указанным Id';
    }
}

// if ($errors) {
//     showLot();
//     exit;
// }

if (empty($errors['validation'])) {
    // Добавляем ставку. 
    $added_bet_id = db_func\add_bet($con, $user_id, $lot_id, $cost);

    if ($added_bet_id !== NULL) {
        $lot_url = 'lot.php?id=' . $lot_id;

        header('Location: ' . $lot_url);  
    } else {
        $errors['fatal'][] = 'Ставка не сделана.';
    }
}

$con = null;

// ToDo
// Как передать ошибки отсюда на форму лота или на эту же форму (add-bet.php) ???

// 1. сформировать шаблон лота и туда передать ошибки.
// 2. т.к код формирования лота будет повторяться, то вынести в отдельную функцию.

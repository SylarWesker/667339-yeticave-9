<?php

require_once('auth.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// Валидация.
$errors = [];
$cost = NULL;
$lot_id = NULL;

if (isset($_POST['submit'])) {
    if (isset($_POST['cost'])) {
        $cost = $_POST['cost'];
        $cost = secure_data_for_sql_query($cost);

        $cost = intval($cost);
    }

    if (isset($_POST['lot_id'])) {
        $lot_id = $_POST['lot_id'];
        $lot_id = secure_data_for_sql_query($lot_id);
    } else {
        $errors['lot_id'] = 'Не указан индетификатор лота.';
    }
}

if (count($errors) === 0) {
    // Минимальная ставка уже расчитывается на lot.php 
    // но... пока страница открыта ситуация может изменится (пользователь может открыть страницу и сделать ставку намного позже => данные могут устареть)

    // Получаем минимальную ставку для лота. 
    $min_lot_bet = db_func\get_lot_min_bet($con, $lot_id);

    if ($min_lot_bet === NULL) {
        $errors['lot_id'] = 'Нет лота с указанным id';

        // ToDo
        // ??? нужно перейти к блоку кода который редиректит на страницу с лотом и еще ошибки передать.
        return; 
    }

    // ToDo
    // Запретить делать ставки на свои лоты? по идее нужно.

    if ($cost >= $min_lot_bet) {
        // Добавляем ставку. 
        $added_bet_id = db_func\add_bet($con, $user_id, $lot_id, $cost);

        if ($added_bet_id !== NULL) {
            $lot_url = 'lot.php?id=' . $lot_id;

            header('Location: ' . $lot_url);  
        } else {
            // ToDo
            // Что делать будем?
            $errors['lot_id'] = 'Ставка не сделана.';
        }
    } else {
        $errors['cost'] = 'Указанная цена меньше минимально возможной ставки.';
    }
}

$con = null;

// ToDo
// Как передать ошибки отсюда на форму лота или на эту же форму (add-bet.php) ???

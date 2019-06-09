<?php

require_once('utils/utils.php');
require_once('helpers.php');

// Функции для сценария добавления лота (add.php)

// Валидация даты окончания торгов
function validate_lot_end_date($end_date)
{
    $error_msg = '';

    if (!is_date_valid($end_date)) {
        $error_msg = 'Дата должна быть в формате ГГГГ-ММ-ДД.';
    }

    // Проверка того что дата больше текущей хотя бы на один день.
    $date_now = new DateTime();
    $lot_end_date = new DateTime($end_date);
    $date_diff = $date_now->diff($lot_end_date);

    if (!at_least_one_day_bigger($date_diff)) {
        $error_msg = 'Дата завершения торгов должна быть больше текущей даты хотя на 1 день!';
    }

    $is_valid = $error_msg === '';

    return ['is_valid' => $is_valid, 'error' => $error_msg];
}

// Валидация цены
function validate_price($price_value, $belong_zero_msg, $now_number_msg)
{
    $error_msg = '';

    if (is_numeric($price_value)) {
        $price_value = intval($price_value);

        if ($price_value <= 0) {
            $error_msg = $belong_zero_msg;
        }
    } else {
        $error_msg = $now_number_msg;
    }

    $is_valid = $error_msg === '';

    return ['is_valid' => $is_valid, 'error' => $error_msg];
}

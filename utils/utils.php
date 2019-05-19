<?php

require_once('helpers.php');

// Удаляет тэги в массиве (если элемент массива сам является массивом, то тоже делает и в нем).
// Возвращает измененную копию.
function strip_tags_for_array($arr_data, $recursive = false)
{
    foreach($arr_data as $key => $value)
    {
        if (is_array($value)) {
            if ($recursive) {
                $arr_data[$key] = strip_tags_for_array($value, $recursive);
            }
        } elseif (is_string($value)) {
            $arr_data[$key] = strip_tags($value);
        }
    }

    return $arr_data;
}

// Вспомогательная функция. 
// Из $date2 вычитает $date1 и проверяет меньше ли часа между ними.
function is_equal_or_less_hour_beetween_dates($date2, $date1)
{
    if (is_string($date2)) {
        $date2 = new DateTime($date2);
    }

    if (is_string($date1)) {
        $date1 = new DateTime($date1);
    }

    $date_diff = $date1->diff($date2);

    return is_equal_or_less_hour($date_diff);
}

// Возвращает истину, если в интервале один час или меньше.
function is_equal_or_less_hour($date_interval) 
{
    if ($date_interval->invert === 1)
        return false;
    
    return  is_less_hour($date_interval) || is_equal_hour($date_interval);
}

function is_only_time_part_has($date_interval)
{
    $result = $date_interval->y === 0 && 
              $date_interval->m === 0 && 
              $date_interval->d === 0;

    return $result;
}

function is_less_hour($date_interval)
{
    $result = $date_interval->h === 0;
    $result = is_only_time_part_has($date_interval) && $result;

    return $result;
}

function is_equal_hour($date_interval)
{
    $result = $date_interval->h === 1 && 
              $date_interval->i === 0 && 
              $date_interval->s === 0;
    $result = is_only_time_part_has($date_interval) && $result;

    return $result;
}

// Функция форматирования суммы заказа.
function format_price($number, $currency_symbol = '₽')
{
    $number = ceil($number);

    if ($number >= 1000) {
        $number = number_format($number, 0, '.', ' ');
    }

    $result = $number . ' ' . $currency_symbol;
    return $result;
}

function secure_data_for_sql_query($param)
{
    // Еще можно такой вариант попробовать.
    // $functions = ['trim', 'strip_tags', 'addslashes'];
    // foreach($functions as $func) {
    //     $param = call_user_func($func, $param);
    // }

    if (is_string($param)) {
        $param = trim($param);
        $param = strip_tags($param);

        $param = addslashes($param); 
    }

    return $param;
}

// Если разница между датами больше хотя бы на 1 день, то возвращает true.
function at_least_one_day_bigger($date_interval) {
    if ($date_interval->invert === 1)
        return false;
    
    return $date_interval->y >= 1 || $date_interval->m >= 1 || $date_interval->d >= 1;
}

// Меняет порядок элементов в массиве $array согласно порядку ключей в $ordered_keys.
function array_order_by_key($array, $ordered_keys) {
    $result = [];

    for ($i = 0; $i < count($array); $i++) 
    {
        $key = $ordered_keys[$i];

        if (array_key_exists($key, $array)) {
            $result[] = $array[$key];
        }
    }

    return $result;
}

// ToDo
// Как это можно реализовать по другому?
// Далеко не элегантное решение.
// ключи в $form_data и $errors должны совпадать.
// постоянно нужно передавать $form_data, $errors... По идее нужно класс сделать.
function show_form_data($key, $form_data, $errors) 
{
  $result = '';

  if(!isset($errors[$key]) && isset($form_data[$key])) {
    $result = $form_data[$key];
  }
    
  return $result;
}

// ToDo
// Та же история. 
function show_error($key, $errors) 
{
  $result = '';

  if(isset($errors[$key])) {
    $result = $errors[$key];
  }

  return $result;
}

// Форматирует дату создания ставки в человекоудобном формате.
function bet_date_create_format($now, $date)
{
    $result = null;

    if (is_string($date)) {
        $date = new DateTime($date);
    }

    $now_midnight = new DateTime($now->format('Y-m-d'));
    $date_midnight = new DateTime($date->format('Y-m-d'));

    $date_diff_with_time = $date->diff($now);
    $date_diff = $date_midnight->diff($now_midnight); // при расчете 'вчера' нужно не использовать время. 

    // сегодня 03.01.2018
    // вчера это 02.01.2018 00:00:00 - 02.01.2018 23:59:59

    // если разница между датой создания ставки и сейчас больше одного дня, то выводим в формате '19.03.17 в 08:21'
    if ($date_diff->d > 1) {
        $result = $date->format('d.m.y') . ' в ' . $date->format('H:i');
    } elseif ($date_diff->d === 1) {
        $result = 'Вчера, в ' . $date->format('H:i');
    } else {
        $hours_ago = get_noun_plural_form_with_number($date_diff_with_time->h, 'час', 'часа', 'часов');
        $minutes_ago = get_noun_plural_form_with_number($date_diff_with_time->i, 'минута', 'минуты', 'минут');

        $result = $hours_ago . ' ' . $minutes_ago . ' назад';
    }

    return $result;
}

function get_noun_plural_form_with_number($number, $one, $two, $many)
{
    $result = '';

    if ($number !== 0) {
        $result = $number . ' ' . get_noun_plural_form($number, $one, $two, $many);
    }

    return $result;
} 

// Форматирует время до окончания торгов лота.
function time_to_lot_end_format($end_date, $now) 
{
    $result = null;

    if (is_string($end_date)) {
        $end_date = new DateTime($end_date);
    }

    $date_diff = $now->diff($end_date);

    // Месяц при разнице дат всегда равен 30 дням (при разнице DateTime с помощью функции diff).
    $hours_sum = $date_diff->h + $date_diff->d * 24 +  $date_diff->m * 30 * 24;
    $result = $hours_sum .':' . $date_diff->i;

    return $result;
}

// Возможно эта функция еще понадобится.
// Альтернативный вариант
// Форматирует время до окончания торгов лота.
function time_to_lot_end_format_NOT_USING($now, $date)
{
    $result = null;

    if (is_string($date)) {
        $date = new DateTime($date);
    }

    $now_midnight = new DateTime($now->format('Y-m-d'));
    $date_midnight = new DateTime($date->format('Y-m-d'));

    $date_diff_with_time = $now->diff($date);
    $date_diff = $now_midnight->diff($date_midnight);

    if ($date_diff->d > 1) {
        $result = $date->format('d.m.y') . ' в ' . $date->format('H:i');
    } elseif ($date_diff->d === 1) {
        $result = 'Завтра, в ' . $date->format('H:i');
    } else {
        $hours_ago = get_noun_plural_form_with_number($date_diff_with_time->h, 'час', 'часа', 'часов');
        $minutes_ago = get_noun_plural_form_with_number($date_diff_with_time->i, 'минута', 'минуты', 'минут');

        $result = 'Осталось ' . $hours_ago . ' ' . $minutes_ago;
    }

    return $result;
}

// ToDo
// Должна ли функция знать, какие ключи должны быть в массиве с ошибками
// По идее лучше передавать аргументы... но вдруг кол-во проверок увеличится => увеличится кол-во параметров - сообщений об ошибках
//
// Простая валидация данных из формы.
// Проверка на пустоту и отсечение тэгов, лишних пробелов, экранирование.
function validate_form_field($field_name, $field_value, $error_messages, $filter_option = null)
{
    $error = '';

    $field_value = secure_data_for_sql_query($field_value);

    if (!empty($filter_option)) {
        $field_value = filter_var($field_value, $filter_option);

        if (!$field_value) {
            $error = $error_messages['filter'];
        }
    } else {
        if (strlen($field_value) === 0) {
            $error = $error_messages['zero_length'];  
        }
    }

    return [ 'is_valid' => empty($error), 
             'field_value' => $field_value,
             'error' => $error
           ];
}

// Функция валидации полей формы
function validate_form_data($form_data, $form_fields) 
{
    $errors = [];
    $validated_data = [];

    foreach($form_fields as $field_name => $field_validate_data)
    {
        $field_value = $form_data[$field_name];

        $result_data = validate_form_field( $field_name, 
                                            $field_value, 
                                            $field_validate_data['error_messages'],
                                            $field_validate_data['filter_option'] ?? null);

        if ($result_data['is_valid']) {
            $validated_data[$field_name] = $result_data['field_value'];
        } else {
            $errors[$field_name] = $result_data['error'];
        }
    }

    return ['data' => $validated_data, 'errors' => $errors];
}

// Функция сбора данных пришедших из формы. 
function get_form_data($form_field_names) 
{
    $form_data = [];

    foreach($form_field_names as $field_name) {
        if (isset($_POST[$field_name])) {
            $form_data[$field_name] = $_POST[$field_name];
        }
    }

    return $form_data;
}

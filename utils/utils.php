<?php

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

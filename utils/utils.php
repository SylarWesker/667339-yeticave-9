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

// ToDo Не учитываю invert!!!
// Возвращает истину, если в интервале один час или меньше.
function is_equal_or_less_hour($date_interval) 
{
    return is_less_hour($date_interval) || is_equal_hour($date_interval);
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
    //     $lot_name = call_user_func($func, $param);
    // }

    $param = trim($param);
    $param = strip_tags($param);

    // т.к использую подготовленные запросы, то экранировать не обязательно
    // но предположим, что я передаю это коду записи в БД, которому не доверяю (не знаю использует он подготовленные запросы или нет).
    // короче перестраховываюсь
    $param = addslashes($param); 

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

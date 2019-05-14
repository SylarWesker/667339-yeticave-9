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

        // т.к использую подготовленные запросы, то экранировать не обязательно
        // но предположим, что я передаю это коду записи в БД, которому не доверяю (не знаю использует он подготовленные запросы или нет).
        // короче перестраховываюсь
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
// Подумать как эту функцию можно назвать.
// ToDo
// Протестировать!!!
// 
// Форматы
// 20 минут назад
// Час назад
// Вчера, в 21:30
// 19.03.17 в 08:21
function human_friendly_time($now, $date)
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

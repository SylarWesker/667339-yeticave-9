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

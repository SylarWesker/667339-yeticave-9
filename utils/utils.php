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
    // тут по идее нужно чтобы все было нулем (но миллисекунды не буду уже учитывать)
    $only_hours_has = $date_interval->y === 0 && 
                      $date_interval->m === 0 && 
                      $date_interval->d === 0;

    $seconds_in_one_hour = 3600;
    
    $total_seconds = $date_interval->h * $seconds_in_one_hour + 
                     $date_interval->i * 60 + 
                     $date_interval->s;

    return $only_hours_has && ($total_seconds <= $seconds_in_one_hour);
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

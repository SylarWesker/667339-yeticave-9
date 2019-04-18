<?php

// Удаляет тэги в массиве (если элемент массива сам является массивом, то тоже делает и в нем).
// Возвращает измененную копию.
function strip_tags_for_array($arr_data, $recursive = false)
{
    $arr_copy = $arr_data;

    foreach($arr_data as $arr_item)
    {
        if (is_array($arr_item)) {
            if ($recursive) {
                $arr_item = strip_tags_for_array($arr_item, $recursive);
            }
        } elseif (is_string($arr_item)) {
            $arr_item = strip_tags($arr_item);
        }
    }

    return $arr_copy;
}

// Возвращает истину, если в интервале один час или меньше.
function is_equal_or_less_hour($time_interval)
{
    return ($time_interval->h === 1 && $time_interval->i === 0) || $time_interval->h === 0;
}
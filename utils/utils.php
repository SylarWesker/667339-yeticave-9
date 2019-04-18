<?php

// Удаляет тэги в массиве (если элемент массива сам является массивом, то тоже делает и в нем).
// Возвращает измененную копию.
function strip_tags_for_array($arr_data, $recursive = false)
{
    foreach($arr_data as $key => $value)
    {
        if (is_array($value)) {
            if ($recursive) {
                $arr_copy[$key] = strip_tags_for_array($value, $recursive);
            }
        } elseif (is_string($value)) {
            $arr_copy[$key] = strip_tags($value);
        }
    }

    return $arr_data;
}

<?php

// Удаляет тэги в массиве (если элемент массива сам является массивом, то тоже делает и в нем).
// Возвращает измененную копию.
function strip_tags_for_array($arr_data, $recursive = false)
{
    $arr_copy = $arr_data;

    foreach(array_keys($arr_copy) as $key)
    {
        if (is_array($arr_copy[$key])) {
            if ($recursive) {
                $arr_copy[$key] = strip_tags_for_array($arr_copy[$key], $recursive);
            }
        } elseif (is_string($arr_copy[$key])) {
            $arr_copy[$key] = strip_tags($arr_copy[$key]);
        }
    }

    return $arr_copy;
}

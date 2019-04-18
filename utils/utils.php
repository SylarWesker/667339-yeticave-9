<?php

// Удаляет тэги в массиве (если элемент массива сам является массивом, то тоже делает и в нем).
// Возвращает измененную копию.
function strip_tags_for_array($arr_data, $recursive = false)
{
    $arr_copy = $arr_data;

    foreach($arr_copy as &$arr_item)
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

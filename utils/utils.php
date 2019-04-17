<?php

// Удаляет тэги в массиве (если элемент массива сам является массивом, то тоже делает и в нем).
// Меняет значения в передаваемом массиве.
function strip_tags_for_array(&$arr_data)
{
    foreach($arr_data as &$arr_item)
    {
        if (is_array($arr_item)) {
            strip_tags_for_array($arr_item);
        } elseif (is_string($arr_item)) {
            $arr_item = strip_tags($arr_item);
        }
    }
    unset($arr_item);
}

<?php

error_reporting(E_ALL);

require_once('auth.php');
require_once('helpers.php');
require_once('utils/db_helper.php');

require_once('getwinner.php');

use yeticave\db\functions as db_func;

$title = 'Главная';
$stuff_categories = [];
$lots = [];
$errors = [];

// список категорий. 
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if (!is_null($func_result['error'])) {
    $errors[] = 'Ошибка MySql при получении списка категорий ( ' . $func_result['error'] . ' )';  
}

// список лотов.
$count_lots = 9;

$func_result = db_func\get_lots($con, null, true, $count_lots);
$lots = $func_result['result'] ?? [];

if (!is_null($func_result['error'])) {
    $errors[] = 'Ошибка MySql при получении лотов: ' . $func_result['error'];  
}

$con = null;

if (!empty($errors)) {
    show_500($errors,  $stuff_categories, $is_auth, $user_name);
    return;
}

$content = include_template('index.php', [
                                            'stuff_categories' => $stuff_categories, 
                                            'lots' => $lots
                                         ]);

$layout = include_template('layout.php', [
                                          'title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name
                                         ]);

print($layout);

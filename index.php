<?php

// ToDo
// - добавить в каждый сценарий
// - добавить в каждый шаблон ???
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
    $errors[] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
}

// список лотов.
$func_result = db_func\get_lots($con);
$lots = $func_result['result'] ?? [];

if (!is_null($func_result['error'])) {
    $errors[] = 'Ошибка MySql при получении лотов: ' . $func_result['error'];  
}

$con = null;

$content = null;

if (empty($errors)) {
    $content = include_template('index.php', [
                                                'stuff_categories' => $stuff_categories, 
                                                'lots' => $lots
                                             ]);
} else {
    $title = 'Ошибка сервера';
    $content = include_template('500.php', ['error_list' => $errors]);
}

$layout = include_template('layout.php', [
                                          'title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => is_auth(), 
                                          'user_name' => $user_name
                                         ]);

print($layout);

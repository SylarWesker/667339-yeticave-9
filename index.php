<?php

require_once('auth.php');

require_once('helpers.php');

// ToDo
// Проверить в файлах все require_once. Все ли они нужны.
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$title = 'Главная';
$stuff_categories = [];
$lots = [];

if (!$con) {
    print('Ошибка подключения к БД!');

    die('Ошибка подключения к БД!');
} 

// print('Соединение уставлено!');

// список лотов.
$func_result = db_func\get_lots($con);
$lots = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    print('Ошибка MySql при получении лотов: ' . $func_result['error']);  
}

// список категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    print('Ошибка MySql при получении списка категорий: ' . $func_result['error']);  
}

$con = null;

// ToDo
// По идее нужно вынести header и footer в отдельные шаблоны.
// или не нужно?

$content = include_template('index.php', ['stuff_categories' => $stuff_categories, 
                                          'lots' => $lots
                                          ]);

$layout = include_template('layout.php', ['title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name]);

print($layout);

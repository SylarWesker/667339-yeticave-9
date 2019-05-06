<?php

require_once('helpers.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// эти параметры должны браться откуда из другого места...
// пока просто закопирую.
$user_name = 'Sylar';
$is_auth = rand(0, 1);
$title = 'Добавление лота';

$stuff_categories = [];

// Получение списка категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] === null ? [] : $func_result['result']; 

if ($func_result['error'] !== null) {
    $errors[] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
}

$content = include_template('add-lot.php', ['stuff_categories' => $stuff_categories 
                                        ]);

$layout = include_template('layout.php', [ 'title' => $title,
                                            'content' => $content, 
                                            'stuff_categories' => $stuff_categories, 
                                            'is_auth' => $is_auth, 
                                            'user_name' => $user_name
                                            ]);

print($layout);

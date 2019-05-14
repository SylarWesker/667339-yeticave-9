<?php

require_once('auth.php');
require_once('helpers.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

if ($is_auth === 0) {
    http_response_code(403);
    return;
}

$title_page = 'Мои ставки';

// Снова эти категории. 
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    print('Ошибка MySql при получении списка категорий: ' . $func_result['error']);  
}

// Получаем ставки пользователя.
$func_result = db_func\get_bets($con, $user_id);
$bets = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    print('Ошибка MySql при получении ставок: ' . $func_result['error']);  
}

$content = include_template('my-bets.php', ['bets' => $bets]);

$layout = include_template('layout.php', [ 'title' => $title_page,
                                           'content' => $content, 
                                           'stuff_categories' => $stuff_categories, 
                                           'is_auth' => $is_auth, 
                                           'user_name' => $user_name
                                         ]);

print($layout);

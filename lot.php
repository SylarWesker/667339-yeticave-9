<?php

require_once('helpers.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// эти параметры должны браться откуда из другого места...
// пока просто закопирую.
$user_name = 'Sylar';
$is_auth = rand(0, 1);

$errors = [];
$id = null; 
$lot = null;
$title = '';

// категории заново получаем? ну вроде да...
$stuff_categories = [];

 // Получение списка категорий.
 $func_result = db_func\get_stuff_categories($con);
 $stuff_categories = $func_result['result'] === null ? [] : $func_result['result']; 

 if ($func_result['error'] !== null) {
     $errors[] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
 }

if (isset($_GET['id'])) {
    // проверяю является ли числом (ну или просто сразу пытаюсь привести к числу)
    if (is_numeric($_GET['id'])) {
        $id = intval($_GET['id']);

        // Берем лот по id.
        $func_result = db_func\get_lots($con, [ $id ]);
        $lot = $func_result['result'] === null ? [] : $func_result['result'][0]; 

        if ($func_result['error'] !== null) {
            $errors[] = $lot['error'];
        } else {
            if ($lot === null) {
                $errors[] = 'Не найден лот с id = ' . $id;
            } 
        }
    } else {
        $errors[] = 'Параметр id не верного формата. Должно быть целое число.';
    }
} else {
    $errors[] = 'Не передан параметр id!';
}

$con = null;

if (count($errors) != 0) {
    // показываем 404 и ошибки
    header('Location: pages/404.html');
}

$content = include_template('lot.php', ['stuff_categories' => $stuff_categories,
                                        'lot' => $lot
                                        ]);

$layout = include_template('layout.php', [ 'title' => $lot['name'],
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name
                                          ]);

print($layout);

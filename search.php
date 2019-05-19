<?php

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$title = 'Поиск по лотам';
$errors = ['validation' => [], 'fatal' => []];

// Получение списка категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

$search_query = null;
$lots = null;

if (isset($_GET['find'])) {
    $form_field = ['search' => ['error_messages' => [ 'zero_length' => 'Запрос должен содержать хотя бы один символ']]];

    // Сбор данных с формы.
    $form_data = get_form_data(array_keys($form_fields));

    // Валидация данных с формы.
    $validation_result = validate_form_data($form_data, $form_fields);

    $errors['validation'] = $validation_result['errors'];
    $validated_data = $validation_result['data'];

    if (empty($errors['validation'])) {
       // Получаем лоты 

    }
}

// ToDo
// и снова...
// как правильно передавать 'поисковый запрос'
// его может не быть, он может быть пустым
$content = include_template('search.php', [ 
                                            'lots' => $lots,
                                            'search_query' => $validated_data['search'],
                                            'stuff_categories' => $stuff_categories
                                          ]);

$layout = include_template('layout.php', [
                                            'title' => $title, 
                                            'content' => $content, 
                                            'stuff_categories' => $stuff_categories, 
                                            'is_auth' => is_auth(), 
                                            'user_name' => $user_name
                                         ]);

print($layout);
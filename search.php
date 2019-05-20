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

$search_query = '';
$lots = [];
$lots_limit = 9; // ограничение кол-ва лотов на странице.

$min_page_number = 1;
$page_number = $min_page_number;

if(isset($_GET['page'])) {
  $page_param = $_GET['page'];

  if (is_numeric($page_param)) {
    $page_number = intval($page_param);
  }
}

if (isset($_GET['find'])) {
  $form_fields = ['search' => ['error_messages' => [ 'zero_length' => 'Запрос должен содержать хотя бы один символ']]];

  // Сбор данных с формы.
  $form_data = get_form_data(array_keys($form_fields));

  // Валидация данных с формы.
  $validation_result = validate_form_data($form_data, $form_fields);

  $errors['validation'] = $validation_result['errors'];
  $validated_data = $validation_result['data'];

  // ToDo
  // Валидация Поисковый запрос должен быть минимум 3 символа

  if (empty($errors['validation'])) {
    $search_query = $validated_data['search'];

    // ToDo
    // Подсчитать кол-во страниц всего.
    // На этот момент только один вариант. Вызвать запрос из get_lots_by_fulltext_search без указания
    // лимита и оффсета и получить от него COUNT(*)
    $func_result = db_func\get_lots_count_with_fulltext_search($con, $search_query);
    $count_lots = $func_result['result'];

    $max_page_number = intval(ceil($count_lots / $lots_limit));

    $page_number = max($min_page_number, $page_number);
    $page_number = min($max_page_number, $page_number);
    $page_number = intval($page_number);

    // расчитываем смещение для запроса в зависимости от номера текущей страницы
    $lot_offset = ($page_number - 1) * $lots_limit;

    // Получаем лоты 
    $func_result = db_func\get_lots_by_fulltext_search($con, $search_query, $lots_limit, $lot_offset);
    $lots = $func_result['result'];

    // var_dump($min_page_number);
    // var_dump($page_number);
    // var_dump($max_page_number);

    // ToDo подумать над записью ошибок
    // $errors['fatal'][] = $func_result['error'];
  }
}

$content = include_template('search.php', [ 
                                            'search_query' => $search_query,
                                            'lots' => $lots,
                                            'stuff_categories' => $stuff_categories,

                                            'min_page_number' => $min_page_number,
                                            'current_page' => $page_number,
                                            'max_page_number' => $max_page_number
                                          ]);

$layout = include_template('layout.php', [
                                            'title' => $title, 
                                            'content' => $content, 
                                            'stuff_categories' => $stuff_categories, 
                                            'is_auth' => is_auth(), 
                                            'user_name' => $user_name,
                                            'search_query' => $search_query
                                         ]);

print($layout);

<?php

error_reporting(E_ALL);

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$title = 'Лоты по категориям';
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

$category_name = '';

if(isset($_GET['category_name'])) {
    $form_fields = ['category_name' => ['error_messages' => [ 'zero_length' => 'Не указано название категории']]];

    // Сбор данных с формы.
    $form_data = get_form_data(array_keys($form_fields));

    // Валидация данных с формы.
    $validation_result = validate_form_data($form_data, $form_fields);

    $errors['validation'] = $validation_result['errors'];
    $validated_data = $validation_result['data'];

    // Название категории
    $category_name = $validated_data['category_name'];

    // Проверяем есть ли такая категория в БД
    $category_id = db_func\get_category_id($con, $category_name);

    if (is_null($category_id)) {
        $errors['validation']['category_name'] = "Не cуществует категории - $category_name";
    }

    if (empty($errors['validation'])) {
        $func_result = db_func\get_lots_count_by_category($con, $category_id);
        $count_lots = $func_result['result']; // кол-во лотов всего по данной категории
    
        $max_page_number = get_max_page_number($lots_limit, $count_lots );
        $page_number = correct_page_number($page_number, $max_page_number);

        if ($count_lots > 0) {
          // расчитываем смещение для запроса в зависимости от номера текущей страницы
          $lot_offset = ($page_number - 1) * $lots_limit;

          // Получаем лоты по категории
          $func_result = db_func\get_lots_by_category($con, $category_id, $lots_limit, $lot_offset);
          $lots_by_category = $func_result['result'];
        }
    }
    // ToDo
    // Если есть ошибки валидации, то обработать их!
  }

  $content = include_template('all-lots.php', [ 
                                                'category_name' => $category_name,
                                                'lots' => $lots_by_category,
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
                                            'user_name' => $user_name
]);

print($layout);

<?php

error_reporting(E_ALL);

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

require_once('utils/add_lot_functions.php');

use yeticave\db\functions as db_func;

// Если пользователь не авторизован, то показываем 403
if (!$is_auth) {
    http_response_code(403);
    return;
}

$title = 'Добавление лота';

$accepted_mime_types = ['image/png', 'image/jpeg'];
$errors = ['validation' => [], 'fatal' => []];
$form_data = [];

$upload_dir = 'uploads'; // папка для загрузки файлов
$uploads_path = __DIR__ . DIRECTORY_SEPARATOR . $upload_dir;

$stuff_categories = [];

// Получение списка категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    $errors['fatal']['get_categories'] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
}

// Валидация данных формы.
if (isset($_POST['submit'])) {
    $form_fields = [
        'lot-name' => [ 'error_messages' => ['zero_length' => 'Введите название лота']],
        'category' => [ 'error_messages' => ['zero_length' => 'Не указана категория лота']],
        'message'  => [ 'error_messages' => ['zero_length' => 'Введите описание лота']],
        'lot-rate' => [ 'error_messages' => ['zero_length' => 'Введите начальную стоимость лота']],
        'lot-step' => [ 'error_messages' => ['zero_length' => 'Введите шаг ставки']],
        'lot-date' => [ 'error_messages' => ['zero_length' => 'Введите дату окончания торгов']],
    ];

    // Сбор данных с формы.
    $form_data = get_form_data(array_keys($form_fields));

    // Валидация данных с формы.
    $validation_result = validate_form_data($form_data, $form_fields);

    $errors['validation'] = $validation_result['errors'];
    $validated_data = $validation_result['data'];

    
    $lot_name = $validated_data['lot-name']; // Наименование
    $lot_description = $validated_data['message']; // Описание
   
    // Уникальные проверки.
    // ------------------------------------------------------------------------------
    $lot_category = $validated_data['category'];  // Категория.

    // Проверяем есть ли такая категория в БД.
    $lot_category_id = db_func\get_category_id($con, $lot_category);

    if (is_null($lot_category_id)) {
        $errors['validation']['category'] = 'Категории $lot_category не существует!';
    }

    // Начальная цена
    $start_price = $validated_data['lot-rate']; 

    $belong_zero_msg = 'Начальная цена должна быть больше нуля';
    $not_number_msg = 'Начальная цена должна быть числом';

    $func_result = validate_price($start_price, $belong_zero_msg, $not_number_msg);
    if (!$func_result['is_valid']) {
        $errors['validation']['lot-rate'] = $func_result['error'];
    }

    // Шаг ставки
    $step_bet = $validated_data['lot-step'];

    $belong_zero_msg = 'Шаг ставки должен быть больше нуля';
    $not_number_msg = 'Шаг ставки должен должна быть числом';

    $func_result = validate_price($step_bet, $belong_zero_msg, $not_number_msg);
    if (!$func_result['is_valid']) {
        $errors['validation']['lot-step'] = $func_result['error'];
    }

    // Дата окончания торгов
    $lot_end_date = $validated_data['lot-date'];

    $func_result = validate_lot_end_date($lot_end_date);
    if (!$func_result['is_valid']) {
        $errors['validation']['lot-date'] = $func_result['error'];
    }
    $lot_end_date = new DateTime($lot_end_date);

    // Изображение лота
    $relative_img_path = null;

    if (isset($_FILES['lot-img']) && $_FILES['lot-img']['error'] === UPLOAD_ERR_OK) {
        $tmp_file_path = $_FILES['lot-img']['tmp_name'];
        $file_name = $_FILES['lot-img']['name'];

        $mime_type = mime_content_type($tmp_file_path);

        if (!in_array($mime_type, $accepted_mime_types)) {
            $errors['validation']['lot-img'] = 'Недопустимый тип файла';
        }

        // если все ок, то перещаем в папку uploads
        if (!isset($errors['validation']['lot-img'])) {
            $func_result = save_file_on_server($tmp_file_path, $file_name, $uploads_path);

            $relative_img_path = $upload_dir . DIRECTORY_SEPARATOR . $func_result['new_file_name'];
        }
    }

    // Если все ок, то добавляем в БД.
    if (empty($errors['validation'])) {
        // Дата окончания аукциона - это выбранная дата + время равное 23.59.59
        $lot_end_time = '23:59:59';

        $params = [ 
                    'author_id'     => $user_id, 
                    'name'          => $lot_name, 
                    'category_id'   => $lot_category_id, 
                    'description'   => $lot_description, 
                    'start_price'   => $start_price, 
                    'step_bet'      => $step_bet, 
                    'end_date'      => $lot_end_date->format('Y/m/d ' . $lot_end_time), 
                    'image_url'     => $relative_img_path
        ];

        $added_lot_id = db_func\add_lot($con, $params);

        if ($added_lot_id !== null) {
            $new_lot_url = 'lot.php?id=' . $added_lot_id;

            header('Location: ' . $new_lot_url);
        } else {
            $errors['fatal'] = 'Не удалось добавить лот.';
        }
    }
}

if (!empty($errors['fatal'])) {
    show_500($errors,  $stuff_categories, $is_auth, $user_name);
    return;
}

$content = include_template('add-lot.php', [ 'stuff_categories' => $stuff_categories,
                                             'errors'           => $errors['validation'],
                                             'form_data'        => $form_data
                                           ]);

$layout = include_template('layout.php', [  'title'             => $title,
                                            'content'           => $content, 
                                            'stuff_categories'  => $stuff_categories, 
                                            'is_auth'           => $is_auth, 
                                            'user_name'         => $user_name
                                         ]);

print($layout);

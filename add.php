<?php

error_reporting(E_ALL);

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$is_auth = is_auth();

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
            $func_result = save_file_on_server($tmp_file_path, $file_name, $upload_dir);

            $relative_img_path = $uploads_dir . '/' . $func_result['new_file_name'];
        }
    }

    // Если все ок, то добавляем в БД.
    if (count($errors['validation']) === 0) {
        $params = [ 
                    'author_id'     => $user_id, 
                    'name'          => $lot_name, 
                    'category_id'   => $lot_category_id, 
                    'description'   => $lot_description, 
                    'start_price'   => $start_price, 
                    'step_bet'      => $step_bet, 
                    'end_date'      => $lot_end_date->format('Y/m/d 23:59:59'), 
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

$content = include_template('add-lot.php', [ 'stuff_categories' => $stuff_categories,
                                             'errors' => $errors['validation'],
                                             'form_data' => $form_data
                                           ]);

$layout = include_template('layout.php', [  'title' => $title,
                                            'content' => $content, 
                                            'stuff_categories' => $stuff_categories, 
                                            'is_auth' => $is_auth, 
                                            'user_name' => $user_name
                                            ]);

print($layout);

// Функции

// Валидация даты окончания торгов
function validate_lot_end_date($end_date)
{
    $error_msg = '';

    if (!is_date_valid($end_date)) {
        $error_msg = 'Дата должна быть в формате ГГГГ-ММ-ДД.';
    }

    // Проверка того что дата больше текущей хотя бы на один день.
    $date_now = new DateTime();
    $lot_end_date = new DateTime($end_date);
    $date_diff = $date_now->diff($end_date);

    if (!at_least_one_day_bigger($date_diff)) {
        $error_msg = 'Дата завершения торгов должна быть больше текущей даты хотя на 1 день!';
    }

    $is_valid = $error_msg === '';

    return ['is_valid' => $is_valid, 'error' => $error_msg];
}

// Перемещение картинки в папку на сервере (постоянную папку)
function save_file_on_server($tmp_file_path, $file_name, $uploads_dir)
{
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);

    $uploads_path = __DIR__ . '/' . $uploads_dir;
    $new_file_name = uniqid() . '.' . $extension;

    $new_file_path = $uploads_path . '/' . $new_file_name;

    move_uploaded_file($tmp_file_path, $new_file_path);

    return ['new_file_name' => $new_file_name, 'new_file_path' => $new_file_path];
}

// Валидация цены
function validate_price($price_value, $belong_zero_msg, $now_number_msg)
{
    $error_msg = '';

    if (is_numeric($price_value)) {
        $price_value = intval($price_value);

        if ($price_value <= 0) {
            $error_msg = $belong_zero_msg;
        }
    } else {
        $error_msg = $now_number_msg;
    }

    $is_valid = $error_msg === '';

    return ['is_valid' => $is_valid, 'error' => $error_msg];
}

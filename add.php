<?php

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// Если пользователь не авторизован, то показываем 403
if ($is_auth === 0) {
    http_response_code(403);
    return;
}

$title = 'Добавление лота';

// Валидация данных формы.
$accepted_mime_types = ['image/png', 'image/jpeg'];
$errors = [];
$form_data = [];

// ToDo поработать над текстом ошибок.
if (isset($_POST['submit'])) {
    // Наименование (обязательное)
    $lot_name = NULL;

    if (isset($_POST['lot-name'])) {
        $lot_name = $_POST['lot-name'];

        $lot_name = secure_data_for_sql_query($lot_name);
        $form_data['lot-name'] = $lot_name;

        // не пустое должно быть.
        if (strlen($lot_name) === 0) {
            $errors['lot-name'] = 'Введите название лота';
        }
    } else {
        // такая ошибка может произойти только если на форме нет поля с именем 'lot-name' ?
        // если да, то это будет больше отладочная инфа для разработчиков. нужно будет текст ошибки изменить.
        $errors['lot-name'] = 'Не задано название лота!';
    }

    // Категория...
    // ToDo
    // Как проверить допустимая ли категория? Каждый раз выполнять запрос к БД? Это накладно?
    // Или не проверять в php, а проверку заложить на уровне sql (БД). Но как тогда понять почему именно не выполнился запрос? корректное сообщение об ошибке.
    $lot_category = NULL;
    $lot_category_id = NULL;

    if (isset($_POST['category'])) {
        $lot_category = $_POST['category'];

        $lot_category = secure_data_for_sql_query($lot_category);
        $form_data['category'] = $lot_category;

        // Проверяем есть ли такая категория в БД.
        $lot_category_id = db_func\get_category_id($con, $lot_category);

        if ($lot_category_id === NULL) {
            $errors['category'] = 'Такой категории нет в БД!';
        }
    } else {
        $errors['category'] = 'Не задана категория лота!';
    }

    // Описание (пускай будет необязательное)
    $lot_description = NULL;

    if (isset($_POST['message'])) {
        $lot_description = $_POST['message'];

        $lot_description = secure_data_for_sql_query($lot_description);
        $form_data['message'] = $lot_description;
    }

    // Начальная цена (обязательное)
    $start_price = NULL;

    if (isset($_POST['lot-rate'])) {
        $start_price = $_POST['lot-rate'];

        $start_price = secure_data_for_sql_query($start_price);
        $form_data['lot-rate'] = $start_price;

        // Будем считать, что может быть только целое число.
        if (is_numeric($start_price)) {
            $start_price = intval($start_price);

            if ($start_price <= 0) {
                $errors['lot-rate'] = 'Начальная цена должна быть больше нуля';
            }
        } else {
            $errors['lot-rate'] = 'Начальная цена должна быть числом';
        }
    } else {
        $errors['lot-rate'] = 'Не задана начальная цена лота!';
    }

    // Шаг ставки (обязательное)
    $step_bet = NULL;

    if (isset($_POST['lot-step'])) {
        $step_bet = $_POST['lot-step'];

        $step_bet = secure_data_for_sql_query($step_bet);
        $form_data['lot-step'] = $step_bet;

        // этот код уже второй раз использую!
        // ToDo Вынести в функцию!!!
        if (is_numeric($step_bet)) {
            // В ТЗ говорится, что шаг ставки должен быть целым числом. 
            // Нужно ли проверять является ли целым?
            $step_bet = intval($step_bet);

            if ($step_bet <= 0) {
                $errors['lot-step'] = 'Шаг ставки должен быть больше нуля!';
            }
        } else {
            $errors['lot-step'] = 'Шаг ставки должен быть числом!';
        }
    } else { 
        $errors['lot-step'] = 'Не задан шаг ставки!';
    }
 
    // Дата окончания торгов (обязательное)
    $lot_end_date = NULL;

    if (isset($_POST['lot-date'])) {
        $lot_end_date = $_POST['lot-date'];

        $lot_end_date = secure_data_for_sql_query($lot_end_date);
        $form_data['lot-date'] = $lot_end_date;

        if (!is_date_valid($lot_end_date)) {
            $errors['lot-date'] = 'Дата должна быть в формате ГГГГ-ММ-ДД.';
        }

        // Проверка того что дата больше текущей хотя бы на один день.
        $date_now = new DateTime();
        $lot_end_date = new DateTime($lot_end_date);

        $date_diff = $date_now->diff($lot_end_date);

        if (!at_least_one_day_bigger($date_diff)) {
            $errors['lot-date'] = 'Дата завершения торгов должна быть больше текущей даты хотя на 1 день!';
        }
    } else {
        $errors['lot-date'] = 'Не задана дата окончания торгов!';
    }

    // Изображение лота (необязательный пока)
    $relative_img_path = NULL;

    if (isset($_FILES['lot-img']) && $_FILES['lot-img']['error'] === UPLOAD_ERR_OK) {
        $tmp_file_path = $_FILES['lot-img']['tmp_name'];
        $file_name = $_FILES['lot-img']['name'];

        $mime_type = mime_content_type($tmp_file_path);

        if (!in_array($mime_type, $accepted_mime_types)) {
            $errors['lot-img'] = 'Недопустимый тип файла';
        }

        // если все ок, то перещаем в папку uploads
        if (!isset($errors['lot-img'])) {
            $extension = pathinfo($file_name , PATHINFO_EXTENSION);

            $uploads_dir = 'uploads';
            $uploads_path = __DIR__ . '/' . $uploads_dir;
            $new_file_name = uniqid() . '.' . $extension;

            $lot_img_path = $uploads_path . '/' . $new_file_name;
            $relative_img_path = $uploads_dir . '/' . $new_file_name;

            move_uploaded_file($tmp_file_path, $lot_img_path);
        }
    }

    // Если все ок, то добавляем в БД.
    if (count($errors) === 0) {
        $params = [ 'author_id' => $user_id, 
                    'name' => $lot_name, 
                    'category_id' => $lot_category_id, 
                    'description' => $lot_description, 
                    'start_price' => $start_price, 
                    'step_bet' => $step_bet, 
                    'end_date' => $lot_end_date->format('Y/m/d H:i:s'), 
                    'image_url' => $relative_img_path
                    ];

        $added_lot_id = db_func\add_lot($con, $params);

        if ($added_lot_id !== NULL) {
            $new_lot_url = 'lot.php?id=' . $added_lot_id;

            header('Location: ' . $new_lot_url);
        } else {
            // ToDo
            // Как обрабатывать эту ошибку?
        }
    } else {
        // ToDo
        // Обдумать этот механизм. Возможно некоторые данные даже не прошедшие проверку лучше все равно отправлять на форму. 

        // Записываем данные формы (данные которые прошли проверку).
        foreach($errors as $key => $value) {
            // если есть ошибка по этому ключу, то
            if (array_key_exists($key, $form_data)) {
                // удаляем из массива с данными формы. 
                unset($form_data[$key]);
            }
        }
    }
}

$stuff_categories = [];

// Получение списка категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    // ToDo
    // Это уже другой тип ошибок. Нужно будет писать в другой массив.
    $errors['get_categories'] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
}

$content = include_template('add-lot.php', [ 'stuff_categories' => $stuff_categories,
                                             'errors' => $errors,
                                             'form_data' => $form_data
                                           ]);

$layout = include_template('layout.php', [  'title' => $title,
                                            'content' => $content, 
                                            'stuff_categories' => $stuff_categories, 
                                            'is_auth' => is_auth(), 
                                            'user_name' => $user_name
                                            ]);

print($layout);

<?php

require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// эти параметры должны браться откуда из другого места...
// пока просто закопирую.
$user_name = 'Sylar';
$is_auth = rand(0, 1);
$title = 'Добавление лота';

// Валидация данных формы.
$errors = [];

// ToDo поработать над текстом ошибок.
if (isset($_POST['submit'])) {
    // Наименование (обязательное)
    $lot_name = NULL;

    if (isset($_POST['lot-name'])) {
        $lot_name = $_POST['lot-name'];

        $lot_name = secure_data_for_sql_query($lot_name);

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
    // Как проверить допустимая ли категория? Каждый раз выполнять запрос к БД? Это накладно?
    // Или не проверять в php, а проверку заложить на уровне sql (БД). Но как тогда понять почему именно не выполнился запрос? корректное сообщение об ошибке.
    $lot_category = NULL;

    if (isset($_POST['category'])) {
        $lot_category = $_POST['category'];

        $lot_category = secure_data_for_sql_query($lot_category);

        // Проверяем есть ли такая категория в БД.
        $has_category = db_func\has_category($con, $lot_category);

        if (!$has_category) {
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
    }

    // Начальная цена (обязательное)
    $start_price = NULL;

    if (isset($_POST['lot-rate'])) {
        $start_price = $_POST['lot-rate'];

        $start_price = secure_data_for_sql_query($start_price);

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
    $bet_step = NULL;

    if (isset($_POST['lot-step'])) {
        $bet_step = $_POST['lot-step'];

        $bet_step = secure_data_for_sql_query($bet_step);

        // этот код уже второй раз использую!
        // ToDo Вынести в функцию!!!
        if (is_numeric($bet_step)) {
            // В ТЗ говорится, что шаг ставки должен быть целым числом. 
            // Нужно ли проверять является ли целым?
            $bet_step = intval($bet_step);

            if ($bet_step <= 0) {
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

        if (!is_date_valid($lot_end_date)) {
            $errors['lot-date'] = 'Дата должна быть в формате ГГГГ-ММ-ДД.';
        }

        // Проверка того что дата больше текущей хотя бы на один день.
        $date_now = new DateTime();
        $lot_end_date = new DateTime($lot_end_date);

        $date_diff = $lot_end_date->diff($date_now);

        if (!at_least_one_day_bigger($date_diff)) {
            $errors['lot-date'] = 'Дата завершения торгов должна быть больше текущей даты хотя на 1 день!';
        }
    } else {
        $errors['lot-date'] = 'Не задана дата окончания торгов!';
    }

    // Загруженный файл / путь к файлу ??? (необязательный пока)


    // Если все ок, то добавляем в БД.
    if (count($errors) === 0) {
        echo 'Ошибок нет. Буду записывать в БД.';
    } 
    // else {
    //     echo 'Есть ошибки. Не запишу в БД и покажу на фронте.';
    // }
}

$stuff_categories = [];

// Получение списка категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] === null ? [] : $func_result['result']; 

if ($func_result['error'] !== null) {
    $errors['get_categories'] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
}

$content = include_template('add-lot.php', [ 'stuff_categories' => $stuff_categories,
                                             'errors' => $errors
                                           ]);

$layout = include_template('layout.php', [  'title' => $title,
                                            'content' => $content, 
                                            'stuff_categories' => $stuff_categories, 
                                            'is_auth' => $is_auth, 
                                            'user_name' => $user_name
                                            ]);

print($layout);

<?php

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$title = 'Регистрация пользователя';

// список категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    print('Ошибка MySql при получении списка категорий: ' . $func_result['error']);  
}

// Валидация.
$errors = [];
$form_data = [];

// ToDo
// Вынести валидация в отдельную функцию.
// Написать универсальную функцию валидации (те части  которые разные вынести допустим в функцию "создание пользователя")

if (isset($_POST['submit'])) {
    // email
    $email = NULL;

    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $email = secure_data_for_sql_query($email);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        $form_data['email'] = $email;

        if (!$email) {
            $errors['email'] = 'Не указан email или неверный формат.';
        } else {
            // ToDo
            // Это валидация или уже бизнес логика?

            // Теперь проверяем что такого email нет в БД.
            $already_has_email = db_func\has_email($con, $email);

            if ($already_has_email) {
                $errors['email'] = 'Пользователь с таким email уже зарегистрирован.';
            }
        } 
    } else {
        $errors['email'] = 'Не указан email.';
    }

    // Пароль.
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
        $password = secure_data_for_sql_query($password);

        if (strlen($password) === 0) {
            $errors['password'] = 'Пароль не может быть пустым.';
        }

        // хэшируем пароль.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $errors['password'] = 'Не указан пароль.';
    }

    // Имя пользователя.
    if (isset($_POST['name'])) {
        $user_name = $_POST['name'];
        $user_name = secure_data_for_sql_query($user_name);

        $form_data['name'] = $user_name;

        if (strlen($user_name) === 0) {
            $errors['name'] = 'Имя пользователя не может быть пустым.';
        } else {
            // Теперь проверяем что такого пользователя нет в БД.
            $already_has_user = db_func\has_user($con, $user_name);

            if ($already_has_user) {
                $errors['name'] = 'Пользователь с таким именем уже зарегистрирован.';
            }
        }
    } else {
        $errors['name'] = 'Не указано имя пользователя.'; 
    }

    // Контактные данные (пускай будет обязательными). просто не пусто
    if (isset($_POST['message'])) {
        $contacts = $_POST['message'];
        $contacts = secure_data_for_sql_query($contacts);

        $form_data['message'] = $contacts;

        if (strlen($user_name) === 0) { 
            $errors['message'] = 'Заполните поле с контактными данными.';
        }
    } else {
        $errors['message'] = 'Не указаны контактные данные пользователя.'; 
    }

    if (count($errors) === 0) {
        // Добавляем пользователя в БД.
        $added_user_id = db_func\add_user($con, $email, $user_name, $password_hash, $contacts);

        // Редирект на страницу авторизации.
        if ($added_user_id !== NULL) {
            $login_page = 'login.php';

            header('Location: ' . $login_page);
        } else {
            // ToDo
            // Или я перехватываю ошибки SQL в функциях работы с БД и возврашаю сюда и помещаю в массив ошибок (c отдельным ключом)
            // см. пример
        }
    }
}

// Пример
// 
// $errors = [
//     'fatal' => [],
//     'validate' => []
// ];

$con = null;

$content = include_template('sign-up.php', [ 'form_data' => $form_data,
                                             'errors' => $errors,
                                             'stuff_categories' => $stuff_categories
                                           ]);

$layout = include_template('layout.php', ['title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name]);

print($layout);

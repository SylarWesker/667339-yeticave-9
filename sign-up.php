<?php

require_once('helpers.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$title = 'Регистрация пользователя';

// Странно эти данные сюда копировать. но в принципе может же уже авторизованный зарегистрировать еще один аккаунт?
// $user_name = 'Sylar';
// $is_auth = rand(0, 1);
$user_name = '';
$is_auth = 0;

// Да сколько уже можно копировать этот код???
// список категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] === null ? [] : $func_result['result']; // стремно, но что поделать.

if ($func_result['error'] !== null) {
    print('Ошибка MySql при получении списка категорий: ' . $func_result['error']);  
}

// Валидация.
$errors = [];
$form_data = [];

if (isset($_POST['submit'])) {
    // ToDo
    // можно объединить проверку наличия email и имени пользователя в одном запросе?

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
            // Теперь проверяем что такого email нет в БД.
            $already_has_email = db_func\has_email($email);

            if ($already_has_email) {
                $errors['email'] = 'Пользователь с таким email уже зарегистрирован.';
            }
        } 
    } else {
        $errors['email'] = 'Не указан email.';
    }

    // пароль
    if (isset($_POST['password'])) {
        $password = $_POST['password'];
        $password = secure_data_for_sql_query($password);

        if (strlen($password) === 0) {
            $errors['password'] = 'Пароль не может быть пустым.';
        }

        // хэшируем пароль.
        // $password = 
    } else {
        $errors['password'] = 'Не указан пароль';
    }

    // Имя пользователя.
}

$con = null;

$content = include_template('sign-up.php', [
                                           ]);

$layout = include_template('layout.php', ['title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name]);

print($layout);

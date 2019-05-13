<?php

session_start();

require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$title = 'Авторизация';

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
    // email
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $email = secure_data_for_sql_query($email);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        $form_data['email'] = $email;
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
    } else {
        $errors['password'] = 'Не указан пароль.';
    }

    // теперь проверяем зарегистрирован ли пользователь.
    if (count($errors) === 0) {
       $user_data = db_func\get_name_password_by_email($con, $email);

       if ($user_data['error'] === NULL) {
            $password_from_db = $user_data['result'][0]['password'];

            $password_correct = password_verify($password, $password_from_db);

            if ($password_correct) {
                $user_name = $user_data['result'][0]['name'];
                $is_auth = 1;

                $_SESSION['user_name'] = $user_name;
                $_SESSION['is_auth'] = $is_auth;

                // echo 'Авторизовался';
                header('Location: index.php');
            } else {
                $errors['password'] = 'Неверный пароль.';
            }
       } else {
            $errors['email'] = 'Пользователь с указанным email не найден.';
       }
    }
}

$con = null;

$content = include_template('login.php', [ 'form_data' => $form_data,
                                            'errors' => $errors
                                           ]);

$layout = include_template('layout.php', ['title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name]);

print($layout);

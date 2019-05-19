<?php

session_start();

require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$title = 'Авторизация';
$user_name = '';
$is_auth = 0;

$errors = [ 'validation' => [], 'fatal' => [] ];
$form_data = []; // данные из формы
$validated_data = []; // провалидированные (скорректированные) данные из формы.

// список категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? []; 

if ($func_result['error'] !== null) {
    print('Ошибка MySql при получении списка категорий: ' . $func_result['error']);  
}

if (isset($_POST['submit'])) {
    // Сбор данных с формы.
    $form_data = get_form_data(array_keys($form_fields));

    // Верификация логина и пароля


    // теперь проверяем зарегистрирован ли пользователь.
    if (count($errors) === 0) {
       $user_data = db_func\get_userdata_by_email($con, $email);

       if ($user_data['error'] === NULL) {
            // ToDo
            // 1. Процесс login вынести в отдельную функцию.
            // 2. В сообщении ошибки - говорить что неверный пользователь или пароль (НЕ РАЗДЕЛЯЕМ чтобы не подобрали злоумыленики список пользователей)
            $password_from_db = $user_data['result'][0]['password'];

            $password_correct = password_verify($password, $password_from_db);

            if ($password_correct) {
                $user_name = $user_data['result'][0]['name'];
                $user_id = $user_data['result'][0]['id'];
                $is_auth = 1;

                $_SESSION['user_name'] = $user_name;
                $_SESSION['user_id'] = $user_id;
                $_SESSION['is_auth'] = $is_auth;

                header('Location: index.php');
            } else {
                $errors['password'] = 'Неверный логин и/или пароль.';
            }
       } else {
            $errors['email'] = 'Неверный логин и/или пароль.';
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

// Функции. 

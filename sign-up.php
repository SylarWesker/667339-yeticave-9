<?php

require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

$title = 'Регистрация пользователя';

$user_name = '';
$is_auth = 0;

$errors = [ 'validation' => [], 'fatal' => [] ];

// список категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    $errors['fatal'][] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
}

// Валидация.
$form_data = []; // данные из формы
$validated_data = []; // провалидированные (скорректированные) данные из формы.

if (isset($_POST['submit'])) {
    // Имена полей, которые будем валидировать.
    // и вспомогательный данные.
    // ToDo
    // не слишком ли сложная структура данных? 
    $form_fields = [ 'email' => ['filter_option' => FILTER_VALIDATE_EMAIL, 
                                 'error_messages' => [ 'filter' => 'Не указан email или неверный формат.',
                                                       'zero_length' => 'Email не может быть пустым'
                                                     ] 
                                ], 
                     'password' => ['error_messages' => [ 'zero_length' => 'Пароль не может быть пустым.']], 
                     'name' => ['error_messages' => ['zero_length' => 'Имя пользователя не может быть пустым.']], 
                     'message' => [ 'error_messages' => ['zero_length' => 'Заполните поле с контактными данными.']]
                   ];

    foreach($form_fields as $field_name => $field_validate_data)
    {
        if (isset($_POST[$field_name])) {
            // Сбор данных с формы.
            $field_value = $_POST[$field_name];
            $form_data[$field_name] = $field_value;

            // Валидация поля.
            $result_data = validate_form_field( $field_name, 
                                                $field_value, 
                                                $field_validate_data['error_messages'],
                                                $field_validate_data['filter_option'] ?? null);
            
            if ($result_data['is_valid']) {
                $validated_data[$field_name] = $result_data['field_value'];
            } else {
                $errors['validation'][$field_name] = $result_data['error'];
            }
        }
    }

    if (count($errors['validation']) === 0) {
        // Добавляем пользователя в БД.
        $email      = $validated_data['email'];
        $user_name  = $validated_data['name'];
        $password   = $validated_data['password'];
        $contacts   = $validated_data['message'];

        $added_user = register_user($con, $email, $user_name, $password, $contacts);

        // Редирект на страницу авторизации.
        if (empty($added_user['errors'])) {
            $login_page = 'pages/login.html'; // 'login.php'

            header('Location: ' . $login_page);
        } else {
            // Ошибки могут быть как и валидации - уже есть пользователь/email
            // так и фатальные - работа с БД.
            $errors['validation'] = $added_user['errors']['validation'];

            // ToDo
            // Или я перехватываю ошибки SQL в функциях работы с БД и возврашаю сюда и помещаю в массив ошибок (c отдельным ключом fatal)
        }
    }
}

$con = null;

$content = include_template('sign-up.php', [ 
                                             'form_data' => $form_data,
                                             'errors' => $errors['validation'],
                                             'stuff_categories' => $stuff_categories
                                           ]);

$layout = include_template('layout.php', [
                                          'title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name
                                          ]);

print($layout);


// Функции.

// Функция регистрации (добавления) пользователя.
function register_user($con, $email, $user_name, $password, $contacts)
{
    $errors = ['validation' => [], 'fatal' => []];

    $already_has_user = db_func\has_user($con, $user_name);
    if ($already_has_user) {
        $errors['validation']['name'] = 'Пользователь с таким именем уже зарегистрирован.';
    }

    $already_has_email = db_func\has_email($con, $email);
    if ($already_has_email) {
        $errors['validation']['email'] = 'Пользователь с таким email уже зарегистрирован.';
    }

    $added_user_id = null;

    if (empty($errors['validation']) && empty($errors['fatal'])) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Добавляем пользователя в БД.
        $added_user_id = db_func\add_user($con, $email, $user_name, $password_hash, $contacts);
    }
 
    return ['errors' => $errors, 'user_id' => $added_user_id];
}

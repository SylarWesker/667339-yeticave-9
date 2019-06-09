<?php

error_reporting(E_ALL);

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

require_once('utils/sign_up_functions.php');

use yeticave\db\functions as db_func;

$title = 'Регистрация пользователя';

$errors = [ 'validation' => [], 'fatal' => [] ];
$form_data = []; // данные из формы
$validated_data = []; // провалидированные (скорректированные) данные из формы.

// список категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? [];

if ($func_result['error'] !== null) {
    $errors['fatal'][] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
}

// Если отправлена форма. 
if (isset($_POST['submit'])) {
    // Имена полей, которые будем валидировать + вспомогательный данные для валидации
    $form_fields = [ 'email' => [
                                 'filter_option' => FILTER_VALIDATE_EMAIL, 
                                 'error_messages' => [ 
                                                       'filter' => 'Не указан email или неверный формат.',
                                                       'zero_length' => 'Email не может быть пустым'
                                                     ] 
                                ], 
                     'password' => ['error_messages' => [ 'zero_length' => 'Пароль не может быть пустым.']], 
                     'name' => ['error_messages' => ['zero_length' => 'Имя пользователя не может быть пустым.']], 
                     'message' => [ 'error_messages' => ['zero_length' => 'Заполните поле с контактными данными.']]
                   ];

    // Сбор данных с формы.
    $form_data = get_form_data(array_keys($form_fields));

    // Валидация данных с формы.
    $validation_result = validate_form_data($form_data, $form_fields);

    $errors['validation'] = $validation_result['errors'];
    $validated_data = $validation_result['data'];

    // Если нет ошибок валидации
    if (empty($errors['validation'])) {
        $email      = $validated_data['email'];
        $user_name  = $validated_data['name'];
        $password   = $validated_data['password'];
        $contacts   = $validated_data['message'];

        // Добавляем пользователя в БД.
        $added_user = register_user($con, $email, $user_name, $password, $contacts);

        $no_errors = empty($added_user['errors']['validation']) &&
                     empty($added_user['errors']['fatal']);

        // Редирект на страницу авторизации.
        if ($no_errors) {
            $login_page = 'login.php';

            header('Location: ' . $login_page);
        } else {
            $errors['validation'] = $added_user['errors']['validation'];
            $errors['fatal'] = array_merge($errors['fatal'] ?? [], $added_user['errors']['fatal'] ?? []);
        }
    }
}

$con = null;

if (!empty($errors['fatal'])) {
    show_500($errors, $stuff_categories, $is_auth, $user_name);
    return;
}

$content = include_template('sign-up.php', [ 
                                             'form_data'        => $form_data,
                                             'errors'           => $errors['validation'],
                                             'stuff_categories' => $stuff_categories
                                           ]);

$layout = include_template('layout.php', [
                                            'title'             => $title, 
                                            'content'           => $content, 
                                            'stuff_categories'  => $stuff_categories, 
                                            'is_auth'           => $is_auth, 
                                            'user_name'         => $user_name
                                          ]);

print($layout);

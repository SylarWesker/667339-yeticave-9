<?php

error_reporting(E_ALL);

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');
require_once('utils/login_functions.php');

use yeticave\db\functions as db_func;

$title = 'Авторизация';

$errors = [ 'validation' => [], 'fatal' => [] ];
$form_data = []; // данные из формы
$validated_data = []; // провалидированные (скорректированные) данные из формы.

// список категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] ?? []; 

if ($func_result['error'] !== null) {
    $errors['fatal'][] = 'Ошибка MySql при получении списка категорий: ' . $func_result['error'];  
}

if (isset($_POST['submit'])) {
    $form_fields = [ 
                    'email' => [ 
                                  'filter_option' => FILTER_VALIDATE_EMAIL, 
                                  'error_messages' => [ 
                                                        'filter' => 'Не указан email или неверный формат.',
                                                        'zero_length' => 'Email не может быть пустым'
                                                      ] 
                                ], 
                    'password' => ['error_messages' => [ 'zero_length' => 'Пароль не может быть пустым.']] 
                ];

    // Сбор данных с формы.
    $form_data = get_form_data(array_keys($form_fields));

    // Валидация логина и пароля
    $validation_result = validate_form_data($form_data, $form_fields); 
    
    $errors['validation'] = $validation_result['errors'];
    $validated_data = $validation_result['data'];

    // теперь проверяем зарегистрирован ли пользователь.
    if (empty($errors['validation'])) {
        $email = $validated_data['email'];
        $password = $validated_data['password'];

        $func_result = login_user($con, $email, $password);
        $login_result = $func_result['result'];
        $login_errors = $func_result['errors'];

        if ($login_result) {
            header('Location: index.php');
        } else {
            $errors['validation'] = $login_errors['validation'];
            $errors['fatal'] = array_merge($errors['fatal'] ?? [], $login_errors['fatal'] ?? []);
        }
    }
}

$con = null;

if (!empty($errors['fatal'])) {
    show_500($errors,  $stuff_categories, $is_auth, $user_name);
    return;
}

$content = include_template('login.php', [ 
                                            'form_data' => $form_data,
                                            'errors' => $errors['validation'],
                                            'stuff_categories' => $stuff_categories, 
                                         ]);

$layout = include_template('layout.php', [
                                          'title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name
                                         ]);

print($layout);

<?php

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

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
    $form_fields = [ 'email' => [ 'filter_option' => FILTER_VALIDATE_EMAIL, 
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
    if (count($errors['validation']) === 0) {
        $email =  $validated_data['email'];
        $password = $validated_data['password'];

        $login_result = login_user($con, $email, $password);

        if ($login_result['result']) {
            header('Location: index.php');
        } else {
            $errors['validation'] = $login_result['errors']['validation'];
            $errors['fatal'] = array_merge($errors['fatal'], $added_user['errors']['fatal']);
        }
    }
}

$con = null;

$content = include_template('login.php', [ 
                                            'form_data' => $form_data,
                                            'errors' => $errors['validation'],
                                            'stuff_categories' => $stuff_categories, 
                                         ]);

$layout = include_template('layout.php', [
                                          'title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => is_auth(), 
                                          'user_name' => $user_name
                                         ]);

print($layout);

// Функции. 

// Вход пользователя.
function login_user($con, $email, $password)
{
    $errors = [ 'validation' => [], 'fatal' => [] ];
    $error_msg = 'Неверный логин и/или пароль.';

    $user_data = db_func\get_userdata_by_email($con, $email);

    if (empty($user_data['error'])) {
        $password_from_db = $user_data['result']['password'];

        $password_correct = password_verify($password, $password_from_db);

        if ($password_correct) {
            $user_name = $user_data['result']['name'];
            $user_id = $user_data['result']['id'];

            // Сохраняем данные пользователя в сессии.
            save_user_data($user_name, $user_id);
        } else {
            $errors['validation']['email'] = $error_msg ;
            $errors['validation']['password'] = $error_msg ;
        }
   } else {
        $errors['validation']['email'] = $error_msg ;
        $errors['validation']['password'] = $error_msg ;
   }

   return ['errors' => $errors, 'result' => empty($errors['validation']) && empty($errors['fatal'])];
}   

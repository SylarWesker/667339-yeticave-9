<?php

require_once('auth.php');
require_once('helpers.php');
require_once('utils/utils.php');
require_once('utils/db_helper.php');

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
    if (count($errors['validation']) === 0) {
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
            // Ошибки могут быть как и валидации - уже есть пользователь/email
            // так и фатальные - работа с БД.
            $errors['validation'] = $added_user['errors']['validation'];
            $errors['fatal'] = array_merge($errors['fatal'], $added_user['errors']['fatal']);
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
                                            'is_auth' => is_auth(), 
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

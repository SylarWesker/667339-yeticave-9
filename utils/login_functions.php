<?php
require_once('auth.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// Функции для сценария login.php

// Вход пользователя.
/**
 * login_user - позволяет залогинится пользователю по почте и паролю.
 *
 * @param  mixed $con - соединение с БД
 * @param  string $email - электронная почта пользователя.
 * @param  string $password - пароль пользователя.
 *
 * @return array (для примера назовем $arr)
 * $arr['result'] - возвращает true, если пользователь успешно залогинился.
 * $arr['errors'] - массив с фатальными ошибками и ошибками валидации.
 */
function login_user($con, string $email, string $password)
{
    $errors = [ 'validation' => [], 'fatal' => [] ];
    $error_msg = 'Неверный логин и/или пароль.';

    $func_result = db_func\get_userdata_by_email($con, $email);
    $user_data = $func_result['result'];
    $func_error = $func_result['error'];
 
    if (!is_null($user_data) && empty($func_error)) {
        $password_from_db = $user_data['password'];

        $password_correct = password_verify($password, $password_from_db);

        if ($password_correct) {
            $user_name = $user_data['name'];
            $user_id = $user_data['id'];

            // Сохраняем данные пользователя в сессии.
            save_user_data($user_name, $user_id);
        } else {
            $errors['validation']['email'] = $error_msg;
            $errors['validation']['password'] = $error_msg;
        }
   } else {
        $errors['validation']['email'] = $error_msg;
        $errors['validation']['password'] = $error_msg;

        $errors['fatal'] = $func_error;
   }

   return ['errors' => $errors, 'result' => empty($errors['validation']) && empty($errors['fatal'])];
}   

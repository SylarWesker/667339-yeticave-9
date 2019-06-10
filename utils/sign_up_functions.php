<?php

require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// Функции для работы сценария sign-up.php

// Функция регистрации (добавления) пользователя.
/**
 * register_user
 *
 * @param  mixed $con - подключение к БД.
 * @param  string $email - электронная почта пользователя.
 * @param  string $user_name - имя/никнэйм пользователя.
 * @param  string $password - пароль пользователя.
 * @param  string $contacts - контактные данные пользователя.
 *
 * @return array (для примера назовем $arr)
 * $arr['user_id'] - возвращает идентификатор зарегистрированого пользователя. null - если пользователя не зарегистрировали.
 * $arr['errors'] - массив с фатальными ошибками и ошибками валидации. 
 */
function register_user($con, string $email, string $user_name, string $password, string $contacts)
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

<?php

session_start();

$user_name = get_user_name();
$user_id = get_user_id();
$is_auth = is_auth();

/**
 * is_auth - возвращает истину если пользователь авторизован.
 *
 * @return bool
 */
function is_auth(): bool
{
    return !(empty($_SESSION['user']['name']) &&
        empty($_SESSION['user']['id']));
}

/**
 * get_user_name - возвращает имя пользователя.
 *
 * @return string
 */
function get_user_name(): string
{
    $user_name = '';

    if (is_auth()) {
        $user_name = $_SESSION['user']['name'];
    }

    return $user_name;
}

/**
 * get_user_id - возвращает идентификатор пользователя (его id в БД).
 *
 * @return void
 */
function get_user_id()
{
    $user_id = null;

    if (is_auth()) {
        $user_id = $_SESSION['user']['id'];
    }

    return $user_id;
}

/**
 * save_user_data - сохраняет данные пользователя в сессии.
 *
 * @param string $user_name - имя пользователя.
 * @param int $user_id - идентификатор пользователя.
 *
 * @return void
 */
function save_user_data(string $user_name, int $user_id)
{
    $_SESSION['user'] = [
        'name' => $user_name,
        'id' => $user_id
    ];
}

/**
 * delete_user_data - удаляет данные пользователя из сессии.
 *
 * @return void
 */
function delete_user_data()
{
    unset($_SESSION['user']);
}
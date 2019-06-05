<?php

namespace yeticave\db\functions;

// ToDo
// Разделить функции общей работы с БД и функции работы с конкретной БД (yeticave)

// ToDo
// Проверить все функции работы с БД на предмет поведения при ошибке (нет соединения, неверный sql запрос)

// ToDo
// Дата окончания аукциона - это 23:59:59 указанной даты 
// Учесть и исправить.

// ToDo
// К предыдущему ToDo - обратить внимание что в запросах использую NOW(). now - возвращает текущую дату и время
// CURRENT_DATE() - только текущую дату.

// ToDo
// Внимательно проверить все используемые JOIN (почему и зачем использую full, left, right)

// Возвращает подключение к БД.
function get_connection()
{
    $db_params = require_once(dirname(__FILE__) . '/../db_config.php');

    $con = mysqli_connect($db_params['host'], 
                          $db_params['user'], 
                          $db_params['password'], 
                          $db_params['db_name']);

    return $con;
}

// Установить кодировки соединения с БД.
function set_charset($con)
{
    mysqli_set_charset($con, "utf8");
}

function get_data_by_field($con, $table_name, $field_name, $field_value, $limit = null) 
{
    $sql_limit_part = $limit ? " LIMIT $limit" : '';

    $sql = "SELECT * FROM `$table_name` WHERE `$field_name` = ? $sql_limit_part";
    $result_data = db_fetch_data($con, $sql, [ $field_value ]);

    return $result_data;
}

function filter($con, $table_name, $field_name, $field_value, $limit = null) 
{
    $result_data = get_data_by_field($con, $table_name, $field_name, $field_value, $limit);

    $result = false;

    if ($result_data['error'] !== null) {
        $result = false;
    } else {
        $result = !empty($result_data['result']);
    }

    return $result;
}

// Вспомогательная функция
// Формирует подстановочные знаки для параметров в подготовленных запросах.
function create_placeholders_for_prepared_query($count, $placeholder = '?')
{
    $query_placeholders = array_fill(0, $count, '?');
    $query_placeholders_str = implode(', ', $query_placeholders);

    return $query_placeholders_str;
}

// Получить последнюю ошибку при работе с БД.
function get_last_db_error($con)
{
    return mysqli_error($con);
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = []) {
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

// ToDo
// Разобраться и определится буду ли запоминать ошибки при работе с БД и пробрасывать их наверх
// или просто буду die()

// Вспомогательная функция получения записей.
function db_fetch_data($link, $sql, $data = [])
{
    $result = null;
    $error = null;

    $stmt = db_get_prepare_stmt($link, $sql, $data);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res) {
        $result = mysqli_fetch_all($res, MYSQLI_ASSOC);
    } else {
        // Слабое место. до этого выполнялось несколько функций и при их выполнении тоже могли быть ошибки, а мы записываем только последнюю.
        $error = get_last_db_error($link);
    }

    return ['result' => $result, 'error' => $error];
}

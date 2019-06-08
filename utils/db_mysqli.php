<?php

namespace yeticave\db\functions;

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
    $query_placeholders = array_fill(0, $count, $placeholder);
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
    }

    return $stmt;
}

// Вспомогательная функция получения записей.
function db_fetch_data($link, $sql, $data = [])
{
    $result = null;
    $error = null;

    try {
        $stmt = db_get_prepare_stmt($link, $sql, $data);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
    
        $result = mysqli_fetch_all($res, MYSQLI_ASSOC);
    } catch(\mysqli_sql_exception $e) {
        $error = $e->getMessage();
    }
   
    return ['result' => $result, 'error' => $error];
}

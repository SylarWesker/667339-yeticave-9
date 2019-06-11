<?php

namespace yeticave\db\functions;

/**
 * get_connection - Возвращает подключение к БД.
 *
 * @return mixed
 */
function get_connection()
{
    $db_params = require_once(dirname(__FILE__) . '/../db_config.php');

    $con = mysqli_connect($db_params['host'],
        $db_params['user'],
        $db_params['password'],
        $db_params['db_name']);

    return $con;
}

/**
 * set_charset - Установка кодировки соединения с БД.
 *
 * @param mixed $con - соединение с БД.
 *
 * @return void
 */
function set_charset($con)
{
    mysqli_set_charset($con, "utf8");
}

/**
 * get_data_by_field - функция получения данных по значению определенного поля.
 *
 * @param mixed $con - подключение к БД.
 * @param string $table_name - имя таблицы.
 * @param string $field_name - название поля (колонки).
 * @param mixed $field_value - значение поля.
 * @param int|null $limit - лимит кол-ва возвращаемых записей (null - если без ограничений).
 *
 * @return array
 */
function get_data_by_field($con, string $table_name, string $field_name, $field_value, $limit = null)
{
    $sql_limit_part = $limit ? " LIMIT $limit" : '';

    $sql = "SELECT * FROM `$table_name` WHERE `$field_name` = ? $sql_limit_part";
    $result_data = db_fetch_data($con, $sql, [$field_value]);

    return $result_data;
}

/**
 * filter - Вспомогательная функция проверяющая есть ли данные в БД.
 *
 * @param mixed $con - подключение к БД.
 * @param string $table_name - имя таблицы.
 * @param string $field_name - название поля (колонки).
 * @param mixed $field_value - значение поля.
 * @param int|null $limit - лимит кол-ва возвращаемых записей (null - если без ограничений).
 *
 * @return bool
 */
function filter($con, string $table_name, string $field_name, $field_value, $limit = null): bool
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

/**
 * create_placeholders_for_prepared_query - Формирует подстановочные знаки для параметров в подготовленных запросах.
 *
 * @param int $count - кол-во параметров для которых формируем знаки.
 * @param string $placeholder - символ подстановочного знака.
 *
 * @return string
 */
function create_placeholders_for_prepared_query(int $count, string $placeholder = '?'): string
{
    $query_placeholders = array_fill(0, $count, $placeholder);
    $query_placeholders_str = implode(', ', $query_placeholders);

    return $query_placeholders_str;
}

/**
 * get_last_db_error - Возвращает последнюю ошибку при работе с БД.
 *
 * @param mixed $con - подключение к БД.
 *
 * @return void
 */
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
function db_get_prepare_stmt($link, $sql, $data = [])
{
    $stmt = mysqli_prepare($link, $sql);

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            } else {
                if (is_string($value)) {
                    $type = 's';
                } else {
                    if (is_double($value)) {
                        $type = 'd';
                    }
                }
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

/**
 * db_fetch_data - функция получения записей.
 *
 * @param mixed $link - подключение к БД.
 * @param string $sql - текст sql запроса.
 * @param array $data - данные для подставновки в запрос (пустой массив если данные для выполнения запроса не требуются).
 *
 * @return array (для примера назовем $arr)
 * $arr['result'] - массив с данными, полученными при выполнении запроса.
 * $arr['error'] - текст ошибки.
 */
function db_fetch_data($link, $sql, $data = [])
{
    $result = null;
    $error = null;

    try {
        $stmt = db_get_prepare_stmt($link, $sql, $data);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);

        if (\is_bool($res)) {
            if ($res === false) {
                $error = get_last_db_error($link);
            }
        } else {
            $result = mysqli_fetch_all($res, MYSQLI_ASSOC);
        }
    } catch (\mysqli_sql_exception $e) {
        $error = $e->getMessage();
    }

    return ['result' => $result, 'error' => $error];
}

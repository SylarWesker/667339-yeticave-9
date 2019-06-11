<?php

require_once('helpers.php');

/**
 * strip_tags_for_array - Удаляет тэги в массиве. Возвращает измененную копию.
 *
 * @param array $arr_data - массив с данными от пользователя.
 * @param bool $recursive - рекурсивно обрабатывать массив (если элемент массива сам является массивом, то тоже делает и в нем).
 *
 * @return array
 */
function strip_tags_for_array($arr_data, $recursive = false): array
{
    foreach ($arr_data as $key => $value) {
        if (is_array($value)) {
            if ($recursive) {
                $arr_data[$key] = strip_tags_for_array($value, $recursive);
            }
        } elseif (is_string($value)) {
            $arr_data[$key] = strip_tags($value);
        }
    }

    return $arr_data;
}

/**
 * is_equal_or_less_hour_beetween_dates - Из $date2 вычитает $date1 и проверяет меньше ли часа между ними.
 *
 * @param mixed $date2 - вторая дата
 * @param mixed $date1 - первая дата
 *
 * @return bool
 */
function is_equal_or_less_hour_beetween_dates($date2, $date1): bool
{
    if (is_string($date2)) {
        $date2 = new DateTime($date2);
    }

    if (is_string($date1)) {
        $date1 = new DateTime($date1);
    }

    $date_diff = $date1->diff($date2);

    return is_equal_or_less_hour($date_diff);
}

/**
 * is_equal_or_less_hour - Возвращает истину, если в интервале один час или меньше.
 *
 * @param DateInterval $date_interval - разница между дата (интервал времени).
 *
 * @return bool
 */
function is_equal_or_less_hour($date_interval): bool
{
    if ($date_interval->invert === 1) {
        return false;
    }

    return is_less_hour($date_interval) || is_equal_hour($date_interval);
}

/**
 * is_only_time_part_has - Возвращает истину, если в интервале времени есть данные только о времени.
 *
 * @param DateInterval $date_interval - разница между дата (интервал времени).
 *
 * @return bool
 */
function is_only_time_part_has($date_interval): bool
{
    $result = $date_interval->y === 0 &&
        $date_interval->m === 0 &&
        $date_interval->d === 0;

    return $result;
}

/**
 * is_less_hour - Определяет меньше ли часа интервал времени.
 *
 * @param DateInterval $date_interval - разница между дата (интервал времени).
 *
 * @return bool
 */
function is_less_hour($date_interval): bool
{
    $result = $date_interval->h === 0;
    $result = is_only_time_part_has($date_interval) && $result;

    return $result;
}

/**
 * is_equal_hour - Определяет строго ли равен одному часу интервал времени.
 *
 * @param DateInterval $date_interval - разница между дата (интервал времени).
 *
 * @return bool
 */
function is_equal_hour($date_interval): bool
{
    $result = $date_interval->h === 1 &&
        $date_interval->i === 0 &&
        $date_interval->s === 0;
    $result = is_only_time_part_has($date_interval) && $result;

    return $result;
}

/**
 * at_least_one_day_bigger - Если разница между датами больше хотя бы на 1 день, то возвращает true.
 *
 * @param DateInterval $date_interval - разница между дата (интервал времени).
 *
 * @return bool
 */
function at_least_one_day_bigger($date_interval): bool
{
    if ($date_interval->invert === 1) {
        return false;
    }

    return $date_interval->y >= 1 || $date_interval->m >= 1 || $date_interval->d >= 1;
}

/**
 * format_price - Возвращает форматирование представление стоимости вместе с знаком валюты.
 *
 * @param int $number - значение стоимости
 * @param string $currency_symbol - символ валюты
 *
 * @return string
 */
function format_price(int $number, string $currency_symbol = '₽'): string
{
    $number = ceil($number);

    if ($number >= 1000) {
        $number = number_format($number, 0, '.', ' ');
    }

    $result = $number . ' ' . $currency_symbol;
    return $result;
}

/**
 * secure_data_for_sql_query - Делает входные данные безопасными для использования в sql-запросах.
 *
 * @param mixed $param - значение полученное от пользователя.
 *
 * @return mixed
 */
function secure_data_for_sql_query($param)
{
    if (is_string($param)) {
        $param = trim($param);
        $param = strip_tags($param);

        $param = addslashes($param);
    }

    return $param;
}

/**
 * array_order_by_key - Меняет порядок элементов в массиве $array согласно порядку ключей в $ordered_keys.
 *
 * @param array $array - массив, который необходимо отсортировать по ключам.
 * @param array $ordered_keys - упорядоченный массив с ключами.
 *
 * @return void
 */
function array_order_by_key($array, $ordered_keys)
{
    $result = [];

    for ($i = 0; $i < count($array); $i++) {
        $key = $ordered_keys[$i];

        if (array_key_exists($key, $array)) {
            $result[] = $array[$key];
        }
    }

    return $result;
}

/**
 * show_form_data - Отображает данные с формы по ключу (если они есть).
 *
 * @param mixed $key - ключ в массиве $form_data (название поля на форме).
 * @param array $form_data - массив с данными взятыми из формы.
 * @param array $errors - массив с ошибками валидации для данных из формы.
 * Если передан массив и в нем есть ошибки по полю, то данные не выводятся.
 *
 * @return void
 */
function show_form_data($key, $form_data, $errors = null): string
{
    $result = '';

    $show = isset($form_data[$key]);

    // НЕ показываем если есть ошибка по этому полю.
    if (isset($errors)) {
        $show &= !isset($errors[$key]);
    }

    if ($show) {
        $result = $form_data[$key];
    }

    return $result;
}

/**
 * show_error - Отображает ошибки валидации поля (если они есть).
 *
 * @param mixed $key - ключ в массиве $errors (название поля на форме).
 * @param mixed $errors - массив с ошибками валидации для данных из формы.
 *
 * @return string
 */
function show_error($key, $errors): string
{
    $result = '';

    if (isset($errors[$key])) {
        $result = $errors[$key];
    }

    return $result;
}

// ToDo - $now зачем передавать?
// почему полночь использую?
/**
 * bet_date_create_format - Возвращает дату создания ставки в человекоудобном формате.
 *
 * @param mixed $now - сейчас.
 * @param mixed $date - дата ставки.
 *
 * @return string
 */
function bet_date_create_format($now, $date): string
{
    $result = null;

    if (is_string($date)) {
        $date = new DateTime($date);
    }

    $now_midnight = new DateTime($now->format('Y-m-d'));
    $date_midnight = new DateTime($date->format('Y-m-d'));

    $date_diff_with_time = $date->diff($now);
    $date_diff = $date_midnight->diff($now_midnight); // при расчете 'вчера' нужно не использовать время. 

    // сегодня 03.01.2018
    // вчера это 02.01.2018 00:00:00 - 02.01.2018 23:59:59

    // если разница между датой создания ставки и сейчас больше одного дня, то выводим в формате '19.03.17 в 08:21'
    if ($date_diff->d > 1) {
        $result = $date->format('d.m.y') . ' в ' . $date->format('H:i');
    } elseif ($date_diff->d === 1) {
        $result = 'Вчера, в ' . $date->format('H:i');
    } else {
        $hours_ago = get_noun_plural_form_with_number($date_diff_with_time->h, 'час', 'часа', 'часов');
        $minutes_ago = get_noun_plural_form_with_number($date_diff_with_time->i, 'минута', 'минуты', 'минут');

        $result = $hours_ago . ' ' . $minutes_ago . ' назад';
    }

    return $result;
}

/**
 * get_noun_plural_form_with_number - Просто обертка над get_noun_plural_form.
 * Возвращает корректную форму во множественном числе, но впереди число.
 *
 * см. функцию get_noun_plural_form
 *
 * @param int $number - число.
 * @param string $one - сущность в единственном числе.
 * @param string $two - сущность в 2-х экземплярах.
 * @param string $many - сущность во множетсвом числе.
 *
 * @return string
 */
function get_noun_plural_form_with_number($number, $one, $two, $many): string
{
    $result = '';

    if ($number !== 0) {
        $result = $number . ' ' . get_noun_plural_form($number, $one, $two, $many);
    }

    return $result;
}

// ToDo - $now зачем передавать?
// 
/**
 * time_to_lot_end_format - Возвращает форматированное время до окончания торгов лота. Формат - часы:минуты.
 *
 * @param mixed $end_date - дата окончания аукциона по лоту.
 * @param mixed $now - сейчас. (вообще относительно какой даты)
 *
 * @return void
 */
function time_to_lot_end_format($end_date, $now)
{
    $result = null;

    if (is_string($end_date)) {
        $end_date = new DateTime($end_date);
    }

    $date_diff = $now->diff($end_date);

    // Месяц при разнице дат всегда равен 30 дням (при разнице DateTime с помощью функции diff).
    $hours_sum = $date_diff->h + $date_diff->d * 24 + $date_diff->m * 30 * 24;
    $result = $hours_sum . ':' . $date_diff->i;

    return $result;
}

/**
 * validate_form_field - Простая валидация поля из формы. Проверка на пустоту и отсечение тэгов, лишних пробелов, экранирование.
 *
 * @param string $field_name - имя поля.
 * @param mixed $field_value - значение поля.
 * @param array $error_messages - массив с текстами сообщений об ошибках валидации.
 * @param mixed $filter_option - параметр для функции filter_var.
 *
 * @return array (для примера назовем $arr)
 * $arr['is_valid'] - true если поле прошло валидацию.
 * $arr['field_value'] - значение поля, прошедшего валидацию (отсечение тэгов, лишних пробелов, экранирование).
 * $arr['error'] - текст об ошибке валидации.
 */
function validate_form_field($field_name, $field_value, $error_messages, $filter_option = null)
{
    $error = '';

    $field_value = secure_data_for_sql_query($field_value);

    if (!empty($filter_option)) {
        $field_value = filter_var($field_value, $filter_option);

        if (!$field_value) {
            $error = $error_messages['filter'];
        }
    } else {
        if (strlen($field_value) === 0) {
            $error = $error_messages['zero_length'];
        }
    }

    return [
        'is_valid' => empty($error),
        'field_value' => $field_value,
        'error' => $error
    ];
}

// ToDo тут неплохо было б предоставить пример массива $form_fields.
/**
 * validate_form_data - Функция валидации данных из формы.
 *
 * @param array $form_data - данные с формы.
 * @param array $form_fields - массив сложной структуры с параметрами валидации.
 *
 * @return void
 */
function validate_form_data($form_data, $form_fields)
{
    $errors = [];
    $validated_data = [];

    foreach ($form_fields as $field_name => $field_validate_data) {
        $field_value = $form_data[$field_name];

        $result_data = validate_form_field($field_name,
            $field_value,
            $field_validate_data['error_messages'],
            $field_validate_data['filter_option'] ?? null);

        if ($result_data['is_valid']) {
            $validated_data[$field_name] = $result_data['field_value'];
        } else {
            $validated_data[$field_name] = null;

            $errors[$field_name] = $result_data['error'];
        }
    }

    return ['data' => $validated_data, 'errors' => $errors];
}

/**
 * get_form_data - Возвращает данные пришедшие с формы от пользователя.
 *
 * @param array $form_field_names - массив имен полей формы из которых нужно собрать данные.
 *
 * @return array
 */
function get_form_data($form_field_names)
{
    $form_data = [];

    $raw_form_data = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw_form_data = $_POST;
    } else {
        $raw_form_data = $_GET;
    }

    foreach ($form_field_names as $field_name) {
        if (isset($raw_form_data[$field_name])) {
            $form_data[$field_name] = $raw_form_data[$field_name];
        }
    }

    return $form_data;
}

// ToDo здесь и в show 500 использовать http_responce_code ???
/**
 * show_404 - Шаблон страницы с 404 ошибкой.
 *
 * @param array $errors - массив с ошибками.
 * @param array $categories - массив категорий лотов.
 * @param bool $is_auth - авторизирован ли пользователь.
 * @param string $user_name - имя пользователя.
 *
 * @return void
 */
function show_404($errors, $categories, $is_auth, $user_name)
{
    $title_page = 'Страница не найдена.';
    $content = include_template('404.php', [
        'error_list' => $errors,
        'stuff_categories' => $categories
    ]);

    $layout = include_template('layout.php', [
        'title' => $title_page,
        'content' => $content,
        'stuff_categories' => $categories,
        'is_auth' => $is_auth,
        'user_name' => $user_name
    ]);

    print($layout);
}

/**
 * show_500 - Шаблон страницы с 500 ошибкой.
 *
 * @param array $errors - массив с ошибками.
 * @param array $categories - массив категорий лотов.
 * @param bool $is_auth - авторизирован ли пользователь.
 * @param string $user_name - имя пользователя.
 *
 * @return void
 */
function show_500($errors, $categories, $is_auth, $user_name)
{
    $title_page = 'Ошибка сервера.';
    $content = include_template('500.php', [
        'error_list' => $errors,
        'stuff_categories' => $categories
    ]);

    $layout = include_template('layout.php', [
        'title' => $title_page,
        'content' => $content,
        'stuff_categories' => $categories,
        'is_auth' => $is_auth,
        'user_name' => $user_name
    ]);

    print($layout);
}

/**
 * get_href - Возвращает ссылку на страницу на сервере
 *
 * @param string $page_name - название страницы/скрипта на сервере.
 * @param array $url_params - параметры адреса страницы.
 *               пары ключ - значение. ключ - название параметра, значение - его значение
 *
 * @return string
 */
function get_href(string $page_name, $url_params): string
{
    $result = $page_name . '?' . http_build_query($url_params);

    return $result;
}

/**
 * get_max_page_number - Возвращает номер максимальной (последней) страницы.
 *
 * @param int $items_per_page - элементов на странице.
 * @param int $total_items - всего элементов, которые нужно вывести.
 *
 * @return int
 */
function get_max_page_number(int $items_per_page, int $total_items): int
{
    return intval(ceil($total_items / $items_per_page));
}

/**
 * correct_page_number - Корректирует номер страницы (ограничивает минимальным и максимальным значением).
 *
 * @param int $page_number - номер текущей страницы.
 * @param int $max_page - номер последней страницы.
 * @param int $min_page - номер первой страницы.
 *
 * @return int
 */
function correct_page_number(int $page_number, int $max_page, int $min_page = 1): int
{
    $page_number = max($min_page, $page_number);
    $page_number = min($max_page, $page_number);

    return $page_number;
}

/**
 * current_nav_class - Вспомогательная функция возвращающая css класс для выбранной категории (используется в навигации на странице all-lots.php)
 *
 * @param string $category_name - название категории.
 * @param string $current_category - текущая (выбранная категория).
 *
 * @return void
 */
function current_nav_class(string $category_name, string $current_category)
{
    $class_name = '';

    if ($category_name === $current_category) {
        $class_name = 'nav__item--current';
    }

    return $class_name;
}

/**
 * save_file_on_server - Перемещение картинки в папку на сервере (постоянную папку).
 *
 * @param string $tmp_file_path - путь к файл во временной папке на сервере.
 * @param string $file_name - оригинальное имя файл.
 * @param string $uploads_path - путь к папке куда нужно загрузить файл.
 *
 * @return void
 */
function save_file_on_server(string $tmp_file_path, string $file_name, string $uploads_path)
{
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_file_name = uniqid() . '.' . $extension;

    $new_file_path = $uploads_path . DIRECTORY_SEPARATOR . $new_file_name;

    move_uploaded_file($tmp_file_path, $new_file_path);

    return ['new_file_name' => $new_file_name, 'new_file_path' => $new_file_path];
}

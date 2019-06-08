<?php

require_once('helpers.php');

// Удаляет тэги в массиве (если элемент массива сам является массивом, то тоже делает и в нем).
// Возвращает измененную копию.
function strip_tags_for_array($arr_data, $recursive = false)
{
    foreach($arr_data as $key => $value)
    {
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

// Вспомогательная функция. 
// Из $date2 вычитает $date1 и проверяет меньше ли часа между ними.
function is_equal_or_less_hour_beetween_dates($date2, $date1)
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

// Возвращает истину, если в интервале один час или меньше.
function is_equal_or_less_hour($date_interval) 
{
    if ($date_interval->invert === 1)
        return false;
    
    return  is_less_hour($date_interval) || is_equal_hour($date_interval);
}

function is_only_time_part_has($date_interval)
{
    $result = $date_interval->y === 0 && 
              $date_interval->m === 0 && 
              $date_interval->d === 0;

    return $result;
}

function is_less_hour($date_interval)
{
    $result = $date_interval->h === 0;
    $result = is_only_time_part_has($date_interval) && $result;

    return $result;
}

function is_equal_hour($date_interval)
{
    $result = $date_interval->h === 1 && 
              $date_interval->i === 0 && 
              $date_interval->s === 0;
    $result = is_only_time_part_has($date_interval) && $result;

    return $result;
}

// Функция форматирования суммы заказа.
function format_price($number, $currency_symbol = '₽')
{
    $number = ceil($number);

    if ($number >= 1000) {
        $number = number_format($number, 0, '.', ' ');
    }

    $result = $number . ' ' . $currency_symbol;
    return $result;
}

function secure_data_for_sql_query($param)
{
    if (is_string($param)) {
        $param = trim($param);
        $param = strip_tags($param);

        $param = addslashes($param); 
    }

    return $param;
}

// Если разница между датами больше хотя бы на 1 день, то возвращает true.
function at_least_one_day_bigger($date_interval) {
    if ($date_interval->invert === 1)
        return false;
    
    return $date_interval->y >= 1 || $date_interval->m >= 1 || $date_interval->d >= 1;
}

// Меняет порядок элементов в массиве $array согласно порядку ключей в $ordered_keys.
function array_order_by_key($array, $ordered_keys) {
    $result = [];

    for ($i = 0; $i < count($array); $i++) 
    {
        $key = $ordered_keys[$i];

        if (array_key_exists($key, $array)) {
            $result[] = $array[$key];
        }
    }

    return $result;
}

function show_form_data($key, $form_data, $errors = null) 
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

function show_error($key, $errors) 
{
  $result = '';

  if(isset($errors[$key])) {
    $result = $errors[$key];
  }

  return $result;
}

// Форматирует дату создания ставки в человекоудобном формате.
function bet_date_create_format($now, $date)
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

function get_noun_plural_form_with_number($number, $one, $two, $many)
{
    $result = '';

    if ($number !== 0) {
        $result = $number . ' ' . get_noun_plural_form($number, $one, $two, $many);
    }

    return $result;
} 

// Форматирует время до окончания торгов лота.
function time_to_lot_end_format($end_date, $now) 
{
    $result = null;

    if (is_string($end_date)) {
        $end_date = new DateTime($end_date);
    }

    $date_diff = $now->diff($end_date);

    // Месяц при разнице дат всегда равен 30 дням (при разнице DateTime с помощью функции diff).
    $hours_sum = $date_diff->h + $date_diff->d * 24 +  $date_diff->m * 30 * 24;
    $result = $hours_sum .':' . $date_diff->i;

    return $result;
}

// Простая валидация данных из формы.
// Проверка на пустоту и отсечение тэгов, лишних пробелов, экранирование.
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

// Функция валидации полей формы
function validate_form_data($form_data, $form_fields) 
{
    $errors = [];
    $validated_data = [];

    foreach($form_fields as $field_name => $field_validate_data)
    {
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

// Функция сбора данных пришедших из формы. 
function get_form_data($form_field_names) 
{
    $form_data = [];

    $raw_form_data = [];
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $raw_form_data = $_POST;
    } else {
      $raw_form_data = $_GET;
    }

    foreach($form_field_names as $field_name) {
        if (isset($raw_form_data[$field_name])) {
            $form_data[$field_name] = $raw_form_data[$field_name];
        }
    }

    return $form_data;
}

// Расчет минимальной ставки на лот.
function get_lot_min_price($start_price, $current_price, $bet_step)
{
    $lot_min_price = $current_price;

    if ($current_price !== $start_price) {
        $lot_min_price += $bet_step;
    }

    return $lot_min_price;
}

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


// Использую в шаблоне пагинации.
// $url_params - пары ключ - значение. 
// ключ - название параметра, значение - его значение
function get_href($page_name, $url_params)
{
  $result = $page_name . '?' . http_build_query($url_params);
  
  return $result;
}

// Возвращает номер максимальной страницы.
// $total_items - всего элементов, которые нужно показать. 
// $items_per_page - максимум элементов на одной странице.
function get_max_page_number($items_per_page, $total_items)
{
    return intval(ceil($total_items / $items_per_page));
}

// Корректирует номер страницы (ограничивает минимальным и максимальным значением)
function correct_page_number($page_number, $max_page, $min_page = 1)
{
    $page_number = max($min_page, $page_number);
    $page_number = min($max_page, $page_number);

    return $page_number;
}

// Вспомогательная функция возвращающая css класс для выбранной категории (используется в навигации на странице all-lots.php)
function current_nav_class($category_name, $current_category)
{
    $class_name = '';

    if ($category_name == $current_category) {
        $class_name = 'nav__item--current';
    }

    return $class_name;
}

// Перемещение картинки в папку на сервере (постоянную папку)
function save_file_on_server($tmp_file_path, $file_name, $uploads_path)
{
    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_file_name = uniqid() . '.' . $extension;

    $new_file_path = $uploads_path . DIRECTORY_SEPARATOR . $new_file_name;

    move_uploaded_file($tmp_file_path, $new_file_path);

    return ['new_file_name' => $new_file_name, 'new_file_path' => $new_file_path];
}
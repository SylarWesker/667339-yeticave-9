<?php

require_once('helpers.php');
require_once('utils/utils.php');

$is_auth = rand(0, 1);

$title = 'Главная';

$user_name = 'Sylar'; // укажите здесь ваше имя

$stuff_categories = ["Доски и лыжи", "Крепления", "Ботинки", "Одежда", "Инструменты", "Разное"];

// По идее категории надо бы заменить на значения из массива $stuff_categories ("Доски и лыжи" на  $stuff_categories[0])
$lots = [
    [
        "name" => "2014 Rossignol District Snowboard",
        "category" => "<h1>Доски и лыжи</h1>",
        "price" => 10999,
        "image_url" => "img/lot-1.jpg"
    ],

    [
        "name" => "DC Ply Mens 2016/2017 Snowboard",
        "category" => "Доски и лыжи",
        "price" => 159999,
        "image_url" => "img/lot-2.jpg"
    ],

    [
        "name" => "Крепления Union Contact Pro 2015 года размер L/XL",
        "category" => "Крепления",
        "price" => 8000,
        "image_url" => "img/lot-3.jpg"
    ],

    [
        "name" => "Ботинки для сноуборда DC Mutiny Charocal",
        "category" => "Ботинки",
        "price" => 10999,
        "image_url" => "img/lot-4.jpg"
    ],

    [
        "name" => "Куртка для сноуборда DC Mutiny Charocal",
        "category" => "Одежда",
        "price" => 7500,
        "image_url" => "img/lot-5.jpg"
    ],

    [
        "name" => "Маска Oakley Canopy",
        "category" => "Разное",
        "price" => 5400,
        "image_url" => "img/lot-6.jpg"
    ],
];

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

// ToDo
// По идее нужно вынести header и footer в отдельные шаблоны.

// Убираем тэги из данных, которые якобы вводит пользователь.
$stuff_categories_filtered = strip_tags_for_array($stuff_categories, true);
$lots_filtered = strip_tags_for_array($lots, true);

// Формирование времени окончания дейтсвия лота
$date_now = new DateTime();
$today_midnight = date_create('tomorrow');
$time_to_midnight = $today_midnight->diff($date_now); // Время до полуночи (считаем что это время окончания "жизни" лота).

// ToDo!
// Сделать расчет (осталось меньше часа до полуночи) и формирование строки в нужном формате в этом файле.
// передавать в шаблон уже готовые данные
// Проблема в том, что в будущем будет не одна дата, а данные для каждого лота. 
// нужно будет или отдельные функции или объекты с методами.
// Upd: Пока сделал метод.

$content = include_template('index.php', ['stuff_categories' => $stuff_categories_filtered, 
                                          'lots' => $lots_filtered,
                                          'lot_lifetime_end' => $time_to_midnight]);

$layout = include_template('layout.php', ['title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories_filtered, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name]);

print($layout);

?>



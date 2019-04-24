<?php

require_once('helpers.php');
require_once('utils/utils.php');

$is_auth = rand(0, 1);

$title = 'Главная';

$user_name = 'Sylar'; // укажите здесь ваше имя

$stuff_categories = [];
$lots = [];

$host = "localhost";
$user = "yeticave_web";
$password = "123"; 
$db_name = "yeticave";

$con = mysqli_connect($host, $user, $password, $db_name);

if (!$con) {
    print('Ошибка подключения: ' . mysqli_connect_error());
} else {
    // print('Соединение уставлено!');

    mysqli_set_charset($con, "utf8");

    // список лотов.
    $sql = 'SELECT  l.name,
                    l.start_price, 
                    l.image_url, 
                    l.creation_date,
                    l.end_date,
                    cat.name category, 
                    l.description
                FROM lot as l
                JOIN stuff_category as cat on l.category_id = cat.id
                ORDER BY l.creation_date DESC';
    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        print('Ошибка MySql при получении лотов: ' . $error);
    } else {
        $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    // список категорий.
    $sql = 'SELECT * FROM stuff_category';
    $result = mysqli_query($con, $sql);

    if (!$result) {
        $error = mysqli_error($con);
        print('Ошибка MySql при получении списка категорий: ' . $error);
    } else {
        $stuff_categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

// ToDo
// По идее нужно вынести header и footer в отдельные шаблоны.

// Формирование времени окончания действия лота.
$date_now = new DateTime();
$today_midnight = new DateTime('tomorrow');

// Время до полуночи (считаем что это время окончания "жизни" лота).
$time_to_midnight = $today_midnight->diff($date_now); 

$content = include_template('index.php', ['stuff_categories' => $stuff_categories, 
                                          'lots' => $lots,
                                          'lot_lifetime_end' => $time_to_midnight]);

$layout = include_template('layout.php', ['title' => $title, 
                                          'content' => $content, 
                                          'stuff_categories' => $stuff_categories, 
                                          'is_auth' => $is_auth, 
                                          'user_name' => $user_name]);

print($layout);

?>



<?php

require_once('helpers.php');
require_once('utils/utils.php');

const DB_CON_TYPE = 'pdo'; // pdo or mysqli

if (DB_CON_TYPE === 'pdo') {
    require_once('utils/db_pdo.php');
    //use yeticave\db\pdo_functions as db_func;
} else if (DB_CON_TYPE === 'mysqli') {
    require_once('utils/db_mysqli.php');
    //use yeticave\db\mysqli_functions as db_func;
}

use yeticave\db\functions as db_func;

$is_auth = rand(0, 1);

$title = 'Главная';

$user_name = 'Sylar'; // укажите здесь ваше имя

$stuff_categories = [];
$lots = [];

$con = db_func\get_connection();

if (!$con) {
    print('Ошибка подключения к БД!');

    die('Ошибка подключения к БД!');
} 

// print('Соединение уставлено!');

db_func\set_charset($con);

// список лотов.
$func_result = db_func\get_lots($con);
$lots = $func_result['result'] === null ? [] : $func_result['result']; // стремно, но что поделать.

if ($func_result['error'] !== null) {
    print('Ошибка MySql при получении лотов: ' . $func_result['error']);  
}

// список категорий.
$func_result = db_func\get_stuff_categories($con);
$stuff_categories = $func_result['result'] === null ? [] : $func_result['result']; // стремно, но что поделать.

if ($func_result['error'] !== null) {
    print('Ошибка MySql при получении списка категорий: ' . $func_result['error']);  
}

$con = null;

// ToDo
// По идее нужно вынести header и footer в отдельные шаблоны.

// Формирование времени окончания действия лота.
$date_now = new DateTime();
$today_midnight = new DateTime('tomorrow');

// ToDo!
// Не учитываю того факта что время лота может уже истечь, но разница между датами все равно будет меньше или равна часу.
// проверять по идее нужно св-во invert.

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

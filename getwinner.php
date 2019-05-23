<?php

require_once('helpers.php');
require_once('utils/db_helper.php');

use yeticave\db\functions as db_func;

// Алгоритм работы:

// 1. Найти все лоты без победителей, дата истечения которых меньше или равна текущей дате.
// 2. Для каждого такого лота найти последнюю ставку.
// 3. Записать в лот победителем автора последней ставки.
// 4. Отправить победителю на email письмо — поздравление с победой.
$errors = [];

// Дата окончания торгов.
// Это или сегодня полночь или сегодня 23.59.59

// Пускай пока без времени.
$func_result = get_lots_without_winners($con);
$lots_without_winner = func_result['data'];

// Обновляем победителей.
set_lots_winners($con, $lots_without_winner);

// Рассылаем письма
foreach ($lots_without_winner as $lot_winner) {
   
}
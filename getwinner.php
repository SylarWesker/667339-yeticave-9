<?php

require_once('vendor/autoload.php');

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

// ToDo
// Проверить set_lots_winners. 
// Обновляем победителей.
set_lots_winners($con, $lots_without_winner);

// ToDo
// Реализовать функцию get_winners_info
// Получаем инфо победителя
$winner_info = get_winners_info($con, [] );


// ToDo
// Проверить отправку. 

// Рассылаем письма
$sender_email = 'keks@phpdemo.ru';
$sender_email_password = 'htmlacademy';
$email_server = 'phpdemo.ru';
$email_server_port = 25; 

$email_content_type = 'text/html';
$email_subject = 'Ваша ставка победила';
$sender_name = 'keks@phpdemo.ru';

// Create the Transport
$transport = (new Swift_SmtpTransport($email_server , $email_server_port))
  ->setUsername($sender_email)
  ->setPassword($sender_email_password);

// Create the Mailer using your created Transport
$mailer = new Swift_Mailer($transport);

foreach ($winner_info as $info) {
    // Формируем текст письма
    $email_text = include_template('email.php', [ 'user_name' => $info['user_name'], 
                                                  'lot_name' => $info['lot_name'],
                                                  'lot_id' => $info['lot_id']
    ]);

    $email_receiver = $info['email'];

    // Create a message
    $message = (new Swift_Message($email_subject))
    ->setFrom([$sender_email => $sender_name ])
    ->setTo([$email_receiver => $info['user_name'] ])
    ->setBody($email_text);

    // Send the message
    $result = $mailer->send($message);
}









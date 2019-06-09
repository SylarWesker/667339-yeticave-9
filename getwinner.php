<?php

require_once('utils/error_report.php');

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

$func_result = db_func\get_lots_without_winners($con);
$lots_without_winner = $func_result['result'];

if (!empty($func_result['error'])) {
  $errors[] = $func_result['error'];
}

// Если нет победителей, то выполнять последующий код бессмыслено
if (empty($lots_without_winner)) {
  return;
}

// Обновляем победителей.
$func_result = db_func\set_lots_winners($con, $lots_without_winner);
$winners_id = $func_result['result'];

// Получаем инфо победителя
$func_result = db_func\get_winners_info($con, $winners_id);
$winners_info = $func_result['result'];

if (!empty($func_result['error'])) {
  $errors[] = $func_result['error'];
}

// Рассылаем письма
$sender_email = 'keks@phpdemo.ru';
$sender_email_password = 'htmlacademy';
$email_server = 'phpdemo.ru';
$email_server_port = 25; 

$email_content_type = 'text/html';
$email_subject = 'Ваша ставка победила';
$sender_name = 'keks@phpdemo.ru';

$transport = (new Swift_SmtpTransport($email_server , $email_server_port))
  ->setUsername($sender_email)
  ->setPassword($sender_email_password);

$mailer = new Swift_Mailer($transport);

foreach ($winners_info as $info) {
    // Формируем текст письма
    $email_text = include_template('email.php', [ 'user_name' => $info['user_name'], 
                                                  'lot_name'  => $info['lot_name'],
                                                  'lot_id'    => $info['lot_id']
    ]);

    $email_receiver = $info['user_email'];

    $message = (new Swift_Message($email_subject))
      ->setFrom([$sender_email => $sender_name])
      ->setTo([$email_receiver => $info['user_name']])
      ->setBody($email_text, $email_content_type);

    $result = $mailer->send($message);
}

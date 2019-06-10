<?php
    $db_params = require_once(dirname(__FILE__) . '/../db_config.php');

    // Текущие имя сервера у меня -  $server_name = 'yeticave.localhost';
    if (empty($db_params['server_name'])) {
        $server_name = 'localhost';
    } else {
        $server_name = $db_params['server_name'];
    }  
?>

<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?= $user_name ?></p>
<p>Ваша ставка для лота <a href="<?=$server_name?>/lot.php?id=<?= $lot_id ?>"><?= $lot_name ?></a> победила.</p>
<p>Перейдите по ссылке <a href="<?=$server_name?>/my-bets.php">мои ставки</a>,
чтобы связаться с автором объявления</p>
<small>Интернет Аукцион "YetiCave"</small>

<?php
    // Текущие имя сервера у меня -  $server_name = 'yeticave.localhost';
    $server_name = $_SERVER['SERVER_NAME'];
?>

<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?= $user_name ?></p>
<p>Ваша ставка для лота <a href="<?=$server_name?>/lot.php?id=<?= $lot_id ?>"><?= $lot_name ?></a> победила.</p>
<p>Перейдите по ссылке <a href="<?=$server_name?>/my-bets.php">мои ставки</a>,
чтобы связаться с автором объявления</p>
<small>Интернет Аукцион "YetiCave"</small>

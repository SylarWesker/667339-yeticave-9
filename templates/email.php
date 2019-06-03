<?php
    // Добавить в пути ссылку на адрес сервера (имя сервера)
    // yeticave.localhost

    // $server_name = $_SERVER['SERVER_NAME']; // в документации сказано, что нельзя доверять... Почему?
    $server_name = 'yeticave.localhost';
?>

<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?= $user_name ?></p>
<p>Ваша ставка для лота <a href="<?=$server_name?>/lot.php?id=<?= $lot_id ?>"><?= $lot_name ?></a> победила.</p>
<p>Перейдите по ссылке <a href="<?=$server_name?>/my-bets.php">мои ставки</a>,
чтобы связаться с автором объявления</p>
<small>Интернет Аукцион "YetiCave"</small>

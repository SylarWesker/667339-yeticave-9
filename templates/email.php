<?php
    // ToDo
    // Добавить в пути ссылку на адрес сервера (имя сервера)
    // yeticave.localhost
?>

<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?= $user_name ?></p>
<p>Ваша ставка для лота <a href="lot.php?id=<?= $lot_id ?>"><?= $lot_name ?></a> победила.</p>
<p>Перейдите по ссылке <a href="my-bets.php">мои ставки</a>,
чтобы связаться с автором объявления</p>
<small>Интернет Аукцион "YetiCave"</small>

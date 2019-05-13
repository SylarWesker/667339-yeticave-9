<?php

require_once('utils/utils.php');

?>

<form class="lot-item__form" action="add-bet.php" method="post" autocomplete="off">
    <input type="hidden" name="lot_id" value="<?= $lot['id']; ?>">

    <p class="lot-item__form-item form__item">  <!-- form__item--invalid -->
        <label for="cost">Ваша ставка</label>
        <input id="cost" type="text" name="cost" placeholder="<?= format_price($lot_min_price) ?>">
        <span class="form__error">Введите наименование лота</span>
    </p>
    <button type="submit" name="submit" class="button">Сделать ставку</button>
</form>

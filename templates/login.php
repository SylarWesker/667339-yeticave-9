<?php

require_once('utils/utils.php');

$has_errors = count($errors) > 0;

$form_invalid_class = 'form--invalid';
$form_item_invalid_class = 'form__item--invalid';

$navigation = include_template('navigate.php', ['stuff_categories' => $stuff_categories]);
?>

<main>
    <?= $navigation; ?>

    <form class="form container <?php if ($has_errors) {
        echo $form_invalid_class;
    } ?>" action="login.php" method="post">
        <h2>Вход</h2>
        <div class="form__item <?php if (isset($errors['email'])) {
            echo $form_item_invalid_class;
        } ?>">
            <label for="email">E-mail <sup>*</sup></label>
            <input id="email" type="text" name="email" placeholder="Введите e-mail"
                   value="<?= show_form_data('email', $form_data); ?>">
            <span class="form__error"><?= show_error('email', $errors); ?></span>
        </div>
        <div class="form__item form__item--last <?php if (isset($errors['password'])) {
            echo $form_item_invalid_class;
        } ?>">
            <label for="password">Пароль <sup>*</sup></label>
            <input id="password" type="password" name="password" placeholder="Введите пароль">
            <span class="form__error"><?= show_error('password', $errors); ?></span>
        </div>
        <button type="submit" name="submit" class="button">Войти</button>
    </form>
</main>

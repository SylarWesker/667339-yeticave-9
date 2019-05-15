<?php
require_once('utils/utils.php');

$has_errors = count($errors) > 0;

$form_invalid_class = 'form--invalid';
$form_item_invalid_class = 'form__item--invalid';

$navigation = include_template('navigate.php', [ 'stuff_categories' => $stuff_categories ]);
?>

<main>
  <?= $navigation; ?>

  <form class="form container <?php if($has_errors) echo $form_invalid_class; ?>" action="sign-up.php" method="post" autocomplete="off"> <!-- form--invalid -->
    <h2>Регистрация нового аккаунта</h2>
    <div class="form__item <?php if(isset($errors['email'])) echo $form_item_invalid_class; ?>"> <!-- form__item--invalid -->
      <label for="email">E-mail <sup>*</sup></label>
      <input id="email" type="text" name="email" placeholder="Введите e-mail" value="<?= show_form_data('email', $form_data, $errors); ?>">
      <span class="form__error"><?= show_error('email', $errors); ?></span>
    </div>
    <div class="form__item <?php if(isset($errors['password'])) echo $form_item_invalid_class; ?>">
      <label for="password">Пароль <sup>*</sup></label>
      <input id="password" type="password" name="password" placeholder="Введите пароль">
      <span class="form__error"><?= show_error('password', $errors); ?></span>
    </div>
    <div class="form__item <?php if(isset($errors['name'])) echo $form_item_invalid_class; ?>">
      <label for="name">Имя <sup>*</sup></label>
      <input id="name" type="text" name="name" placeholder="Введите имя" value="<?= show_form_data('name', $form_data, $errors); ?>">
      <span class="form__error"><?= show_error('name', $errors); ?></span>
    </div>
    <div class="form__item <?php if(isset($errors['message'])) echo $form_item_invalid_class; ?>">
      <label for="message">Контактные данные <sup>*</sup></label>
      <textarea id="message" name="message" placeholder="Напишите как с вами связаться"><?= show_form_data('message', $form_data, $errors); ?></textarea>
      <span class="form__error"><?= show_error('message', $errors); ?></span>
    </div>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <button type="submit" name="submit" class="button">Зарегистрироваться</button>
    <a class="text-link" href="#">Уже есть аккаунт</a>
  </form>
</main>

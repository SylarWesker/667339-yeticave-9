<?php

require_once('utils/utils.php');

$has_errors = count($errors) > 0;

$form_invalid_class = 'form--invalid';
$form_item_invalid_class = 'form__item--invalid';

?>

<main>
  <nav class="nav">
    <ul class="nav__list container">
      <li class="nav__item">
        <a href="all-lots.html">Доски и лыжи</a>
      </li>
      <li class="nav__item">
        <a href="all-lots.html">Крепления</a>
      </li>
      <li class="nav__item">
        <a href="all-lots.html">Ботинки</a>
      </li>
      <li class="nav__item">
        <a href="all-lots.html">Одежда</a>
      </li>
      <li class="nav__item">
        <a href="all-lots.html">Инструменты</a>
      </li>
      <li class="nav__item">
        <a href="all-lots.html">Разное</a>
      </li>
    </ul>
  </nav>

  <form class="form form--add-lot container <?php if($has_errors) echo $form_invalid_class; ?>" action="add.php" method="post" enctype="multipart/form-data"> <!-- form--invalid -->
    <h2>Добавление лота</h2>
    <div class="form__container-two">
      <div class="form__item <?php if(isset($errors['lot-name'])) echo $form_item_invalid_class; ?>"> <!-- form__item--invalid -->
        <label for="lot-name">Наименование <sup>*</sup></label>
        <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота" value="<?= show_form_data('lot-name', $form_data, $errors); ?>">
        <span class="form__error"><?= show_error('lot_name', $errors); ?></span>
      </div>
      <div class="form__item <?php if(isset($errors['category'])) echo $form_item_invalid_class; ?>">
        <label for="category">Категория <sup>*</sup></label>
        <select id="category" name="category">
          <?php foreach($stuff_categories as $category): ?>
            <option <?php if ($category['name'] === show_form_data('category', $form_data, $errors)) echo 'selected';?>><?= $category['name']; ?></option>
          <?php endforeach; ?>
        </select>
        <span class="form__error"><?= show_error('category', $errors); ?></span>
      </div>
    </div>
    <div class="form__item form__item--wide <?php if(isset($errors['message'])) echo $form_item_invalid_class; ?>">
      <label for="message">Описание <sup>*</sup></label>
      <textarea id="message" name="message" placeholder="Напишите описание лота"><?= show_form_data('message', $form_data, $errors); ?></textarea>
      <span class="form__error"><?= show_error('message', $errors); ?></span>
    </div>
    <div class="form__item form__item--file <?php if(isset($errors['lot-img'])) echo $form_item_invalid_class; ?>">
      <label>Изображение <sup>*</sup></label>
      <div class="form__input-file">
        <input class="visually-hidden" type="file" id="lot-img" name='lot-img' value="">
        <label for="lot-img">
          Добавить
        </label>
      </div>
      <span class="form__error"><?= show_error('lot-img', $errors); ?></span>
    </div>
    <div class="form__container-three">
      <div class="form__item form__item--small <?php if(isset($errors['lot-rate'])) echo $form_item_invalid_class; ?>">
        <label for="lot-rate">Начальная цена <sup>*</sup></label>
        <input id="lot-rate" type="text" name="lot-rate" placeholder="0" value="<?= show_form_data('lot-rate', $form_data, $errors); ?>">
        <span class="form__error"><?= show_error('lot-rate', $errors); ?></span>
      </div>
      <div class="form__item form__item--small <?php if(isset($errors['lot-step'])) echo $form_item_invalid_class; ?>">
        <label for="lot-step">Шаг ставки <sup>*</sup></label>
        <input id="lot-step" type="text" name="lot-step" placeholder="0" value="<?= show_form_data('lot-step', $form_data, $errors); ?>">
        <span class="form__error"><?= show_error('lot-step', $errors); ?></span>
      </div>
      <div class="form__item <?php if(isset($errors['lot-date'])) echo $form_item_invalid_class; ?>">
        <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
        <input class="form__input-date" id="lot-date" type="text" name="lot-date" placeholder="Введите дату в формате ГГГГ-ММ-ДД" value="<?= show_form_data('lot-date', $form_data, $errors); ?>">
        <span class="form__error"><?= show_error('lot-date', $errors); ?></span>
      </div>
    </div>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <button type="submit" name='submit' class="button">Добавить лот</button>
  </form>
</main>

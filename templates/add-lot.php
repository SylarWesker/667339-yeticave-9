<?php
  $has_errors = count($errors) > 0;

  // ToDo
  // Подумать над добавлением классов ошибки и выводом сообщения об ошибке.
  // бесит повторение if (isset(массив['ключ'])) echo класс;
  /* хочу <?= echo функция(параметр) ?> */
  // - вынести в функцию? много параметров отдавать тогда - массив ошибок, ключ, класс ошибки css
  // - ???
  $form_invalid_class = 'form--invalid';
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
  <form class="form form--add-lot container <?php if($has_errors) echo $form_invalid_class; ?>" action="add.php" method="post"> <!-- form--invalid -->
    <h2>Добавление лота</h2>
    <div class="form__container-two">
      <div class="form__item <?php if(isset($errors['lot-name'])) echo $form_invalid_class; ?>"> <!-- form__item--invalid -->
        <label for="lot-name">Наименование <sup>*</sup></label>
        <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота">
        <span class="form__error"><?php if(isset($errors['lot-name'])) echo $errors['lot-name']; ?></span>
      </div>
      <div class="form__item <?php if(isset($errors['category'])) echo $form_invalid_class; ?>">
        <label for="category">Категория <sup>*</sup></label>
        <select id="category" name="category">
          <?php foreach($stuff_categories as $category): ?>
            <option><?= $category['name']; ?></option>
          <?php endforeach; ?>
        </select>
        <span class="form__error"><?php if(isset($errors['category'])) echo $errors['category']; ?></span>
      </div>
    </div>
    <div class="form__item form__item--wide <?php if(isset($errors['message'])) echo $form_invalid_class; ?>">
      <label for="message">Описание <sup>*</sup></label>
      <textarea id="message" name="message" placeholder="Напишите описание лота"></textarea>
      <span class="form__error"><?php if(isset($errors['message'])) echo $errors['message']; ?></span>
    </div>
    <div class="form__item form__item--file">
      <label>Изображение <sup>*</sup></label>
      <div class="form__input-file">
        <input class="visually-hidden" type="file" id="lot-img" value="">
        <label for="lot-img">
          Добавить
        </label>
      </div>
    </div>
    <div class="form__container-three">
      <div class="form__item form__item--small <?php if(isset($errors['lot-rate'])) echo $form_invalid_class; ?>">
        <label for="lot-rate">Начальная цена <sup>*</sup></label>
        <input id="lot-rate" type="text" name="lot-rate" placeholder="0">
        <span class="form__error"><?php if(isset($errors['lot-rate'])) echo $errors['lot-rate']; ?></span>
      </div>
      <div class="form__item form__item--small <?php if(isset($errors['lot-step'])) echo $form_invalid_class; ?>">
        <label for="lot-step">Шаг ставки <sup>*</sup></label>
        <input id="lot-step" type="text" name="lot-step" placeholder="0">
        <span class="form__error"><?php if(isset($errors['lot-step'])) echo $errors['lot-step']; ?></span>
      </div>
      <div class="form__item <?php if(isset($errors['lot-date'])) echo $form_invalid_class; ?>">
        <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
        <input class="form__input-date" id="lot-date" type="text" name="lot-date" placeholder="Введите дату в формате ГГГГ-ММ-ДД">
        <span class="form__error"><?php if(isset($errors['lot-date'])) echo $errors['lot-date']; ?></span>
      </div>
    </div>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <button type="submit" name='submit' class="button">Добавить лот</button>
  </form>
</main>

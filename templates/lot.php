<?php
require_once('utils/utils.php');

$date_now = new DateTime();
?>

<main>
    <!-- список категорий уже есть в БД. но нет ссылок на страницы... 
    - Хранить ссылки в БД? 
    - Создать массив на странице?
    надо подумать... -->
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

    <section class="lot-item container">
      <h2><?= $lot['name'] ?></h2>
      <div class="lot-item__content">
        <div class="lot-item__left">
          <div class="lot-item__image">
            <img src="<?= $lot['image_url'] ?>" width="730" height="548" alt=""> <!-- как тут alt выставить? и ширину с высотой? -->
          </div>
          <p class="lot-item__category">Категория: <span><?= $lot['category'] ?></span></p>
          <p class="lot-item__description"><?= $lot['description'] ?></p>
        </div>
        <div class="lot-item__right">
          <div class="lot-item__state" <?php if($is_auth === 0) echo 'hidden'; ?> >
            <?php if(is_equal_or_less_hour_beetween_dates($lot['end_date'], $date_now)) : ?>
                <div class="lot__timer timer timer--finishing">
            <?php else: ?>
                <div class="lot__timer timer">
            <?php endif; ?>

              <?= time_to_lot_end_format($lot['end_date'], $date_now); ?>
            </div>

            <div class="lot-item__cost-state">
              <div class="lot-item__rate">
                <span class="lot-item__amount">Текущая цена</span>
                <span class="lot-item__cost"><?= format_price($lot['current_price']) ?></span>
              </div>
              <div class="lot-item__min-cost"> <!-- ToDo Минимальная ставка или стартовая цена? -->
                Мин. ставка <span><?= format_price($lot['start_price']) ?></span>
              </div>
            </div>

            <!-- Форма добавления ставки -->
            <?= $add_bet_content; ?>
            
          </div>
          <div class="history">
            <h3>История ставок (<span><?= count($bets_history); ?></span>)</h3>
            <table class="history__list">
              <?php foreach($bets_history as $history_record): ?>
                <tr class="history__item">
                  <td class="history__name"><?= $history_record['name']; ?></td>
                  <td class="history__price"><?= format_price($history_record['price']); ?></td>
                  <td class="history__time"><?= bet_date_create_format($date_now, $history_record['create_date']); ?></td>
                </tr>
              <?php endforeach; ?>
            </table>
          </div>
        </div>
      </div>
    </section>
  </main>
<?php

require_once('utils/utils.php');
require_once('helpers.php');

$now = new DateTime('Now');

$navigation = include_template('navigate.php', [ 'stuff_categories' => $stuff_categories ]);
?>

<main>
  <?= $navigation; ?>

  <section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
      <?php foreach($bets as $bet): ?>
        <tr class="rates__item">
          <td class="rates__info">
            <div class="rates__img">
              <img src="<?= $bet['image_url']; ?>" width="54" height="40" alt=""> <!-- Так и не понял как alt выставляют -->
            </div>
            <h3 class="rates__title"><a href="lot.php?id=<?= $bet['lot_id']; ?>"><?= $bet['name']; ?></a></h3>
            
            <!-- Контакты юзера создавшего лот. Показываем если выиграли. -->
            <?php if($bet['winner_id'] === $bet['user_id']): ?>
              <p><?= $bet['contacts']; ?></p>
            <?php endif; ?>
          </td>
          <td class="rates__category">
            <?= $bet['category_name']; ?>
          </td>
          <td class="rates__timer">
            <?php if($bet['winner_id'] === $bet['user_id']): ?>
              <div class="timer timer--win">Ставка выиграла</div>
            <?php else: ?>
              <?php if ($bet['end_date'] >= $now): ?>
                <div class="timer timer--end">Торги окончены</div>
              <?php else: ?>
                <!-- время до окончания торгов -->
                <?php if(is_equal_or_less_hour_beetween_dates($bet['end_date'], $now)) : ?>
                    <div class="lot__timer timer timer--finishing">
                <?php else: ?>
                    <div class="lot__timer timer">
                <?php endif; ?>

                  <?= time_to_lot_end_format($bet['end_date'], $now); ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td class="rates__price">
            <?= format_price($bet['price']); ?>
          </td>
          <td class="rates__time">
            <?= bet_date_create_format($now, $bet['create_date']); ?>
          </td>
        </tr>
      <?php endforeach; ?>
<!-- 
      <tr class="rates__item">
        <td class="rates__info">
          <div class="rates__img">
            <img src="../img/rate2.jpg" width="54" height="40" alt="Сноуборд">
          </div>
          <h3 class="rates__title"><a href="lot.html">DC Ply Mens 2016/2017 Snowboard</a></h3>
        </td>
        <td class="rates__category">
          Доски и лыжи
        </td>
        <td class="rates__timer">
          <div class="timer timer--finishing">07:13:34</div>
        </td>
        <td class="rates__price">
          10 999 р
        </td>
        <td class="rates__time">
          20 минут назад
        </td>
      </tr>
      <tr class="rates__item rates__item--win">
        <td class="rates__info">
          <div class="rates__img">
            <img src="../img/rate3.jpg" width="54" height="40" alt="Крепления">
          </div>
          <div>
            <h3 class="rates__title"><a href="lot.html">Крепления Union Contact Pro 2015 года размер L/XL</a></h3>
            <p>Телефон +7 900 667-84-48, Скайп: Vlas92. Звонить с 14 до 20</p>
          </div>
        </td>
        <td class="rates__category">
          Крепления
        </td>
        <td class="rates__timer">
          <div class="timer timer--win">Ставка выиграла</div>
        </td>
        <td class="rates__price">
          10 999 р
        </td>
        <td class="rates__time">
          Час назад
        </td>
      </tr>
      <tr class="rates__item">
        <td class="rates__info">
          <div class="rates__img">
            <img src="../img/rate4.jpg" width="54" height="40" alt="Ботинки">
          </div>
          <h3 class="rates__title"><a href="lot.html">Ботинки для сноуборда DC Mutiny Charocal</a></h3>
        </td>
        <td class="rates__category">
          Ботинки
        </td>
        <td class="rates__timer">
          <div class="timer">07:13:34</div>
        </td>
        <td class="rates__price">
          10 999 р
        </td>
        <td class="rates__time">
          Вчера, в 21:30
        </td>
      </tr>
      <tr class="rates__item rates__item--end">
        <td class="rates__info">
          <div class="rates__img">
            <img src="../img/rate5.jpg" width="54" height="40" alt="Куртка">
          </div>
          <h3 class="rates__title"><a href="lot.html">Куртка для сноуборда DC Mutiny Charocal</a></h3>
        </td>
        <td class="rates__category">
          Одежда
        </td>
        <td class="rates__timer">
          <div class="timer timer--end">Торги окончены</div>
        </td>
        <td class="rates__price">
          10 999 р
        </td>
        <td class="rates__time">
          Вчера, в 21:30
        </td>
      </tr>
      <tr class="rates__item rates__item--end">
        <td class="rates__info">
          <div class="rates__img">
            <img src="../img/rate6.jpg" width="54" height="40" alt="Маска">
          </div>
          <h3 class="rates__title"><a href="lot.html">Маска Oakley Canopy</a></h3>
        </td>
        <td class="rates__category">
          Разное
        </td>
        <td class="rates__timer">
          <div class="timer timer--end">Торги окончены</div>
        </td>
        <td class="rates__price">
          10 999 р
        </td>
        <td class="rates__time">
          19.03.17 в 08:21
        </td>
      </tr>
      <tr class="rates__item rates__item--end">
        <td class="rates__info">
          <div class="rates__img">
            <img src="../img/rate7.jpg" width="54" height="40" alt="Сноуборд">
          </div>
          <h3 class="rates__title"><a href="lot.html">DC Ply Mens 2016/2017 Snowboard</a></h3>
        </td>
        <td class="rates__category">
          Доски и лыжи
        </td>
        <td class="rates__timer">
          <div class="timer timer--end">Торги окончены</div>
        </td>
        <td class="rates__price">
          10 999 р
        </td>
        <td class="rates__time">
          19.03.17 в 08:21
        </td>
      </tr> -->
    </table>
  </section>
</main>

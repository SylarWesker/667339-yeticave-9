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
              <img src="<?= $bet['image_url']; ?>" width="54" height="40" alt=""> 
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
      
    </table>
  </section>
</main>

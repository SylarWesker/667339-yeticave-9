<?php
require_once('utils/utils.php');

$date_now = new DateTime();

$form_item_invalid_class = 'form__item--invalid';

$navigation = include_template('navigate.php', ['stuff_categories' => $stuff_categories]);
?>

<main>
    <?= $navigation; ?>

    <section class="lot-item container">
        <h2><?= $lot['name'] ?></h2>
        <div class="lot-item__content">
            <div class="lot-item__left">
                <div class="lot-item__image">
                    <img src="<?= $lot['image_url'] ?>" width="730" height="548" alt="">
                </div>
                <p class="lot-item__category">Категория: <span><?= $lot['category'] ?></span></p>
                <p class="lot-item__description"><?= $lot['description'] ?></p>
            </div>
            <div class="lot-item__right">
                <div class="lot-item__state" <?php if (!$is_auth) {
                    echo 'hidden';
                } ?> >
                    <?php if ($lot['winner_id'] === $user_id): ?>
                        <div class="lot__timer timer--win">Ставка выиграла</div>
                    <?php elseif ($lot['end_date'] >= $date_now): ?>
                        <div class="lot__timer timer--end">Торги окончены</div>
                    <?php else: ?>
                    <?php if (is_equal_or_less_hour_beetween_dates($lot['end_date'], $date_now)) : ?>
                    <div class="lot__timer timer timer--finishing">
                        <?php else: ?>
                        <div class="lot__timer timer">
                            <?php endif; ?>

                            <?= time_to_lot_end_format($lot['end_date'], $date_now); ?>
                        </div>
                        <?php endif; ?>

                        <div class="lot-item__cost-state">
                            <div class="lot-item__rate">
                                <span class="lot-item__amount">Текущая цена</span>
                                <span class="lot-item__cost"><?= format_price($lot['current_price']) ?></span>
                            </div>
                            <div class="lot-item__min-cost">
                                Мин. ставка <span><?= format_price($lot['start_price']) ?></span>
                            </div>
                        </div>

                        <!-- Форма добавления ставки -->
                        <?php if ($allow_add_bet): ?>
                            <form class="lot-item__form" action="lot.php?id=<?= $lot['id']; ?>" method="post"
                                  autocomplete="off">
                                <input type="hidden" name="lot_id" value="<?= $lot['id']; ?>">

                                <p class="lot-item__form-item form__item <?php if (isset($add_bet_errors['cost'])) {
                                    echo $form_item_invalid_class;
                                } ?>">
                                    <label for="cost">Ваша ставка</label>
                                    <input id="cost" type="text" name="cost"
                                           placeholder="<?= format_price($lot_min_price) ?>">
                                    <span class="form__error"><?= show_error('cost', $add_bet_errors); ?></span>
                                </p>
                                <button type="submit" name="submit" class="button">Сделать ставку</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div class="history">
                        <h3>История ставок (<span><?= count($bets_history); ?></span>)</h3>
                        <table class="history__list">
                            <?php foreach ($bets_history as $history_record): ?>
                                <tr class="history__item">
                                    <td class="history__name"><?= $history_record['name']; ?></td>
                                    <td class="history__price"><?= format_price($history_record['price']); ?></td>
                                    <td class="history__time"><?= bet_date_create_format($date_now,
                                            $history_record['create_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </div>

                </div>
            </div>
    </section>
</main>

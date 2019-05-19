<?php
    require_once('utils/utils.php');

    $now = new DateTime();
?>

<main class="container">
    <section class="promo">
        <h2 class="promo__title">Нужен стафф для катки?</h2>
        <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
        <ul class="promo__list">
            <!--заполните этот список из массива категорий-->
            <?php foreach($stuff_categories as $category): ?>
                <li class="promo__item promo__item--<?= $category['symbol_code']?>">
                    <a class="promo__link" href="pages/all-lots.html"><?= $category['name'] ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="lots">
        <div class="lots__header">
            <h2>Открытые лоты</h2>
        </div>
        <ul class="lots__list">
            <!--заполните этот список из массива с товарами-->
            <?php foreach($lots as $lot): ?>
                <li class="lots__item lot">
                    <div class="lot__image">
                        <img src="<?= $lot['image_url'] ?>" width="350" height="260" alt="">
                    </div>
                    <div class="lot__info">
                        <span class="lot__category"><?= $lot['category'] ?></span>
                        <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= $lot['id']?>"><?= $lot['name'] ?></a></h3> <!-- href = lot.php?id=1 -->
                        <div class="lot__state">
                            <div class="lot__rate">
                                <span class="lot__amount">Стартовая цена</span>
                                <span class="lot__cost"><?= format_price($lot['start_price']) ?></span>
                            </div>

                            <!-- если времени осталось ровно один час или меньше -->
                            <?php if(is_equal_or_less_hour_beetween_dates($lot['end_date'], $now)) : ?>
                                <div class="lot__timer timer timer--finishing">
                            <?php else: ?>
                                <div class="lot__timer timer">
                            <?php endif; ?>

                            <?= time_to_lot_end_format($lot['end_date'], $now); ?>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</main>
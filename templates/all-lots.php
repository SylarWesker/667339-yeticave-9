<?php
require_once('utils/utils.php');

$date_now = new DateTime();

$navigation = include_template('navigate.php', [ 'stuff_categories' => $stuff_categories ]);

$page_name = 'all-lots.php';
$url_params = [ 'category_name' => $category_name ];

$pagination = include_template('pagination.php', [
                                                    'current_page'    => $current_page,
                                                    'min_page_number' => $min_page_number,
                                                    'max_page_number' => $max_page_number,
                                                    'page_name'       => $page_name,
                                                    'url_params'      => $url_params
]);
?>

<main>
    <!-- Навигация по категориям -->
    <?=$navigation ?>

    <div class="container">
        <section class="lots">
        <h2>Все лоты в категории <span>«<?= $category_name; ?>»</span></h2>

        <ul class="lots__list">
            <?php foreach($lots as $lot): ?>
                <li class="lots__item lot">
                <div class="lot__image">
                    <img src="<?= $lot['image_url'] ?>" width="350" height="260" alt="">
                </div>
                <div class="lot__info">
                    <span class="lot__category"><?= $lot['category'] ?></span>
                    <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= $lot['id']; ?>"><?= $lot['name'] ?></a></h3>
                    <div class="lot__state">
                        <?php if($lot['bets_count'] > 0) : ?>
                            <span class="lot__amount"><?= get_noun_plural_form_with_number($lot['bets_count'], 'ставка', 'ставки', 'ставок'); ?></span>
                            <span class="lot__cost"><?= $lot['current_price']; ?><b class="rub">р</b></span>
                        <?php else: ?>
                            <span class="lot__amount">Стартовая цена</span>
                            <span class="lot__cost"><?= $lot['start_price'] ?><b class="rub">р</b></span>
                        <?php endif; ?>
                    </div>
                    <?php if(is_equal_or_less_hour_beetween_dates($lot['end_date'], $date_now)) : ?>
                        <div class="lot__timer timer timer--finishing">
                        <?php else: ?>
                            <div class="lot__timer timer">
                        <?php endif; ?>

                        <?= time_to_lot_end_format($lot['end_date'], $date_now); ?>
                    </div>
                </div>

                </li>
            <?php endforeach; ?>

            <!-- <li class="lots__item lot">
            <div class="lot__image">
                <img src="../img/lot-2.jpg" width="350" height="260" alt="Сноуборд">
            </div>
            <div class="lot__info">
                <span class="lot__category">Доски и лыжи</span>
                <h3 class="lot__title"><a class="text-link" href="lot.html">DC Ply Mens 2016/2017 Snowboard</a></h3>
                <div class="lot__state">
                <div class="lot__rate">
                    <span class="lot__amount">12 ставок</span>
                    <span class="lot__cost">15 999<b class="rub">р</b></span>
                </div>
                <div class="lot__timer timer timer--finishing">
                    00:54:12
                </div>
                </div>
            </div>
            </li>
            <li class="lots__item lot">
            <div class="lot__image">
                <img src="../img/lot-3.jpg" width="350" height="260" alt="Крепления">
            </div>
            <div class="lot__info">
                <span class="lot__category">Крепления</span>
                <h3 class="lot__title"><a class="text-link" href="lot.html">Крепления Union Contact Pro 2015 года размер
                L/XL</a></h3>
                <div class="lot__state">
                <div class="lot__rate">
                    <span class="lot__amount">7 ставок</span>
                    <span class="lot__cost">8 000<b class="rub">р</b></span>
                </div>
                <div class="lot__timer timer">
                    10:54:12
                </div>
                </div>
            </div>
            </li>
            <li class="lots__item lot">
            <div class="lot__image">
                <img src="../img/lot-4.jpg" width="350" height="260" alt="Ботинки">
            </div>
            <div class="lot__info">
                <span class="lot__category">Ботинки</span>
                <h3 class="lot__title"><a class="text-link" href="lot.html">Ботинки для сноуборда DC Mutiny Charocal</a>
                </h3>
                <div class="lot__state">
                <div class="lot__rate">
                    <span class="lot__amount">12 ставок</span>
                    <span class="lot__cost">10 999<b class="rub">р</b></span>
                </div>
                <div class="lot__timer timer timer--finishing">
                    00:12:03
                </div>
                </div>
            </div>
            </li>
            <li class="lots__item lot">
            <div class="lot__image">
                <img src="../img/lot-5.jpg" width="350" height="260" alt="Куртка">
            </div>
            <div class="lot__info">
                <span class="lot__category">Одежда</span>
                <h3 class="lot__title"><a class="text-link" href="lot.html">Куртка для сноуборда DC Mutiny Charocal</a></h3>
                <div class="lot__state">
                <div class="lot__rate">
                    <span class="lot__amount">12 ставок</span>
                    <span class="lot__cost">10 999<b class="rub">р</b></span>
                </div>
                <div class="lot__timer timer">
                    00:12:03
                </div>
                </div>
            </div>
            </li>
            <li class="lots__item lot">
            <div class="lot__image">
                <img src="../img/lot-6.jpg" width="350" height="260" alt="Маска">
            </div>
            <div class="lot__info">
                <span class="lot__category">Разное</span>
                <h3 class="lot__title"><a class="text-link" href="lot.html">Маска Oakley Canopy</a></h3>
                <div class="lot__state">
                <div class="lot__rate">
                    <span class="lot__amount">Стартовая цена</span>
                    <span class="lot__cost">5 500<b class="rub">р</b></span>
                </div>
                <div class="lot__timer timer">
                    07:13:34
                </div>
                </div>
            </div>
            </li>
            <li class="lots__item lot">
            <div class="lot__image">
                <img src="../img/lot-4.jpg" width="350" height="260" alt="Ботинки">
            </div>
            <div class="lot__info">
                <span class="lot__category">Ботинки</span>
                <h3 class="lot__title"><a class="text-link" href="lot.html">Ботинки для сноуборда DC Mutiny Charocal</a>
                </h3>
                <div class="lot__state">
                <div class="lot__rate">
                    <span class="lot__amount">12 ставок</span>
                    <span class="lot__cost">10 999<b class="rub">р</b></span>
                </div>
                <div class="lot__timer timer timer--finishing">
                    00:12:03
                </div>
                </div>
            </div>
            </li>
            <li class="lots__item lot">
            <div class="lot__image">
                <img src="../img/lot-5.jpg" width="350" height="260" alt="Куртка">
            </div>
            <div class="lot__info">
                <span class="lot__category">Одежда</span>
                <h3 class="lot__title"><a class="text-link" href="lot.html">Куртка для сноуборда DC Mutiny Charocal</a></h3>
                <div class="lot__state">
                <div class="lot__rate">
                    <span class="lot__amount">12 ставок</span>
                    <span class="lot__cost">10 999<b class="rub">р</b></span>
                </div>
                <div class="lot__timer timer">
                    00:12:03
                </div>
                </div>
            </div>
            </li>
            <li class="lots__item lot">
            <div class="lot__image">
                <img src="../img/lot-6.jpg" width="350" height="260" alt="Маска">
            </div>
            <div class="lot__info">
                <span class="lot__category">Разное</span>
                <h3 class="lot__title"><a class="text-link" href="lot.html">Маска Oakley Canopy</a></h3>
                <div class="lot__state">
                <div class="lot__rate">
                    <span class="lot__amount">Стартовая цена</span>
                    <span class="lot__cost">5 500<b class="rub">р</b></span>
                </div>
                <div class="lot__timer timer">
                    07:13:34
                </div>
                </div>
            </div>
            </li> -->
        </ul>
        </section>

        <!-- Пагинация -->
        <?= $pagination; ?>
        
        <!-- <ul class="pagination-list">
            <li class="pagination-item pagination-item-prev"><a>Назад</a></li>
            <li class="pagination-item pagination-item-active"><a>1</a></li>
            <li class="pagination-item"><a href="#">2</a></li>
            <li class="pagination-item"><a href="#">3</a></li>
            <li class="pagination-item"><a href="#">4</a></li>
            <li class="pagination-item pagination-item-next"><a href="#">Вперед</a></li>
        </ul> -->
    </div>
</main>

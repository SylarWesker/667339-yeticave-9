<?php
require_once('utils/utils.php');

$date_now = new DateTime();
$nothing_not_found_msg = 'Ничего не найдено по вашему запросу';

$page_name = 'search.php';
$url_params = [
    'find' => 'Найти',
    'search' => $search_query
];

$navigation = include_template('navigate.php', ['stuff_categories' => $stuff_categories]);

$pagination = include_template('pagination.php', [
    'current_page' => $current_page,
    'min_page_number' => $min_page_number,
    'max_page_number' => $max_page_number,
    'page_name' => $page_name,
    'url_params' => $url_params
]);

?>

<main>
    <?= $navigation; ?>

    <div class="container">
        <section class="lots">
            <?php if (empty($lots)): ?>
                <h2><?= $nothing_not_found_msg ?></h2>
                <p><?= show_error('search', $errors); ?></p>
            <?php else: ?>
                <h2>Результаты поиска по запросу «<span><?= $search_query; ?></span>»</h2>
            <?php endif; ?>

            <ul class="lots__list">

                <?php foreach ($lots as $lot): ?>
                    <li class="lots__item lot">
                        <div class="lot__image">
                            <img src="<?= $lot['image_url'] ?>" width="350" height="260" alt="">
                        </div>
                        <div class="lot__info">
              <span class="lot__category">Категория: <span><?= $lot['category'] ?></span>
              <h3 class="lot__title"><a class="text-link"
                                        href="lot.php?id=<?= $lot['id']; ?>"><?= $lot['name'] ?></a></h3>
              <div class="lot__state">
                <div class="lot__rate">
                  <!-- Если не было ставок, то выводим начальную стоимость
                  Если ставки были, то выводим кол-во ставок и текущую цену -->

                  <?php if ($lot['bets_count'] > 0) : ?>
                      <span class="lot__amount"><?= get_noun_plural_form_with_number($lot['bets_count'], 'ставка',
                              'ставки', 'ставок'); ?></span>
                      <span class="lot__cost"><?= $lot['current_price']; ?><b class="rub">р</b></span>
                  <?php else: ?>
                      <span class="lot__amount">Стартовая цена</span>
                      <span class="lot__cost"><?= $lot['start_price'] ?><b class="rub">р</b></span>
                  <?php endif; ?>
                  
                </div>

                <?php if (is_equal_or_less_hour_beetween_dates($lot['end_date'], $date_now)) : ?>
                    <div class="lot__timer timer timer--finishing">
                <?php else: ?>
                    <div class="lot__timer timer">
                <?php endif; ?>

                <?= time_to_lot_end_format($lot['end_date'], $date_now); ?>
                </div>

              </div>
            </div>
                    </li>
                <?php endforeach; ?>

            </ul>
        </section>

        <!-- Пагинация -->
        <?= $pagination ?>
    </div>
</main>

</div>

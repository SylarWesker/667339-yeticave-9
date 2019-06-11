<?php
require_once('utils/utils.php');
?>

<ul class="pagination-list">
    <?php if ($current_page !== $min_page_number): ?>
        <?php $url_params['page'] = $current_page - 1; ?>

        <li class="pagination-item pagination-item-prev"><a href="<?= get_href($page_name, $url_params); ?>">Назад</a>
        </li>
    <?php else: ?>
        <li class="pagination-item pagination-item-prev"><a href="#">Назад</a></li>
    <?php endif; ?>

    <?php for ($page_number = $min_page_number; $page_number <= $max_page_number; $page_number++): ?>
        <?php $url_params['page'] = $page_number; ?>

        <?php if ($page_number === $current_page): ?>
            <li class="pagination-item pagination-item-active"><a
                        href="<?= get_href($page_name, $url_params); ?>"><?= $page_number; ?></a></li>
        <?php else: ?>
            <li class="pagination-item"><a href="<?= get_href($page_name, $url_params); ?>"><?= $page_number; ?></a>
            </li>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($current_page !== $max_page_number): ?>
        <?php $url_params['page'] = $current_page + 1; ?>

        <li class="pagination-item pagination-item-next"><a href="<?= get_href($page_name, $url_params); ?>">Вперед</a>
        </li>
    <?php else: ?>
        <li class="pagination-item pagination-item-next"><a href="#">Вперед</a></li>
    <?php endif; ?>
</ul>
<?php
    http_response_code(404);

    $navigation = include_template('navigate.php', [ 'stuff_categories' => $stuff_categories ]);
?>

<main>
    <?= $navigation; ?>

    <section class="lot-item container">
        <h2>404 Страница не найдена</h2>
        <p>Данной страницы не существует на сайте.</p>

        <p>Список ошибок:</p>
        <ul>
            <?php foreach($error_list as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        <ul>
    </section>
</main>

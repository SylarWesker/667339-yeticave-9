<?php
http_response_code(500);
?>

<main>
    <section class="lot-item container">
        <h2>500 Ошибка на сервере</h2>
        <p>На сервере произошла непредвиденная ошибка. Пожалуйста, свяжитесь с администратором.</p>

        <p>Список ошибок:</p>
        <ul>
            <?php foreach ($error_list as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
            <ul>
    </section>
</main>

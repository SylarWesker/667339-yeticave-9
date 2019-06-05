<nav class="nav">
    <ul class="nav__list container">
        <?php foreach($stuff_categories as $category): ?>
            <li class="nav__item <?= current_nav_class($category['name'], $current_category) ?>"> 
                <a href="all-lots.php?category_name=<?= $category['name']; ?>"><?= $category['name']; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

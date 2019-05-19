<?php

// session_start();

// // 1. Избавиться от is_auth
// // 2. Сделать отдельную функцию разлогинирования.
// // 3. Хранить все данные пользователя по ключу user. user => [данные юзера]
// unset($_SESSION['user_name']);
// unset($_SESSION['is_auth']);

require_once('auth.php');

delete_user_data();

header('Location: index.php');

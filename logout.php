<?php

session_start();

unset($_SESSION['user_name']);
unset($_SESSION['is_auth']);

header('Location: index.php');

<?php

error_reporting(E_ALL);

require_once('auth.php');

delete_user_data();

header('Location: index.php');

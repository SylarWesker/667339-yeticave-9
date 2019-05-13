<?php

session_start();

$user_name = ''; 
$is_auth = 0;
$user_id = NULL;

$is_auth_help = isset($_SESSION['user_name']) && 
                isset($_SESSION['user_id']) && 
                isset($_SESSION['is_auth']);

if ($is_auth_help) {
    $user_name = $_SESSION['user_name'];
    $user_id = $_SESSION['user_id'];

    $is_auth = 1;
}
  
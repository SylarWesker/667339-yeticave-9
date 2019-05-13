<?php

session_start();

$user_name = ''; 
$is_auth = 0; 

if (isset($_SESSION['user_name']) && isset($_SESSION['is_auth'])) {
    $user_name = $_SESSION['user_name'];
    $is_auth = 1;
}
  
<?php

session_start();

$user_name = get_user_name();
$user_id = get_user_id();

function is_auth()
{
    return !(empty($_SESSION['user']['name']) && 
             empty($_SESSION['user']['id']));
}

function get_user_name()
{
    $user_name = '';

    if(is_auth()) {
        $user_name = $_SESSION['user']['name'];
    }

    return $user_name;
}

function get_user_id()
{
    $user_id = NULL;

    if(is_auth()) {
        $user_id = $_SESSION['user']['id'];
    }

    return $user_id;
}
 
function save_user_data($user_name, $user_id)
{
    $_SESSION['user'] = [
                            'name' => $user_name,
                            'id' =>  $user_id
                        ];
}

function delete_user_data()
{
    unset($_SESSION['user']);
}
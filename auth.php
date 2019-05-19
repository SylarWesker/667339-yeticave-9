<?php

session_start();

$user_name = ''; 
$user_id = NULL;
// $is_auth = 0;

// $is_auth_help = isset($_SESSION['user_name']) && 
//                 isset($_SESSION['user_id']) && 
//                 isset($_SESSION['is_auth']);

// if ($is_auth_help) {
//     $user_name = $_SESSION['user_name'];
//     $user_id = $_SESSION['user_id'];

//     // $is_auth = 1;
// }

load_user_data();

function is_auth()
{
    return !(empty($_SESSION['user']['name']) && 
             empty($_SESSION['user']['id']));
}

function load_user_data()
{
    if(is_auth()) {
        $user_name = $_SESSION['user']['name'];
        $user_id = $_SESSION['user']['id'];
    }
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
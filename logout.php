<?php

require_once('utils/error_report.php');

require_once('auth.php');

delete_user_data();

header('Location: index.php');

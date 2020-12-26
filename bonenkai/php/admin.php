<?php
session_start();

require_once('Common.php');
require_once('Db.php');

// if (empty($_SESSION['auth'])) {
//     header('Location: admin_login.php');
//     exit;
// }

require_once('../html/admin.html');

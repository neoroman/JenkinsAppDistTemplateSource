<?php
session_start();

if (isset($_SESSION['id'])) {
    unset($_SESSION['id']);
}
if (isset($_SESSION['internal_id'])) {
    unset($_SESSION['internal_id']);
}

require_once('config.php');
global $usingLogin;
if ($usingLogin && !isset($_SESSION['id'])) {
  header('Location: login.php');  
}
?>
<?php
require_once "../config/security.php";
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>

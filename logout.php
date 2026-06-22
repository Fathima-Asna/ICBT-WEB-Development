<?php
// logout.php
// Clears session and logs out user

session_start();
session_unset();
session_destroy();

header("Location: login.php");
exit;
?>

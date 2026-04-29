<?php
session_start();

// hapus semua data session
$_SESSION = [];

// hancurkan session
session_destroy();

// redirect ke login
header("Location: login.php");
exit;
?>
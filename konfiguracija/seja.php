<?php
ini_set('session.gc_maxlifetime', 600); // 10 minut
session_start();

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 600) {
    session_unset();
    session_destroy();
    header("Location: ../index.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();
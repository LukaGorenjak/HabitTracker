<?php
ini_set('session.gc_maxlifetime', 300);
session_start();

// Seja potekla – preusmeri z oznako
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 600) {
    session_unset();
    session_destroy();
    header("Location: /HabitTracker/index.php?timeout=1");
    exit();
}

$_SESSION['last_activity'] = time();

// Generiraj CSRF token (samo enkrat na sejo)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

<?php
// Nastavi maksimalno življenjsko dobo seje na 600 sekund (10 minut)
ini_set('session.gc_maxlifetime', 300);

// Zaženi sejo (prebere cookie PHPSESSID in naloži $_SESSION)
session_start();

// Preveri, ali je seja že potekla
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 600) {
    // Zbriši vse spremenljivke seje iz pomnilnika
    session_unset();
    // Uniči sejo na strežniku (izbriše datoteko seje)
    session_destroy();
    // Preusmeri na prijavno stran z oznako timeout=1 (za prikaz sporočila)
    header("Location: /HabitTracker/index.php?timeout=1");
    exit(); // Ustavi izvajanje – brez tega bi se preostala koda še izvedla
}

// Posodobi čas zadnje aktivnosti na trenutni Unix timestamp
$_SESSION['last_activity'] = time();

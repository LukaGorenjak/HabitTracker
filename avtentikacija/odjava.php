<?php
// Zaženi sejo (potrebno, da jo sploh lahko uničimo)
session_start();

// Počisti vse spremenljivke seje iz pomnilnika (prazen array)
$_SESSION = array();

// Zbriši tudi sejni cookie iz brskalnika (drugače ostane PHPSESSID)
if (ini_get("session.use_cookies")) {
    // Preberi trenutne parametre sejnega cookieja (pot, domena, secure, httponly)
    $params = session_get_cookie_params();
    // Nastavi cookie z enakimi parametri, a z datumom v preteklosti (time() - 42000)
    // To brskalnik pripravi na brisanje cookieja
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Uniči sejo na strežniku (izbriše datoteko seje iz /tmp ali session save path)
session_destroy();

// Preusmeri na prijavno stran
header("Location: ../index.php");
exit; // zaustavi PHP – brez tega bi se morebitna naslednja koda še izvedla
?>

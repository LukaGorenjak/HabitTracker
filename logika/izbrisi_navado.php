<?php
// Zaženi sejo z omejitvijo časa (seja.php kliče session_start)
require_once '../konfiguracija/seja.php';
// Naloži PDO povezavo z bazo
require_once '../konfiguracija/db.php';

// Ta endpoint vrača JSON (kliče ga JavaScript s fetch())
header('Content-Type: application/json');

// Če ni prijavljen, vrni napako v JSON obliki (ne preusmeritev, ker kliče AJAX)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

// Sprejmi samo POST zahteve z veljavnim id_navade
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_navade'])) {
    echo json_encode(['success' => false, 'error' => 'Neveljaven zahtevek.']);
    exit();
}

// Pretvori ID-je v cela števila
$id_navade     = (int)$_POST['id_navade'];
$id_uporabnika = (int)$_SESSION['user_id'];

// Najprej izbriši vse dnevnike za to navado (tuje ključe moramo zbrisati pred navado)
// Brez tega bi MySQL vrnil napako zaradi foreign key constraint
$stmt = $pdo->prepare("DELETE FROM dnevniki WHERE id_navade = ?");
$stmt->execute([$id_navade]);

// Izbriši navado – pogoj AND id_uporabnika = ? zagotavlja, da uporabnik ne more brisati tujih navad
$stmt = $pdo->prepare("DELETE FROM navade WHERE id_navade = ? AND id_uporabnika = ?");
$stmt->execute([$id_navade, $id_uporabnika]);

// rowCount() vrne število dejansko izbrisanih vrstic
if ($stmt->rowCount() > 0) {
    // Navada je bila uspešno izbrisana
    echo json_encode(['success' => true]);
} else {
    // Navada ni bila najdena (napačen ID ali ne pripada temu uporabniku)
    echo json_encode(['success' => false, 'error' => 'Navada ni bila najdena.']);
}
?>

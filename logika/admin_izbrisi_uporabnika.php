<?php
require_once '../konfiguracija/seja.php';
require_once '../konfiguracija/db.php';

header('Content-Type: application/json');

// Samo admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Dostop zavrnjen.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_uporabnika'])) {
    echo json_encode(['success' => false, 'error' => 'Neveljaven zahtevek.']);
    exit();
}

$targetId  = (int)$_POST['id_uporabnika'];
$adminId   = (int)$_SESSION['user_id'];

// Admin ne more zbrisati samega sebe
if ($targetId === $adminId) {
    echo json_encode(['success' => false, 'error' => 'Ne moreš izbrisati lastnega računa.']);
    exit();
}

// Preveri, da ciljni uporabnik obstaja
$stmt = $pdo->prepare("SELECT id_uporabnika FROM uporabniki WHERE id_uporabnika = ?");
$stmt->execute([$targetId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Uporabnik ne obstaja.']);
    exit();
}

// Kaskadno brisanje (od otrok do staršev, da ne kršimo tujih ključev):
// 1. Dnevniki → 2. Navade → 3. Kategorije → 4. Uporabnik

// 1. Izbriši vse dnevnike za navade tega uporabnika
$stmt = $pdo->prepare("DELETE FROM dnevniki WHERE id_navade IN (SELECT id_navade FROM navade WHERE id_uporabnika = ?)");
$stmt->execute([$targetId]);

// 2. Izbriši vse navade
$stmt = $pdo->prepare("DELETE FROM navade WHERE id_uporabnika = ?");
$stmt->execute([$targetId]);

// 3. Izbriši vse kategorije
$stmt = $pdo->prepare("DELETE FROM kategorije WHERE id_uporabnika = ?");
$stmt->execute([$targetId]);

// 4. Izbriši uporabnika
$stmt = $pdo->prepare("DELETE FROM uporabniki WHERE id_uporabnika = ?");
$stmt->execute([$targetId]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Brisanje ni uspelo.']);
}
?>

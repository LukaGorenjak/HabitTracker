<?php
require_once '../konfiguracija/seja.php';
require_once '../konfiguracija/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Dostop zavrnjen.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_uporabnika'])) {
    echo json_encode(['success' => false, 'error' => 'Neveljaven zahtevek.']);
    exit();
}

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Neveljavna seja.']);
    exit();
}

$targetId = (int)$_POST['id_uporabnika'];
$adminId  = (int)$_SESSION['user_id'];

if ($targetId === $adminId) {
    echo json_encode(['success' => false, 'error' => 'Ne moreš izbrisati lastnega računa.']);
    exit();
}

$stmt = $pdo->prepare("SELECT id_uporabnika FROM uporabniki WHERE id_uporabnika = ?");
$stmt->execute([$targetId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Uporabnik ne obstaja.']);
    exit();
}

$stmt = $pdo->prepare("DELETE FROM dnevniki WHERE id_navade IN (SELECT id_navade FROM navade WHERE id_uporabnika = ?)");
$stmt->execute([$targetId]);

$stmt = $pdo->prepare("DELETE FROM navade WHERE id_uporabnika = ?");
$stmt->execute([$targetId]);

$stmt = $pdo->prepare("DELETE FROM kategorije WHERE id_uporabnika = ?");
$stmt->execute([$targetId]);

$stmt = $pdo->prepare("DELETE FROM uporabniki WHERE id_uporabnika = ?");
$stmt->execute([$targetId]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Brisanje ni uspelo.']);
}
?>

<?php
require_once '../konfiguracija/seja.php';
require_once '../konfiguracija/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

// Preveri CSRF token
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Neveljavna seja.']);
    exit();
}

$id_uporabnika = (int)$_SESSION['user_id'];
$ime   = trim($_POST['ime']   ?? '');
$barva = trim($_POST['barva'] ?? '#4a9d6f');

if (empty($ime)) {
    echo json_encode(['success' => false, 'error' => 'Ime kategorije je obvezno.']);
    exit();
}

$stmt = $pdo->prepare("SELECT id_kategorije FROM kategorije WHERE id_uporabnika = ? AND ime = ?");
$stmt->execute([$id_uporabnika, $ime]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Kategorija s tem imenom že obstaja.']);
    exit();
}

if (!preg_match('/^#[0-9a-fA-F]{6}$/', $barva)) {
    $barva = '#4a9d6f';
}

$stmt = $pdo->prepare("INSERT INTO kategorije (id_uporabnika, ime, barva) VALUES (?, ?, ?)");
$stmt->execute([$id_uporabnika, $ime, $barva]);

echo json_encode(['success' => true, 'id' => (int)$pdo->lastInsertId()]);
?>

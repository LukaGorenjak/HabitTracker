<?php
session_start();
require_once '../konfiguracija/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_navade'])) {
    echo json_encode(['success' => false, 'error' => 'Neveljaven zahtevek.']);
    exit();
}

$id_navade     = (int)$_POST['id_navade'];
$id_uporabnika = (int)$_SESSION['user_id'];

// Delete related diary entries first
$stmt = $pdo->prepare("DELETE FROM dnevniki WHERE id_navade = ?");
$stmt->execute([$id_navade]);

// Delete the habit — only if it belongs to the logged-in user (security check)
$stmt = $pdo->prepare("DELETE FROM navade WHERE id_navade = ? AND id_uporabnika = ?");
$stmt->execute([$id_navade, $id_uporabnika]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Navada ni bila najdena.']);
}
?>

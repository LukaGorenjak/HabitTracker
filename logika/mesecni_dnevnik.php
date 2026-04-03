<?php
require_once '../konfiguracija/seja.php';
require_once '../konfiguracija/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

$id_navade = (int)($_GET['id_navade'] ?? 0);
$year      = (int)($_GET['leto']      ?? date('Y'));
$month     = (int)($_GET['mesec']     ?? date('n'));

if ($id_navade <= 0) {
    echo json_encode(['success' => false, 'error' => 'Manjka id_navade.']);
    exit();
}

$stmt = $pdo->prepare("SELECT id_navade FROM navade WHERE id_navade = ? AND id_uporabnika = ?");
$stmt->execute([$id_navade, (int)$_SESSION['user_id']]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Navada ni najdena.']);
    exit();
}

$from = sprintf('%04d-%02d-01', $year, $month);
$to   = date('Y-m-t', strtotime($from));

$stmt = $pdo->prepare("
    SELECT datum FROM dnevniki
    WHERE id_navade = ? AND datum BETWEEN ? AND ? AND opravljeno = 1
");
$stmt->execute([$id_navade, $from, $to]);

$opravljeni = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode(['success' => true, 'opravljeni' => $opravljeni]);
?>

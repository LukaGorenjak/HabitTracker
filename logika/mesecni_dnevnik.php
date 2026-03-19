<?php
// Zaženi sejo z omejitvijo časa (seja.php kliče session_start)
require_once '../konfiguracija/seja.php';
// Naloži PDO povezavo z bazo
require_once '../konfiguracija/db.php';

// Ta endpoint vrača JSON (GET zahteva iz JavaScript fetch())
header('Content-Type: application/json');

// Preveri prijavo
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

// Preberi parametre iz URL query stringa (?id_navade=X&leto=2025&mesec=3)
$id_navade = (int)($_GET['id_navade'] ?? 0);
$year      = (int)($_GET['leto']      ?? date('Y')); // privzeto tekoče leto
$month     = (int)($_GET['mesec']     ?? date('n')); // privzeto tekoči mesec (n = brez vodilne ničle)

// id_navade mora biti pozitivno število
if ($id_navade <= 0) {
    echo json_encode(['success' => false, 'error' => 'Manjka id_navade.']);
    exit();
}

// VARNOST: preveri, da navada pripada prijavljenemu uporabniku
$stmt = $pdo->prepare("SELECT id_navade FROM navade WHERE id_navade = ? AND id_uporabnika = ?");
$stmt->execute([$id_navade, (int)$_SESSION['user_id']]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Navada ni najdena.']);
    exit();
}

// Izračunaj prvi in zadnji dan izbranega meseca
// sprintf('%04d-%02d-01') zagotovi ničle: 2025-03-01 (ne 2025-3-1)
$from = sprintf('%04d-%02d-01', $year, $month);
// date('Y-m-t') → t vrne število dni v mesecu; npr. za 2025-03-01 vrne 2025-03-31
$to   = date('Y-m-t', strtotime($from));

// Poišči vse opravljene datume za to navado v izbranem mesecu
$stmt = $pdo->prepare("
    SELECT datum FROM dnevniki
    WHERE id_navade = ? AND datum BETWEEN ? AND ? AND opravljeno = 1
");
$stmt->execute([$id_navade, $from, $to]);

// FETCH_COLUMN vrne ravno polje z vrednostmi prve kolone: ['2025-03-01', '2025-03-03', ...]
$opravljeni = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Vrni JSON z nizom opravljenih datumov
echo json_encode(['success' => true, 'opravljeni' => $opravljeni]);
?>

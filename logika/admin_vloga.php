<?php
require_once '../konfiguracija/seja.php';
require_once '../konfiguracija/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Dostop zavrnjen.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_uporabnika']) || empty($_POST['vloga'])) {
    echo json_encode(['success' => false, 'error' => 'Neveljaven zahtevek.']);
    exit();
}

$targetId = (int)$_POST['id_uporabnika'];
$vloga    = $_POST['vloga'];

// Dovoljeni vrednosti vloge
if (!in_array($vloga, ['uporabnik', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Neveljavna vloga.']);
    exit();
}

// Admin ne more spremeniti lastne vloge
if ($targetId === (int)$_SESSION['user_id']) {
    echo json_encode(['success' => false, 'error' => 'Ne moreš spremeniti lastne vloge.']);
    exit();
}

$stmt = $pdo->prepare("UPDATE uporabniki SET vloga = ? WHERE id_uporabnika = ?");
$stmt->execute([$vloga, $targetId]);

echo json_encode(['success' => true]);
?>

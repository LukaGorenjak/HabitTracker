<?php
require_once '../konfiguracija/seja.php';
require_once '../konfiguracija/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    header("Location: ../index.php?error=csrf");
    exit();
}

$id_uporabnika = (int)$_SESSION['user_id'];

$ime_navade    = trim($_POST['ime_navade'] ?? '');
$opis          = trim($_POST['opis'] ?? '');
$ponavljanje   = $_POST['ponavljanje'] ?? 'dnevno';
$izbrani_dnevi = isset($_POST['dnevi']) ? implode(",", $_POST['dnevi']) : "vsak_dan";
$del_dneva     = isset($_POST['del_dneva']) ? implode(",", $_POST['del_dneva']) : null;
$cilj_kolicina = (int)($_POST['cilj_kolicina'] ?? 1);
$cilj_enota    = $_POST['cilj_enota'] ?? 'krat';
$cilj_obdobje  = $_POST['cilj_obdobje'] ?? 'na_dan';
$cilj_dni      = !empty($_POST['cilj_dni']) ? (int)$_POST['cilj_dni'] : null;
$emoji         = mb_substr(trim($_POST['emoji'] ?? ''), 0, 10);

if (empty($ime_navade)) {
    header("Location: ../index.php?error=prazno_ime");
    exit();
}

// --- Kategorija ---
$ime_kategorije = $_POST['kategorija'] ?? 'osebno';
$barva_map = [
    'zdravje' => '#4a9d6f',
    'delo'    => '#4a90e2',
    'osebno'  => '#c47c9f',
];
$barva = $barva_map[$ime_kategorije] ?? '#4a9d6f';

$stmt = $pdo->prepare("SELECT id_kategorije FROM kategorije WHERE id_uporabnika = ? AND ime = ?");
$stmt->execute([$id_uporabnika, $ime_kategorije]);
$kategorija = $stmt->fetch();

if ($kategorija) {
    $id_kategorije = (int)$kategorija['id_kategorije'];
} else {
    $stmt = $pdo->prepare("INSERT INTO kategorije (id_uporabnika, ime, barva) VALUES (?, ?, ?)");
    $stmt->execute([$id_uporabnika, $ime_kategorije, $barva]);
    $id_kategorije = (int)$pdo->lastInsertId();
}

$sql = "INSERT INTO navade (
    id_uporabnika, id_kategorije, ime_navade, ponavljanje, izbrani_dnevi,
    del_dneva, cilj_kolicina, cilj_enota, cilj_obdobje, cilj_dni, opis, emoji, streak
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $id_uporabnika, $id_kategorije, $ime_navade, $ponavljanje, $izbrani_dnevi,
    $del_dneva, $cilj_kolicina, $cilj_enota, $cilj_obdobje, $cilj_dni, $opis, $emoji ?: null,
]);

header("Location: ../index.php?status=uspeh");
exit();
?>

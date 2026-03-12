<?php
session_start();
require_once '../konfiguracija/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST['id_navade'])) {
    header("Location: ../index.php");
    exit();
}

$id_navade     = (int)$_POST['id_navade'];
$id_uporabnika = (int)$_SESSION['user_id'];

// Verify this habit belongs to the logged-in user before updating
$stmt = $pdo->prepare("SELECT id_navade FROM navade WHERE id_navade = ? AND id_uporabnika = ?");
$stmt->execute([$id_navade, $id_uporabnika]);
if (!$stmt->fetch()) {
    header("Location: ../index.php?error=nedovoljeno");
    exit();
}

// --- Basic fields ---
$ime_navade    = trim($_POST['ime_navade'] ?? '');
$opis          = trim($_POST['opis'] ?? '');
$zacetni_datum = $_POST['zacetni_datum'] ?? date('Y-m-d');
$ponavljanje   = $_POST['ponavljanje'] ?? 'dnevno';
$izbrani_dnevi = isset($_POST['dnevi']) ? implode(",", $_POST['dnevi']) : "vsak_dan";

// --- Goal ---
$cilj_kolicina = (int)($_POST['cilj_kolicina'] ?? 1);
$cilj_enota    = $_POST['cilj_enota'] ?? 'krat';
$cilj_obdobje  = $_POST['cilj_obdobje'] ?? 'na_dan';

// --- End condition ---
$konec_tip   = $_POST['konec_tip'] ?? 'nikoli';
$konec_datum = null;
if ($konec_tip === 'datum') {
    $konec_datum = !empty($_POST['konec_datum']) ? $_POST['konec_datum'] : null;
} elseif ($konec_tip === 'dni') {
    $dni = max(1, (int)($_POST['konec_dni'] ?? 30));
    $konec_datum = date('Y-m-d', strtotime($zacetni_datum . " +$dni days"));
}

if (empty($ime_navade)) {
    header("Location: ../index.php?error=prazno_ime");
    exit();
}

// --- Category: find or create ---
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

// --- Update ---
$sql = "UPDATE navade SET
    id_kategorije  = ?,
    ime_navade     = ?,
    ponavljanje    = ?,
    izbrani_dnevi  = ?,
    cilj_kolicina  = ?,
    cilj_enota     = ?,
    cilj_obdobje   = ?,
    zacetni_datum  = ?,
    konec_tip      = ?,
    konec_datum    = ?,
    opis           = ?
WHERE id_navade = ? AND id_uporabnika = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $id_kategorije,
    $ime_navade,
    $ponavljanje,
    $izbrani_dnevi,
    $cilj_kolicina,
    $cilj_enota,
    $cilj_obdobje,
    $zacetni_datum,
    $konec_tip,
    $konec_datum,
    $opis,
    $id_navade,
    $id_uporabnika,
]);

header("Location: ../index.php?status=posodobljeno");
exit();
?>

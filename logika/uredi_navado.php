<?php
// Zaženi sejo z omejitvijo časa (seja.php kliče session_start)
require_once '../konfiguracija/seja.php';
// Naloži PDO povezavo z bazo
require_once '../konfiguracija/db.php';

// Varnostno preverjanje: samo prijavljeni uporabniki smejo urejati
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Sprejmi samo POST zahteve, ki vsebujejo id_navade
if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_POST['id_navade'])) {
    header("Location: ../index.php");
    exit();
}

// Pretvori ID-je v cela števila – prepreči SQL injection
$id_navade     = (int)$_POST['id_navade'];
$id_uporabnika = (int)$_SESSION['user_id'];

// VARNOST: Preveri, da navada res pripada temu uporabniku (ne more urejati tujih navad)
$stmt = $pdo->prepare("SELECT id_navade FROM navade WHERE id_navade = ? AND id_uporabnika = ?");
$stmt->execute([$id_navade, $id_uporabnika]);
if (!$stmt->fetch()) {
    // Navada ne obstaja ali pripada drugemu uporabniku → blokiraj
    header("Location: ../index.php?error=nedovoljeno");
    exit();
}

// --- Osnovna polja iz obrazca ---
$ime_navade    = trim($_POST['ime_navade'] ?? '');
$opis          = trim($_POST['opis'] ?? '');
$ponavljanje   = $_POST['ponavljanje'] ?? 'dnevno';

// implode() združi array checkboxov v vejicami ločen niz
$izbrani_dnevi = isset($_POST['dnevi']) ? implode(",", $_POST['dnevi']) : "vsak_dan";
$del_dneva     = isset($_POST['del_dneva']) ? implode(",", $_POST['del_dneva']) : null;

// --- Cilj ---
$cilj_kolicina = (int)($_POST['cilj_kolicina'] ?? 1);
$cilj_enota    = $_POST['cilj_enota'] ?? 'krat';
$cilj_obdobje  = $_POST['cilj_obdobje'] ?? 'na_dan';
$cilj_dni      = !empty($_POST['cilj_dni']) ? (int)$_POST['cilj_dni'] : null;

// Ime navade je obvezno
if (empty($ime_navade)) {
    header("Location: ../index.php?error=prazno_ime");
    exit();
}

// --- Kategorija: poišči obstoječo ali ustvari novo ---
$ime_kategorije = $_POST['kategorija'] ?? 'osebno';
$barva_map = [
    'zdravje' => '#4a9d6f',
    'delo'    => '#4a90e2',
    'osebno'  => '#c47c9f',
];
$barva = $barva_map[$ime_kategorije] ?? '#4a9d6f';

// Preveri obstoj kategorije za tega uporabnika
$stmt = $pdo->prepare("SELECT id_kategorije FROM kategorije WHERE id_uporabnika = ? AND ime = ?");
$stmt->execute([$id_uporabnika, $ime_kategorije]);
$kategorija = $stmt->fetch();

if ($kategorija) {
    $id_kategorije = (int)$kategorija['id_kategorije'];
} else {
    // Kategorija ne obstaja → ustvari jo
    $stmt = $pdo->prepare("INSERT INTO kategorije (id_uporabnika, ime, barva) VALUES (?, ?, ?)");
    $stmt->execute([$id_uporabnika, $ime_kategorije, $barva]);
    $id_kategorije = (int)$pdo->lastInsertId();
}

// --- Posodobi navado v bazi ---
$sql = "UPDATE navade SET
    id_kategorije  = ?,
    ime_navade     = ?,
    ponavljanje    = ?,
    izbrani_dnevi  = ?,
    del_dneva      = ?,
    cilj_kolicina  = ?,
    cilj_enota     = ?,
    cilj_obdobje   = ?,
    cilj_dni       = ?,
    opis           = ?
WHERE id_navade = ? AND id_uporabnika = ?";
// WHERE vsebuje oba pogoja: id navade IN id uporabnika → dvojna zaščita

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $id_kategorije,
    $ime_navade,
    $ponavljanje,
    $izbrani_dnevi,
    $del_dneva,
    $cilj_kolicina,
    $cilj_enota,
    $cilj_obdobje,
    $cilj_dni,
    $opis,
    $id_navade,     // WHERE id_navade = ?
    $id_uporabnika, // AND id_uporabnika = ?
]);

// Preusmeri nazaj z obvestilom o uspešni posodobitvi
header("Location: ../index.php?status=posodobljeno");
exit();
?>

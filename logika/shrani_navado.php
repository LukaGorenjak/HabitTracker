<?php
// Zaženi sejo z omejitvijo časa (seja.php kliče session_start)
require_once '../konfiguracija/seja.php';
// Naloži PDO povezavo z bazo ($pdo)
require_once '../konfiguracija/db.php';

// Če uporabnik ni prijavljen, ga preusmeri na domačo stran
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Sprejmi samo POST zahteve (obrazec pošilja s POST)
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../index.php");
    exit();
}

// Preberi ID prijavljenega uporabnika in ga pretvori v celo število (varnost pred SQL injection)
$id_uporabnika = (int)$_SESSION['user_id'];

// --- Osnovna polja iz obrazca ---
$ime_navade    = trim($_POST['ime_navade'] ?? '');    // trim() odstrani presledke na začetku/koncu
$opis          = trim($_POST['opis'] ?? '');           // opis je neobvezen, privzeto prazen niz
$ponavljanje   = $_POST['ponavljanje'] ?? 'dnevno';   // dnevno/tedensko/mesecno

// Dnevi: iz PHP polje (checkboxi) v vejicami ločen niz (npr. "ponedeljek,torek")
// Če frekvenka ni tedensko, checkboxov ni → shranimo "vsak_dan"
$izbrani_dnevi = isset($_POST['dnevi']) ? implode(",", $_POST['dnevi']) : "vsak_dan";

// Del dneva (zjutraj/popoldne/zvečer) – neobvezno, lahko je null
$del_dneva = isset($_POST['del_dneva']) ? implode(",", $_POST['del_dneva']) : null;

// --- Cilj ---
$cilj_kolicina = (int)($_POST['cilj_kolicina'] ?? 1); // količina (npr. 30 minut)
$cilj_enota    = $_POST['cilj_enota'] ?? 'krat';      // enota (krat/minute/uri)
$cilj_obdobje  = $_POST['cilj_obdobje'] ?? 'na_dan';  // obdobje (na_dan/na_teden)
$cilj_dni      = !empty($_POST['cilj_dni']) ? (int)$_POST['cilj_dni'] : null; // streak cilj (neobvezno)

// --- Obvezno polje: ime navade ne sme biti prazno ---
if (empty($ime_navade)) {
    header("Location: ../index.php?error=prazno_ime");
    exit();
}

// --- Kategorija: poišči obstoječo ali ustvari novo ---
$ime_kategorije = $_POST['kategorija'] ?? 'osebno';

// Privzete barve za vsako kategorijo
$barva_map = [
    'zdravje' => '#4a9d6f',
    'delo'    => '#4a90e2',
    'osebno'  => '#c47c9f',
];
// Če kategorija ni v mapi, vzamemo zeleno kot privzeto
$barva = $barva_map[$ime_kategorije] ?? '#4a9d6f';

// Preveri ali ta kategorija za tega uporabnika že obstaja v bazi
$stmt = $pdo->prepare("SELECT id_kategorije FROM kategorije WHERE id_uporabnika = ? AND ime = ?");
$stmt->execute([$id_uporabnika, $ime_kategorije]);
$kategorija = $stmt->fetch(); // vrne vrstico ali false

if ($kategorija) {
    // Kategorija obstaja → vzamemo njen ID
    $id_kategorije = (int)$kategorija['id_kategorije'];
} else {
    // Kategorija ne obstaja → jo vstavimo v bazo
    $stmt = $pdo->prepare("INSERT INTO kategorije (id_uporabnika, ime, barva) VALUES (?, ?, ?)");
    $stmt->execute([$id_uporabnika, $ime_kategorije, $barva]);
    // lastInsertId() vrne auto-increment ID pravkar vstavljene vrstice
    $id_kategorije = (int)$pdo->lastInsertId();
}

// --- Vstavi novo navado v tabelo navade ---
$sql = "INSERT INTO navade (
    id_uporabnika, id_kategorije, ime_navade, ponavljanje, izbrani_dnevi,
    del_dneva, cilj_kolicina, cilj_enota, cilj_obdobje, cilj_dni, opis, streak
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
// ? so placeholderji – PDO jih varno zamenja z vrednostmi (prepreči SQL injection)
// streak začnemo pri 0 (nova navada, nič ni opravljeno)

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $id_uporabnika,
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
]);

// Po uspešnem shranjevanju preusmeri nazaj z oznako uspeh
header("Location: ../index.php?status=uspeh");
exit();
?>

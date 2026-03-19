<?php
// Zaženi sejo z omejitvijo časa (seja.php kliče session_start)
require_once '../konfiguracija/seja.php';
// Naloži PDO povezavo z bazo
require_once '../konfiguracija/db.php';

// Vrne JSON (kliče JavaScript fetch())
header('Content-Type: application/json');

// Preveri prijavo
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

// Zahteva mora biti POST z veljavnim id_navade
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_navade'])) {
    echo json_encode(['success' => false, 'error' => 'Neveljaven zahtevek.']);
    exit();
}

$id_navade     = (int)$_POST['id_navade'];
$id_uporabnika = (int)$_SESSION['user_id'];

// Sprejmi opcijski datum (klik na dan v kalendarju); privzeto danes
// preg_match preveri, da datum ustreza formatu LLLL-MM-DD (prepreči škodljiv vnos)
$datum = isset($_POST['datum']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['datum'])
    ? $_POST['datum']
    : date('Y-m-d');

$today = date('Y-m-d'); // danes – referenca za izračun streaka

// VARNOST: preveri, da navada pripada prijavljenemu uporabniku
$stmt = $pdo->prepare("SELECT id_navade FROM navade WHERE id_navade = ? AND id_uporabnika = ?");
$stmt->execute([$id_navade, $id_uporabnika]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Navada ni najdena.']);
    exit();
}

// Preveri, ali za ta datum že obstaja vnos v dnevniku
$stmt = $pdo->prepare("SELECT id_dnevnika, opravljeno FROM dnevniki WHERE id_navade = ? AND datum = ?");
$stmt->execute([$id_navade, $datum]);
$entry = $stmt->fetch(); // vrne vrstico ali false

if ($entry) {
    // Vnos obstaja → toggle: preklopi vrednost (1 postane 0, 0 postane 1)
    $new_value = $entry['opravljeno'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE dnevniki SET opravljeno = ? WHERE id_dnevnika = ?");
    $stmt->execute([$new_value, $entry['id_dnevnika']]);
    $logged = (bool)$new_value; // true = opravljeno, false = razveljavili
} else {
    // Vnosa ni → ustvari novega z opravljeno = 1
    $stmt = $pdo->prepare("INSERT INTO dnevniki (id_navade, datum, opravljeno) VALUES (?, ?, 1)");
    $stmt->execute([$id_navade, $datum]);
    $logged = true;
}

// --- Izračun streaka ---
// Poberi vse opravljene datume, sortirane od najnovejšega naprej
$stmt = $pdo->prepare("
    SELECT datum FROM dnevniki
    WHERE id_navade = ? AND opravljeno = 1
    ORDER BY datum DESC
");
$stmt->execute(params: [$id_navade]);
// FETCH_COLUMN vrne ravno polje vrednosti prve kolone (samo datume, brez asociativnih ključev)
$dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

$streak   = 0;
$expected = new DateTime('today'); // začnemo od danes in gremo nazaj

foreach ($dates as $dateStr) {
    $d = new DateTime($dateStr); // pretvori niz datuma v DateTime objekt
    if ($d->format('Y-m-d') === $expected->format('Y-m-d')) {
        // Dan se ujema s pričakovanim → povečamo streak
        $streak++;
        $expected->modify('-1 day'); // naslednji pričakovani dan je en dan prej
    } else {
        // Prekinitev zaporedja → prekinemo zanko
        break;
    }
}

// Shrani posodobljeni streak v tabelo navade
$stmt = $pdo->prepare("UPDATE navade SET streak = ? WHERE id_navade = ?");
$stmt->execute([$streak, $id_navade]);

// Vrni JSON odgovor JavaScriptu
echo json_encode([
    'success' => true,
    'logged'  => $logged,  // ali je dan zdaj označen kot opravljen
    'streak'  => $streak,  // trenutni zaporedni streak
]);
?>

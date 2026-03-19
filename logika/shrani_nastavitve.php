<?php
// Zaženi sejo z omejitvijo časa (seja.php kliče session_start)
require_once '../konfiguracija/seja.php';
// Naloži PDO povezavo z bazo
require_once '../konfiguracija/db.php';

// Vrne JSON (kliče JavaScript fetch() iz nastavitvenega modala)
header('Content-Type: application/json');

// Preveri prijavo
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

// Preberi ID iz seje in osnovna polja iz POST
$id    = (int)$_SESSION['user_id'];
$ime   = trim($_POST['ime']   ?? ''); // trim() odstrani presledke
$email = trim($_POST['email'] ?? '');

// Ime in e-pošta sta obvezni polji
if (empty($ime) || empty($email)) {
    echo json_encode(['success' => false, 'error' => 'Ime in e-pošta sta obvezna.']);
    exit();
}

// Preveri veljavnost e-poštnega naslova s PHP funkcijo filter_var
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Neveljaven e-poštni naslov.']);
    exit();
}

// Preveri, ali ta e-pošta že obstaja pri drugem uporabniku
// AND id_uporabnika != ? → tega uporabnika izključimo (lahko obdrži svojo e-pošto)
$stmt = $pdo->prepare("SELECT id_uporabnika FROM uporabniki WHERE email = ? AND id_uporabnika != ?");
$stmt->execute([$email, $id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Ta e-pošta je že v uporabi.']);
    exit();
}

// --- Opcijska menjava gesla ---
$novoHash = null; // null pomeni: gesla ne menjamo
$trenutno = $_POST['trenutno_geslo'] ?? '';
$novo     = $_POST['novo_geslo']     ?? '';
$potrdi   = $_POST['potrdi_geslo']   ?? '';

if (!empty($novo)) {
    // Novo geslo je vpisano → preveri pogoje
    if (strlen($novo) < 6) {
        echo json_encode(['success' => false, 'error' => 'Geslo mora biti dolgo vsaj 6 znakov.']);
        exit();
    }
    if ($novo !== $potrdi) {
        // Gesli se ne ujemata
        echo json_encode(['success' => false, 'error' => 'Gesli se ne ujemata.']);
        exit();
    }
    // Preberi trenutni hash gesla iz baze
    $stmt = $pdo->prepare("SELECT hash_gesla FROM uporabniki WHERE id_uporabnika = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    // password_verify() primerja vnešeno geslo s hashom (bcrypt)
    if (!password_verify($trenutno, $row['hash_gesla'])) {
        echo json_encode(['success' => false, 'error' => 'Trenutno geslo je napačno.']);
        exit();
    }
    // Ustvari nov bcrypt hash novega gesla (PASSWORD_DEFAULT = bcrypt)
    $novoHash = password_hash($novo, PASSWORD_DEFAULT);
}

// --- Opcijska profilna slika ---
$profilnaSlika = null; // null pomeni: slike ne menjamo
if (!empty($_FILES['profilna_slika']['name'])) {
    // Dovoljeni MIME tipi
    $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $tmpPath  = $_FILES['profilna_slika']['tmp_name']; // začasna pot naložene datoteke
    // mime_content_type() prebere dejanski format datoteke (ne samo pripono – varnejše)
    $mimeType = mime_content_type($tmpPath);

    if (!in_array($mimeType, $allowed)) {
        echo json_encode(['success' => false, 'error' => 'Nepodprta vrsta slike (jpeg/png/webp).']);
        exit();
    }
    // Omejitev velikosti: največ 2 MB (2 * 1024 * 1024 bajtov)
    if ($_FILES['profilna_slika']['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'Slika je prevelika (max 2 MB).']);
        exit();
    }

    // Pridobi pripono iz originalnega imena datoteke in jo pretvori v male črke
    $ext       = strtolower(pathinfo($_FILES['profilna_slika']['name'], PATHINFO_EXTENSION));
    // Edinstveno ime datoteke: ID_timestamp.ext (prepreči prepise med uporabniki)
    $filename  = $id . '_' . time() . '.' . $ext;
    $uploadDir = '../ostalo/slike/profil/';

    // Ustvari mapo, če ne obstaja (0755 = rwxr-xr-x; true = ustvari celotno pot)
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Premakni datoteko iz začasnega mesta v ciljno mapo
    if (!move_uploaded_file($tmpPath, $uploadDir . $filename)) {
        echo json_encode(['success' => false, 'error' => 'Napaka pri nalaganju slike.']);
        exit();
    }

    // Relativna pot za shranjevanje v bazo (od korena projekta)
    $profilnaSlika = 'ostalo/slike/profil/' . $filename;
}

// --- Dinamičen UPDATE glede na to, kaj je spremenila se ---
// 4 primeri: obe novi (hash + slika), samo hash, samo slika, nič novega
if ($novoHash && $profilnaSlika) {
    $stmt = $pdo->prepare("UPDATE uporabniki SET uporabnisko_ime=?, email=?, hash_gesla=?, profilna_slika=? WHERE id_uporabnika=?");
    $stmt->execute([$ime, $email, $novoHash, $profilnaSlika, $id]);
} elseif ($novoHash) {
    $stmt = $pdo->prepare("UPDATE uporabniki SET uporabnisko_ime=?, email=?, hash_gesla=? WHERE id_uporabnika=?");
    $stmt->execute([$ime, $email, $novoHash, $id]);
} elseif ($profilnaSlika) {
    $stmt = $pdo->prepare("UPDATE uporabniki SET uporabnisko_ime=?, email=?, profilna_slika=? WHERE id_uporabnika=?");
    $stmt->execute([$ime, $email, $profilnaSlika, $id]);
} else {
    // Samo ime in e-pošta sta se spremenili
    $stmt = $pdo->prepare("UPDATE uporabniki SET uporabnisko_ime=?, email=? WHERE id_uporabnika=?");
    $stmt->execute([$ime, $email, $id]);
}

// Posodobi sejo – da se novo ime takoj prikaže brez ponovne prijave
$_SESSION['username'] = $ime;

// Sestavi JSON odgovor; dodaj profilna_slika samo če je bila naložena
$response = ['success' => true, 'ime' => $ime];
if ($profilnaSlika) {
    $response['profilna_slika'] = $profilnaSlika;
}

echo json_encode($response);
?>

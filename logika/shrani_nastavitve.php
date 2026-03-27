<?php
require_once '../konfiguracija/seja.php';
require_once '../konfiguracija/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Neveljavna seja.']);
    exit();
}

$id    = (int)$_SESSION['user_id'];
$ime   = trim($_POST['ime']   ?? '');
$email = trim($_POST['email'] ?? '');

if (empty($ime) || empty($email)) {
    echo json_encode(['success' => false, 'error' => 'Ime in e-pošta sta obvezna.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Neveljaven e-poštni naslov.']);
    exit();
}

$stmt = $pdo->prepare("SELECT id_uporabnika FROM uporabniki WHERE email = ? AND id_uporabnika != ?");
$stmt->execute([$email, $id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Ta e-pošta je že v uporabi.']);
    exit();
}

$novoHash = null;
$trenutno = $_POST['trenutno_geslo'] ?? '';
$novo     = $_POST['novo_geslo']     ?? '';
$potrdi   = $_POST['potrdi_geslo']   ?? '';

if (!empty($novo)) {
    if (strlen($novo) < 6) {
        echo json_encode(['success' => false, 'error' => 'Geslo mora biti dolgo vsaj 6 znakov.']);
        exit();
    }
    if ($novo !== $potrdi) {
        echo json_encode(['success' => false, 'error' => 'Gesli se ne ujemata.']);
        exit();
    }
    $stmt = $pdo->prepare("SELECT hash_gesla FROM uporabniki WHERE id_uporabnika = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!password_verify($trenutno, $row['hash_gesla'])) {
        echo json_encode(['success' => false, 'error' => 'Trenutno geslo je napačno.']);
        exit();
    }
    $novoHash = password_hash($novo, PASSWORD_DEFAULT);
}

$profilnaSlika = null;
if (!empty($_FILES['profilna_slika']['name'])) {
    $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $tmpPath  = $_FILES['profilna_slika']['tmp_name'];
    $mimeType = mime_content_type($tmpPath);

    if (!in_array($mimeType, $allowed)) {
        echo json_encode(['success' => false, 'error' => 'Nepodprta vrsta slike (jpeg/png/webp).']);
        exit();
    }
    if ($_FILES['profilna_slika']['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'Slika je prevelika (max 2 MB).']);
        exit();
    }

    $ext       = strtolower(pathinfo($_FILES['profilna_slika']['name'], PATHINFO_EXTENSION));
    $filename  = $id . '_' . time() . '.' . $ext;
    $uploadDir = '../ostalo/slike/profil/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!move_uploaded_file($tmpPath, $uploadDir . $filename)) {
        echo json_encode(['success' => false, 'error' => 'Napaka pri nalaganju slike.']);
        exit();
    }

    $profilnaSlika = 'ostalo/slike/profil/' . $filename;
}

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
    $stmt = $pdo->prepare("UPDATE uporabniki SET uporabnisko_ime=?, email=? WHERE id_uporabnika=?");
    $stmt->execute([$ime, $email, $id]);
}

$_SESSION['username'] = $ime;

$response = ['success' => true, 'ime' => $ime];
if ($profilnaSlika) {
    $response['profilna_slika'] = $profilnaSlika;
}

echo json_encode($response);
?>

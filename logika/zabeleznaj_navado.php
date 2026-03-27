<?php
require_once '../konfiguracija/seja.php';
require_once '../konfiguracija/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Niste prijavljeni.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id_navade'])) {
    echo json_encode(['success' => false, 'error' => 'Neveljaven zahtevek.']);
    exit();
}

if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Neveljavna seja.']);
    exit();
}

$id_navade     = (int)$_POST['id_navade'];
$id_uporabnika = (int)$_SESSION['user_id'];

$datum = isset($_POST['datum']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['datum'])
    ? $_POST['datum']
    : date('Y-m-d');

$stmt = $pdo->prepare("SELECT id_navade FROM navade WHERE id_navade = ? AND id_uporabnika = ?");
$stmt->execute([$id_navade, $id_uporabnika]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Navada ni najdena.']);
    exit();
}

$stmt = $pdo->prepare("SELECT id_dnevnika, opravljeno FROM dnevniki WHERE id_navade = ? AND datum = ?");
$stmt->execute([$id_navade, $datum]);
$entry = $stmt->fetch();

if ($entry) {
    $new_value = $entry['opravljeno'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE dnevniki SET opravljeno = ? WHERE id_dnevnika = ?");
    $stmt->execute([$new_value, $entry['id_dnevnika']]);
    $logged = (bool)$new_value;
} else {
    $stmt = $pdo->prepare("INSERT INTO dnevniki (id_navade, datum, opravljeno) VALUES (?, ?, 1)");
    $stmt->execute([$id_navade, $datum]);
    $logged = true;
}

$stmt = $pdo->prepare("
    SELECT datum FROM dnevniki
    WHERE id_navade = ? AND opravljeno = 1
    ORDER BY datum DESC
");
$stmt->execute([$id_navade]);
$dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

$streak   = 0;
$expected = new DateTime('today');

foreach ($dates as $dateStr) {
    $d = new DateTime($dateStr);
    if ($d->format('Y-m-d') === $expected->format('Y-m-d')) {
        $streak++;
        $expected->modify('-1 day');
    } else {
        break;
    }
}

$stmt = $pdo->prepare("UPDATE navade SET streak = ? WHERE id_navade = ?");
$stmt->execute([$streak, $id_navade]);

echo json_encode([
    'success' => true,
    'logged'  => $logged,
    'streak'  => $streak,
]);
?>

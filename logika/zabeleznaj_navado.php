<?php
session_start();
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

$id_navade     = (int)$_POST['id_navade'];
$id_uporabnika = (int)$_SESSION['user_id'];

// Accept optional datum (for chart day-click); default to today
$datum = isset($_POST['datum']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['datum'])
    ? $_POST['datum']
    : date('Y-m-d');
$today = date('Y-m-d'); // still needed for streak calc reference

// Security: verify the habit belongs to this user
$stmt = $pdo->prepare("SELECT id_navade FROM navade WHERE id_navade = ? AND id_uporabnika = ?");
$stmt->execute([$id_navade, $id_uporabnika]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Navada ni najdena.']);
    exit();
}

// Check if there is already an entry for this date
$stmt = $pdo->prepare("SELECT id_dnevnika, opravljeno FROM dnevniki WHERE id_navade = ? AND datum = ?");
$stmt->execute([$id_navade, $datum]);
$entry = $stmt->fetch();

if ($entry) {
    // Toggle: flip opravljeno (1 → 0 or 0 → 1)
    $new_value = $entry['opravljeno'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE dnevniki SET opravljeno = ? WHERE id_dnevnika = ?");
    $stmt->execute([$new_value, $entry['id_dnevnika']]);
    $logged = (bool)$new_value;
} else {
    // No entry yet → insert as completed
    $stmt = $pdo->prepare("INSERT INTO dnevniki (id_navade, datum, opravljeno) VALUES (?, ?, 1)");
    $stmt->execute([$id_navade, $datum]);
    $logged = true;
}

// Recalculate streak: count consecutive completed days going backwards
$stmt = $pdo->prepare("
    SELECT datum FROM dnevniki
    WHERE id_navade = ? AND opravljeno = 1
    ORDER BY datum DESC
");
$stmt->execute(params: [$id_navade]);
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

// Save updated streak to navade table
$stmt = $pdo->prepare("UPDATE navade SET streak = ? WHERE id_navade = ?");
$stmt->execute([$streak, $id_navade]);

echo json_encode([
    'success' => true,
    'logged'  => $logged,
    'streak'  => $streak,
]);
?>

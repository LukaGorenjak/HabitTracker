<?php
require_once 'konfiguracija/seja.php';
require_once 'konfiguracija/db.php';

// Dostop samo za admina
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Statistike
$steviloUporabnikov = (int)$pdo->query("SELECT COUNT(*) FROM uporabniki")->fetchColumn();
$steviloNavad       = (int)$pdo->query("SELECT COUNT(*) FROM navade")->fetchColumn();
$steviloDnevnikov   = (int)$pdo->query("SELECT COUNT(*) FROM dnevniki WHERE opravljeno = 1")->fetchColumn();

// Vsi uporabniki z številom navad
$stmt = $pdo->query("
    SELECT u.id_uporabnika, u.uporabnisko_ime, u.email, u.vloga,
           u.ustvarjeno, COUNT(n.id_navade) AS stevilo_navad
    FROM uporabniki u
    LEFT JOIN navade n ON u.id_uporabnika = n.id_uporabnika
    GROUP BY u.id_uporabnika
    ORDER BY u.ustvarjeno DESC
");
$vsiUporabniki = $stmt->fetchAll();

// Za navigacija.php (zahteva $currentUser)
$stmt = $pdo->prepare("SELECT uporabnisko_ime, email, profilna_slika FROM uporabniki WHERE id_uporabnika = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – HabitFlow</title>
    <link rel="stylesheet" href="ostalo/style.css">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
</head>
<body class="dashboard-body">
<div class="layout" id="layout">
    <?php include 'deli_strani/navigacija.php'; ?>

    <div class="main-content">
        <div class="top-nav">
            <div class="top-nav-left">
                <button class="hamburger-btn" id="hamburgerBtn">&#9776;</button>
                <span class="nav-active-label">Admin panel</span>
            </div>
            <div class="top-nav-right">
                <span class="habit-focus-name">HabitFlow Admin</span>
            </div>
        </div>

        <div class="admin-content">

            <!-- Statistike -->
            <div class="admin-stats">
                <div class="admin-stat-card">
                    <div class="admin-stat-num"><?php echo $steviloUporabnikov; ?></div>
                    <div class="admin-stat-label">Registrirani uporabniki</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-num"><?php echo $steviloNavad; ?></div>
                    <div class="admin-stat-label">Navade skupaj</div>
                </div>
                <div class="admin-stat-card">
                    <div class="admin-stat-num"><?php echo $steviloDnevnikov; ?></div>
                    <div class="admin-stat-label">Opravljeni vnosi</div>
                </div>
            </div>

            <!-- Tabela uporabnikov -->
            <div class="admin-table-wrap">
                <h3 class="admin-section-title">Vsi uporabniki</h3>
                <div class="admin-table-scroll">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Uporabnik</th>
                            <th>E-pošta</th>
                            <th>Vloga</th>
                            <th>Navade</th>
                            <th>Registracija</th>
                            <th>Akcija</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($vsiUporabniki as $u): ?>
                        <tr id="user-row-<?php echo (int)$u['id_uporabnika']; ?>">
                            <td><?php echo (int)$u['id_uporabnika']; ?></td>
                            <td><?php echo htmlspecialchars($u['uporabnisko_ime']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <select class="admin-role-select"
                                        data-id="<?php echo (int)$u['id_uporabnika']; ?>"
                                        <?php if ((int)$u['id_uporabnika'] === (int)$_SESSION['user_id']) echo 'disabled title="Ne moreš spremeniti lastne vloge"'; ?>>
                                    <option value="uporabnik" <?php if ($u['vloga'] === 'uporabnik') echo 'selected'; ?>>Uporabnik</option>
                                    <option value="admin"     <?php if ($u['vloga'] === 'admin')     echo 'selected'; ?>>Admin</option>
                                </select>
                            </td>
                            <td><?php echo (int)$u['stevilo_navad']; ?></td>
                            <td><?php echo $u['ustvarjeno'] ? date('d.m.Y', strtotime($u['ustvarjeno'])) : '-'; ?></td>
                            <td>
                                <?php if ((int)$u['id_uporabnika'] !== (int)$_SESSION['user_id']): ?>
                                <button class="admin-delete-btn"
                                        data-id="<?php echo (int)$u['id_uporabnika']; ?>"
                                        data-name="<?php echo htmlspecialchars($u['uporabnisko_ime']); ?>">
                                    Izbriši
                                </button>
                                <?php else: ?>
                                <span class="admin-self-label">(ti)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // CSRF token
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // Stubi – navigacija.php kliče te funkcije, ki ne obstajajo na admin strani
    function openNastavitve() {}
    function setDelDnevaFilter() {}
    function filterByKategorija() {}
    function openNovaKategorija() {}

    // Hamburger za mobilni pogled
    document.getElementById('hamburgerBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('layout').classList.toggle('sidebar-open');
    });
    document.addEventListener('click', function() {
        document.getElementById('layout').classList.remove('sidebar-open');
    });
    document.querySelector('.sidebar').addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Sprememba vloge prek AJAX
    document.querySelectorAll('.admin-role-select').forEach(function(sel) {
        sel.addEventListener('change', function() {
            const id    = this.dataset.id;
            const vloga = this.value;
            fetch('logika/admin_vloga.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_uporabnika=' + encodeURIComponent(id) + '&vloga=' + encodeURIComponent(vloga) + '&csrf_token=' + encodeURIComponent(CSRF)
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) showToast('Vloga posodobljena.');
                else showToast('Napaka: ' + (d.error || 'Neznana napaka'), 'error');
            });
        });
    });

    // Brisanje uporabnika prek AJAX
    document.querySelectorAll('.admin-delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id   = this.dataset.id;
            const name = this.dataset.name;
            if (!confirm('Ste prepričani, da želite izbrisati uporabnika "' + name + '"?\nVse navade in dnevniki bodo trajno izgubljeni!')) return;
            fetch('logika/admin_izbrisi_uporabnika.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id_uporabnika=' + encodeURIComponent(id) + '&csrf_token=' + encodeURIComponent(CSRF)
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    document.getElementById('user-row-' + id).remove();
                } else {
                    showToast('Napaka: ' + (d.error || 'Neznana napaka'), 'error');
                }
            });
        });
    });
</script>
</body>
</html>

<?php
require_once 'konfiguracija/seja.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'konfiguracija/db.php';

$stmt = $pdo->prepare("SELECT uporabnisko_ime, email, profilna_slika FROM uporabniki WHERE id_uporabnika = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

$stmt = $pdo->prepare("SELECT id_kategorije, ime, barva FROM kategorije WHERE id_uporabnika = ? ORDER BY ime ASC");
$stmt->execute([$_SESSION['user_id']]);
$kategorijeList = $stmt->fetchAll();

$profilSlika = $currentUser['profilna_slika']
    ? htmlspecialchars($currentUser['profilna_slika'])
    : 'ostalo/slike/simple-white-circle-and-drop-shadow-png.png';
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil – Habit Flow</title>
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
                <span class="nav-active-label">Profil</span>
            </div>
        </div>

        <div class="main pomo-main">
            <div class="pomo-card profil-card">

                <!-- Profilna slika -->
                <div class="profil-slika-section">
                    <div class="profil-slika-wrap">
                        <img id="profilPreview" src="<?php echo $profilSlika; ?>" alt="Profil" class="profil-preview-img">
                        <label for="profilnaSlika" class="profil-slika-overlay">&#128247;</label>
                        <input type="file" id="profilnaSlika" name="profilna_slika" accept="image/*">
                    </div>
                </div>

                <!-- Forma -->
                <form id="profilForm" style="width:100%;">

                    <div class="form-group">
                        <label>Uporabniško ime</label>
                        <input type="text" id="profilIme" name="ime" class="form-input"
                            value="<?php echo htmlspecialchars($currentUser['uporabnisko_ime']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>E-pošta</label>
                        <input type="email" id="profilEmail" name="email" class="form-input"
                            value="<?php echo htmlspecialchars($currentUser['email']); ?>" required>
                    </div>

                    <div class="form-section-divider">Sprememba gesla <span>(neobvezno)</span></div>

                    <div class="form-group">
                        <label>Trenutno geslo</label>
                        <input type="password" name="trenutno_geslo" class="form-input" placeholder="Vnesite trenutno geslo">
                    </div>

                    <div class="form-group">
                        <label>Novo geslo</label>
                        <input type="password" name="novo_geslo" class="form-input" placeholder="Min. 6 znakov">
                    </div>

                    <div class="form-group">
                        <label>Potrdi novo geslo</label>
                        <input type="password" name="potrdi_geslo" class="form-input" placeholder="Ponovi novo geslo">
                    </div>

                    <div class="nastavitve-msg" id="profilError"   style="display:none;"></div>
                    <div class="nastavitve-msg nastavitve-success" id="profilSuccess" style="display:none;"></div>

                    <button type="submit" class="pomo-btn-start" style="width:100%; margin-top:8px;">Shrani spremembe</button>

                </form>

            </div>
        </div>
    </div>
</div>

<script>
    // Hamburger
    document.getElementById('hamburgerBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('layout').classList.toggle('sidebar-open');
    });
    document.addEventListener('click', function() {
        document.getElementById('layout').classList.remove('sidebar-open');
    });
    document.getElementById('sidebar').addEventListener('click', function(e) { e.stopPropagation(); });

    // Predogled slike pred nalaganjem
    document.getElementById('profilnaSlika').addEventListener('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilPreview').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // CSRF token
    var CSRF = document.querySelector('meta[name="csrf-token"]').content;

    // Pošlji formo z AJAX
    document.getElementById('profilForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var errorEl   = document.getElementById('profilError');
        var successEl = document.getElementById('profilSuccess');
        errorEl.style.display = successEl.style.display = 'none';

        var formData = new FormData(this);
        formData.append('csrf_token', CSRF);
        // Dodaj sliko ročno (ker input ni znotraj forme)
        var slikaInput = document.getElementById('profilnaSlika');
        if (slikaInput.files[0]) {
            formData.append('profilna_slika', slikaInput.files[0]);
        }

        fetch('logika/shrani_nastavitve.php', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    errorEl.textContent   = data.error;
                    errorEl.style.display = 'block';
                    return;
                }
                // Posodobi sidebar ime in sliko takoj
                var sidebarIme = document.querySelector('.profile h2');
                if (sidebarIme) sidebarIme.textContent = data.ime;
                if (data.profilna_slika) {
                    var sidebarImg = document.querySelector('.profile img');
                    if (sidebarImg) sidebarImg.src = data.profilna_slika;
                    document.getElementById('profilPreview').src = data.profilna_slika;
                }
                successEl.textContent   = 'Profil je bil uspešno posodobljen!';
                successEl.style.display = 'block';
            })
            .catch(function() {
                errorEl.textContent   = 'Napaka pri shranjevanju.';
                errorEl.style.display = 'block';
            });
    });
</script>
</body>
</html>

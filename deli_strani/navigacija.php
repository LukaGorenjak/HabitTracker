<?php
$username    = isset($_SESSION['username']) ? $_SESSION['username'] : 'Gost';
$profilSlika = isset($currentUser['profilna_slika']) && $currentUser['profilna_slika']
    ? htmlspecialchars($currentUser['profilna_slika'])
    : 'ostalo/slike/simple-white-circle-and-drop-shadow-png.png';
$jeAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<div class="sidebar" id="sidebar">

    <!-- Profil – klik odpre nastavitve (stari izgled) -->
    <div class="profile-container">
        <div class="profile" onclick="openNastavitve()">
            <img src="<?php echo $profilSlika; ?>" alt="Profil" class="sidebar-avatar">
            <h2><?php echo htmlspecialchars($username); ?></h2>
        </div>
    </div>

    <!-- Navade + filtri po delu dneva -->
    <h2 class="sidebar-section-title">Navade</h2>
    <ul class="sidebar-list">
        <li><a href="index.php" class="sidebar-link sidebar-link-all">Vse navade</a></li>
        <li><a href="#" class="sidebar-link sidebar-time-btn" data-filter="zjutraj"
               onclick="setDelDnevaFilter('zjutraj'); return false;">Zjutraj</a></li>
        <li><a href="#" class="sidebar-link sidebar-time-btn" data-filter="popoldne"
               onclick="setDelDnevaFilter('popoldne'); return false;">Popoldne</a></li>
        <li><a href="#" class="sidebar-link sidebar-time-btn" data-filter="zvecer"
               onclick="setDelDnevaFilter('zvecer'); return false;">Zvečer</a></li>
    </ul>

    <!-- Kategorije – samo gumb za dodajanje -->
    <div class="sidebar-section-header">
        <h2 class="sidebar-section-title">Kategorije</h2>
        <button class="sidebar-add-btn" onclick="openNovaKategorija()" title="Nova kategorija">+</button>
    </div>

    <!-- Mini forma za novo kategorijo -->
    <div class="nova-kategorija-panel" id="novaKategorijaPanel">
        <form id="novaKategorijaFormEl">
            <input type="text" id="novaKatIme" class="sidebar-mini-input" placeholder="Ime kategorije" maxlength="30">
            <input type="color" id="novaKatBarva" value="#4a9d6f" class="sidebar-color-pick">
            <button type="submit" class="sidebar-mini-save">Dodaj</button>
        </form>
    </div>

    <?php if ($jeAdmin): ?>
    <h2 class="sidebar-section-title">Admin</h2>
    <ul class="sidebar-list">
        <li><a href="admin.php" class="sidebar-link">Admin panel</a></li>
    </ul>
    <?php endif; ?>

    <h2 class="sidebar-section-title">Ostalo
    </h2>
    <ul class="sidebar-list">
        <li><a href="#" onclick="openStatistika(); return false;" class="sidebar-link">Statistika</a></li>
        <li><a href="#" onclick="openNastavitve(); return false;" class="sidebar-link">Uredi profil</a></li>
        <li><a href="avtentikacija/odjava.php" class="sidebar-link logout-link">Odjava</a></li>
    </ul>

</div>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script>
    document.getElementById('sidebarOverlay').addEventListener('click', function() {
        var layout = document.getElementById('layout');
        if (layout) layout.classList.remove('sidebar-open');
    });
</script>

<?php
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Gost';
?>


<div class="sidebar">
    <div class="profile-container">
        <div class="profile" id="profileToggle">
            <img src="ostalo/slike/simple-white-circle-and-drop-shadow-png.png" alt="Profil">
            <h2><?php echo htmlspecialchars($username); ?></h2>
            <span class="profile-arrow">▼</span>
        </div>
        
        <div class="profile-dropdown" id="sideDropdown">
            <a href="profil_nastavitve.php">⚙️ Nastavitve</a>
            <a href="avtentikacija/odjava.php" class="logout-link">🚪 Odjava</a>
        </div>
    </div>

    <div class="habits-section">
        <h2>Navade</h2>
        <ul>
            <li><a href="#">Vse navade</a></li>
            <li><a href="#">Popoldne</a></li>
        </ul>
    </div>

    <h2>Področja</h2>
    <ul>
        <li><a href="#">Novo področje</a></li>
    </ul>

    <h2>Nastavitve</h2>
    <ul>
        <li><a href="#">Uredi navade</a></li>
        <li><a href="#">Nastavitve aplikacije</a></li>
        <li><a href="#">Viri</a></li>
    </ul>
</div>

<script>
    document.getElementById('profileToggle').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('sideDropdown').classList.toggle('active');
    });

    document.addEventListener('click', function() {
        document.getElementById('sideDropdown').classList.remove('active');
    });
</script>
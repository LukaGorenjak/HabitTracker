<?php
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Gost';
?>

<style>
    .sidebar {
        width: 270px;
        height: 100vh;
        background: #2b3a2f;
        padding: 20px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
    }

    .profile-container {
        position: relative;
        margin-bottom: 20px;
    }

    .sidebar .profile {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        gap: 10px;
        padding: 10px;
        background-color: #3b4d3f;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.3s;
        border: 1px solid rgba(0,0,0,0.1);
    }

    .sidebar .profile:hover {
        background-color: #2b3a2f;
    }

    .sidebar .profile img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        border: 1px solid #222;
        object-fit: cover;
    }

    .sidebar .profile h2 {
        font-size: 16px;
        margin: 0;
        color: #fff;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .profile-dropdown {
        display: none;
        background: #3b4d3f;
        margin-top: 5px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .profile-dropdown.active {
        display: block;
        animation: slideDown 0.3s ease-out;
    }

    .profile-dropdown a {
        font-size: 16px !important;
        padding: 12px 15px !important;
        margin-bottom: 0 !important;
        border-radius: 0 !important;
        color: #fff !important;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .profile-dropdown a:hover {
        background-color: #2b3a2f !important;
    }

    .logout-link {
        color: #ff9d9d !important; 
    }

    .habit-section h2, .sidebar h2 {
        font-size: 14px;
        margin: 20px 0 10px 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.8;
        color: #fff;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin-bottom: 20px;
    }

    .sidebar a {
        font-size: 18px;
        text-decoration: none;
        color: #fff;
        padding: 12px 15px;
        margin-bottom: 5px;
        display: block;
        border-radius: 10px;
        transition: all 0.3s ease;
    }

    .sidebar a:hover {
        background-color: #3b4d3f;
        color: #fff;
    }

    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="sidebar">
    <div class="profile-container">
        <div class="profile" id="profileToggle">
            <img src="ostalo/slike/simple-white-circle-and-drop-shadow-png.png" alt="Profil">
            <h2><?php echo htmlspecialchars($username); ?></h2>
            <span style="font-size: 10px; margin-left: auto;">▼</span>
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
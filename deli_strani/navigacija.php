<style>
    .nav {
        width: 220px;
        height: 100vh;
        background: #667c59;
        padding: 20px;
    }

    .nav .profile {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        gap: 10px;
        padding: 10px;
        margin-bottom: 20px;
        background-color: #5d6f53;
        border-radius: 10px;
    }

    .nav .profile img {
        width: 60px;
        height: 60px;
        border-radius: 50%;
    }

    .habit-section h2 {
        font-size: 18px;
        margin-bottom: 30px;
    }

    .habit-section h2:hover {
    }

    .nav ul {
        list-style: none;
        padding: 0;
        margin-bottom: 40px;

    }

    .nav a {
        font-size:22px;
        text-decoration: none;
        color: #333;
        padding: 15px;
        display: block;
    }

    .nav a:hover {
        background-color: #79af5a;
        border-radius: 10px;
    }
</style>
<div class="nav">
    <div class="profile">
        <img src="ostalo/slike/simple-white-circle-and-drop-shadow-png.png">
        <h2>Uporabnik</h2>
    </div>
    <div class="habits-section">
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
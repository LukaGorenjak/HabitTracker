<?php
session_start();
require 'C:\xampp\htdocs\HabitTracker\konfiguracija\db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_name = trim($_POST['ime_ali_email']);
    $password   = $_POST['geslo'];

    if (empty($login_name) || empty($password)) {
        $error = "Prosim, vnesi vse podatke.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM uporabniki WHERE uporabnisko_ime = ? OR email = ?");
        $stmt->execute([$login_name, $login_name]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['hash_gesla'])) {
            $_SESSION['user_id']  = $user['id_uporabnika'];
            $_SESSION['username'] = $user['uporabnisko_ime'];
            $_SESSION['role']     = $user['vloga'];
            header("Location: ../index.php");
            exit;
        } else {
            $error = "Napačno uporabniško ime ali geslo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava – Habit Flow</title>
    <link rel="stylesheet" href="../ostalo/style.css">
</head>
<body class="auth-body">
    <div class="auth-center">
        <div class="auth-card">

            <div class="auth-logo">Habit Flow</div>
            <h1 class="auth-title">Dobrodošli nazaj</h1>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="prijava.php">
                <div class="form-group">
                    <label for="email">E-pošta ali uporabniško ime</label>
                    <input type="text" id="email" name="ime_ali_email" placeholder="Vnesite e-pošto ali ime" required>
                </div>
                <div class="form-group">
                    <label for="password">Geslo</label>
                    <input type="password" id="password" name="geslo" placeholder="Vnesite geslo" required>
                </div>
                <button type="submit" class="btn-submit">Prijavi se</button>
            </form>

            <div class="auth-link">Še nimate računa? <a href="registracija.php">Registracija</a></div>

        </div>
    </div>
</body>
</html>

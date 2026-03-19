<?php
// Zaženi sejo (pred vsem ostalim)
session_start();
// Absolutna pot do db.php (zanesljiva ne glede na CWD)
require 'C:\xampp\htdocs\HabitTracker\konfiguracija\db.php';

$error = ''; // spremenljivka za prikaz napake v HTML

// Obdelaj obrazec samo ob POST zahtevi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // trim() odstrani presledke; sprejme uporabniško ime ALI e-pošto
    $login_name = trim($_POST['ime_ali_email']);
    $password = $_POST['geslo'];

    // Oba polja morata biti izpolnjena
    if (empty($login_name) || empty($password)) {
        $error = "Prosim, vnesi vse podatke.";
    } else {
        // Poišči uporabnika po imenu ALI e-pošti (en poizvedba pokrije oba primera)
        $stmt = $pdo->prepare("SELECT * FROM uporabniki WHERE uporabnisko_ime = ? OR email = ?");
        // Isti $login_name pošljemo dvakrat – enkrat za vsak ?
        $stmt->execute([$login_name, $login_name]);

        $user = $stmt->fetch(); // vrne associativni niz z vsemi stolpci ali false

        // password_verify() primerja vneseno geslo z bcrypt hashom iz baze
        if ($user && password_verify($password, $user['hash_gesla'])) {
            // Geslo se ujema → shrani podatke v sejo
            $_SESSION['user_id'] = $user['id_uporabnika'];     // primarni ključ
            $_SESSION['username'] = $user['uporabnisko_ime'];  // prikazno ime
            $_SESSION['role'] = $user['vloga'];                // vloga (za morebitne pravice)

            // Preusmeri na glavno stran
            header("Location: ../index.php");
            exit; // zaustavi izvajanje (ne pošlji HTML)
        } else {
            // Napačno geslo ali uporabnik ne obstaja
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

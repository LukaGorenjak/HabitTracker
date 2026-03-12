<?php
session_start();
require 'C:\xampp\htdocs\HabitTracker\konfiguracija\db.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_name = trim($_POST['ime_ali_email']);
    $password = $_POST['geslo'];

    if (empty($login_name) || empty($password)) {
        $error = "Prosim, vnesi vse podatke.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM uporabniki WHERE uporabnisko_ime = ? OR email = ?");
        $stmt->execute([$login_name, $login_name]);
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['hash_gesla'])) {
            
            $_SESSION['user_id'] = $user['id_uporabnika'];
            $_SESSION['username'] = $user['uporabnisko_ime'];
            $_SESSION['role'] = $user['vloga'];

            header("Location: ../index.php");
            exit;
        } else {
            $error = "Napačno uporabniško ime ali geslo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava</title>
    <link rel="stylesheet" href="../ostalo/style.css">
</head>
<body class="auth-body">
    <div class="container">
        <div class="left">
            <div class="testimonial">
                <div class="testimonial-quote">"66chat je res izjemen. Omogoča prilagodljivost in širok nabor raziskovalnih metod ter oblikovanja študij."</div>
                <div class="testimonial-author">Pablo Escanor – UX raziskovalec</div>
            </div>
        </div>
        <div class="right">
            <div class="form-box">
                <h1>Dobrodošli v HabitFlow!</h1>

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
                    <button type="submit">Prijavi se</button>
                </form>

                <div class="divider">ali</div>
                <div class="login-link">Še nimate računa? <a href="registracija.php">Registracija</a></div>
                <button class="social-btn"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg" alt="Google"> Nadaljuj z Google</button>
                <button class="social-btn"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apple/apple-original.svg" alt="Apple"> Nadaljuj z Apple</button>
                <button class="social-btn"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/twitter/twitter-original.svg" alt="X"> Nadaljuj z X</button>
            </div>
        </div>
    </div>
</body>
</html>
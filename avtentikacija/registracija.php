<?php
session_start();
require 'C:\xampp\htdocs\HabitTracker\konfiguracija\db.php'; 

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $name = trim($_POST['ime']);
    $email = trim($_POST['email']);
    $password = $_POST['geslo'];
    $confirm_password = $_POST['potrdi_geslo'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "Prosim, izpolni vsa obvezna polja.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Neveljaven e-poštni naslov.";
    } elseif ($password !== $confirm_password) {
        $error = "Gesli se ne ujemata!";
    } elseif (strlen($password) < 6) {
        $error = "Geslo mora biti dolgo vsaj 6 znakov.";
    } else {

        $stmt = $pdo->prepare("SELECT id_uporabnika FROM uporabniki WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Uporabnik s tem e-poštnim naslovom že obstaja.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO uporabniki (uporabnisko_ime, email, hash_gesla, vloga) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            $role = 'uporabnik';
            
            if ($stmt->execute([$name, $email, $password_hash, $role])) {
                $success = "Registracija je uspešna! Preusmerjam na prijavo...";
                header("refresh:2;url=prijava.php"); 
            } else {
                $error = "Prišlo je do napake pri shranjevanju v bazo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija - HabitFlow</title>
    <link rel="stylesheet" href="../ostalo/style.css">
</head>
<body class="auth-body">
    <div class="container">
        <div class="left">
            <div class="testimonial">
                <div class="testimonial-quote">"HabitFlow je res izjemen. Omogoča mi, da na enem mestu učinkovito sledim svojim ciljem in ohranjam motivacijo vsak dan."</div>
                <div class="testimonial-author">Luka G. – Zvesti uporabnik</div>
            </div>
        </div>
        <div class="right">
            <div class="form-box">
                <h1>Dobrodošli v HabitFlow!</h1>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="ime">Uporabniško ime</label>
                        <input type="text" id="ime" name="ime" placeholder="Vnesite uporabniško ime" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-pošta</label>
                        <input type="email" id="email" name="email" placeholder="Vnesite e-pošto" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="geslo">Geslo</label>
                        <input type="password" id="geslo" name="geslo" placeholder="Vnesite geslo" required>
                    </div>
                    <div class="form-group">
                        <label for="potrdi_geslo">Potrdi geslo</label>
                        <input type="password" id="potrdi_geslo" name="potrdi_geslo" placeholder="Ponovno vnesite geslo" required>
                    </div>
                    <button type="submit" class="btn-submit">Ustvari račun</button>
                </form>

                <div class="divider">ali</div>
                
                <button class="social-btn"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/google/google-original.svg" alt="Google"> Nadaljuj z Google</button>
                <button class="social-btn"><img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apple/apple-original.svg" alt="Apple"> Nadaljuj z Apple</button>
                
                <div class="login-link">Že imate račun? <a href="prijava.php">Prijava</a></div>
            </div>
        </div>
    </div>
</body>
</html>
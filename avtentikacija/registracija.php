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
    <link href="https://fonts.googleapis.com/css2?family=Fjalla+One&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            font-family: 'Fjalla One', Arial, sans-serif;
            font-style: normal;
            background: #0a0f0d;
            overflow: hidden;
        }
        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .left {
            flex: 1;
            background: #0a0f0d;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            height: 100vh;
        }
        .testimonial {
            max-width: fit-content;
            background: #1a1a1a;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            padding: 32px;
            color: #f5f3e7;
        }
        .testimonial-quote {
            font-size: 22px;
            font-style: italic;
            margin-bottom: 24px;
        }
        .testimonial-author {
            font-size: 16px;
            color: #aaa;
        }
        .right {
            flex: 1;
            background: #0a0f0d;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            height: 100vh;
        }
        .form-box {
            width: 100%;
            max-width: 400px;
            background: #1a1a1a;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            padding: 32px;
            border: 1px solid #333;
        }
        .form-box h1 {
            font-size: 28px;
            margin-bottom: 24px;
            text-align: center;
            font-weight: 750;
            color: #f5f3e7;
        }
        
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 18px;
            font-size: 15px;
            text-align: center;
        }
        .alert-error {
            background-color: #3d1a1a;
            color: #ff9d9d;
            border: 1px solid #5a2a2a;
        }
        .alert-success {
            background-color: #1a3a2a;
            color: #7dc89f;
            border: 1px solid #2a5a3a;
        }

        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 15px;
            color: #f5f3e7;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 6px;
            font-size: 15px;
            box-sizing: border-box;
            background: #2b3a2f;
            color: #f5f3e7;
        }
        .form-box button.btn-submit {
            width: 100%;
            padding: 12px;
            background: #2b5a4a;
            color: #f5f3e7;
            border: 2px solid #4a9d6f;
            border-radius: 6px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            font-family: 'Fjalla One', Arial, sans-serif;
            transition: background 0.3s ease;
        }
        .form-box button.btn-submit:hover {
            background: #4a9d6f;
        }
        .divider {
            text-align: center;
            margin: 18px 0;
            color: #666;
        }
        .social-btn {
            width: 100%;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 6px;
            font-size: 15px;
            margin-bottom: 10px;
            background: #2b3a2f;
            color: #f5f3e7;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Fjalla One', Arial, sans-serif;
            transition: background 0.3s ease;
        }
        .social-btn:hover {
            background: #3b4d3f;
        }
        .social-btn img {
            width: 22px;
            height: 22px;
            margin-right: 8px;
        }
        .form-box .login-link {
            text-align: center;
            margin-top: 12px;
            font-size: 14px;
            color: #aaa;
        }
        .form-box .login-link a {
            color: #7dc89f;
            text-decoration: none;
        }
    </style>
</head>
<body>
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
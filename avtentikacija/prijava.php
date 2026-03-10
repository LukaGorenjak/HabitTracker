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
        }
        .testimonial {
            max-width: fit-content;
            height: 80vh;
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
        .form-group {
            margin-bottom: 18px;
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
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 15px;
            color: #f5f3e7;
        }
        .error-msg {
            color: #ff9d9d;
            background: #3d1a1a;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
            border: 1px solid #5a2a2a;
        }
        .form-box button {
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
            transition: background 0.3s, color 0.3s;
        }
        .form-box button:hover {
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
            transition: background 0.3s;
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
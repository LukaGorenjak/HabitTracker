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
            background: #fafbfc;
            overflow: hidden;
        }
        .container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .left {
            flex: 1;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .testimonial {
            max-width: fit-content;
            height: 80vh;
            max-height: auto;
            background: #f5f6fa;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 32px;
        }
        .testimonial-quote {
            font-size: 22px;
            font-style: italic;
            margin-bottom: 24px;
        }
        .testimonial-author {
            font-size: 16px;
            color: #555;
        }
        .right {
            flex: 1;
            background: #fafbfc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .form-box {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            padding: 32px;
        }
        .form-box h1 {
            font-size: 28px;
            margin-bottom: 24px;
            text-align: center;
            font-weight: 750;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 15px;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
        }
        .form-box button {
            width: 100%;
            padding: 12px;
            background: #08d444;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            font-family: 'Fjalla One', Arial, sans-serif;

        }
        .divider {
            text-align: center;
            margin: 18px 0;
            color: #888;
        }
        .social-btn {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            margin-bottom: 10px;
            background: #fff;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Fjalla One', Arial, sans-serif;
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
        }
        .form-box .login-link a {
            color: #08d444;
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
                <form>
                    <div class="form-group">
                        <label for="email">E-pošta</label>
                        <input type="email" id="email" name="email" placeholder="Vnesite e-pošto">
                    </div>
                    <div class="form-group">
                        <label for="password">Geslo</label>
                        <input type="password" id="password" name="password" placeholder="Vnesite geslo">
                    </div>
                    <button type="submit">Ustvari račun</button>
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
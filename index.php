<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Flow</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Fjalla+One&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: url('ostalo/slike/kendal-hnysCJrPpkc-unsplash.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            font-family: 'Fjalla One', Arial, sans-serif;
        }
        .nav {
            display: flex;
            justify-content: center;
            gap: 500px;
            margin-top: 32px;
        }
        .nav-btn {
            padding: 12px 36px;
            border: none;
            /* border: 2px solid #fff;
            border-radius: 30px; */
            background: transparent;
            color: #fff;
            font-size: 20px;
            font-family: 'Fjalla One', Arial, sans-serif;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .nav-btn:hover {
            background: rgba(255,255,255,0.1);
            color: #e0e0e0;
        }
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            height: 70vh;
            margin-left: 80px;
        }
        .subtitle {
            font-size: 22px;
            margin-bottom: 16px;
            font-family: 'Fjalla One', Arial, sans-serif;
        }
        .title {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 64px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .desc {
            font-size: 20px;
            max-width: 600px;
            margin-bottom: 32px;
        }
        .register-btn {
            position: absolute;
            right: 80px;
            bottom: 80px;
            padding: 12px 36px;
            border: 2px solid #fff;
            border-radius: 30px;
            height: 60px;
            width: 350px;
            background: transparent;
            color: #fff;
            font-size: 20px;
            font-family: 'Fjalla One', Arial, sans-serif;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .register-btn:hover {
            background: rgba(255,255,255,0.1);
            color: #e0e0e0;
        }
        @media (max-width: 700px) {
            .main-content {
                margin-left: 20px;
                height: auto;
            }
            .register-btn {
                right: 20px;
                bottom: 20px;
            }
            .title {
                font-size: 36px;
            }
        }
    </style>
</head>
<body>
    <div class="nav">
        <button class="nav-btn">Home</button>
        <button class="nav-btn">About us</button>
        <button class="nav-btn">Features</button>
    </div>
    <div class="main-content">
        <div class="subtitle">Gorenjak production</div>
        <div class="title">Habit Flow</div>
        <div class="desc">A night of inspiration, connection, and a chance to make a real impact</div>
    </div>
    <button class="register-btn">Register</button>
</body>
</html>
</html>
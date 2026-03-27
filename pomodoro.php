<?php
require_once 'konfiguracija/seja.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

require_once 'konfiguracija/db.php';

$stmt = $pdo->prepare("SELECT uporabnisko_ime, email, profilna_slika FROM uporabniki WHERE id_uporabnika = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

$stmt = $pdo->prepare("SELECT id_kategorije, ime, barva FROM kategorije WHERE id_uporabnika = ? ORDER BY ime ASC");
$stmt->execute([$_SESSION['user_id']]);
$kategorijeList = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pomodoro – Habit Flow</title>
    <link rel="stylesheet" href="ostalo/style.css">
</head>
<body class="dashboard-body">
<div class="layout" id="layout">

    <?php include 'deli_strani/navigacija.php'; ?>

    <div class="main-content">
        <div class="top-nav">
            <div class="top-nav-left">
                <button class="hamburger-btn" id="hamburgerBtn">&#9776;</button>
                <span class="nav-active-label">Pomodoro</span>
            </div>
        </div>

        <div class="main pomo-main">
            <div class="pomo-card">

                <!-- Izbira načina -->
                <div class="pomo-modes">
                    <button class="pomo-mode-btn pomo-active" onclick="setMode(25, 'Fokus', this)">Fokus</button>
                    <button class="pomo-mode-btn" onclick="setMode(5,  'Kratki odmor', this)">Kratki odmor</button>
                    <button class="pomo-mode-btn" onclick="setMode(15, 'Dolgi odmor', this)">Dolgi odmor</button>
                </div>

                <!-- Čas -->
                <div class="pomo-time" id="pomoTime">25:00</div>
                <div class="pomo-label" id="pomoLabel">Fokus</div>

                <!-- Progres vrstica -->
                <div class="pomo-bar-wrap">
                    <div class="pomo-bar-fill" id="pomoBar"></div>
                </div>

                <!-- Gumbi -->
                <div class="pomo-btns">
                    <button class="pomo-btn-start" id="pomoStartBtn" onclick="toggleTimer()">Start</button>
                    <button class="pomo-btn-reset" onclick="resetTimer()">Reset</button>
                </div>

                <!-- Seje -->
                <div class="pomo-sessions">
                    Seja <span id="pomoSession">1</span> / 4
                    <div class="pomo-dots">
                        <span class="pomo-dot pomo-dot-on" id="dot1"></span>
                        <span class="pomo-dot" id="dot2"></span>
                        <span class="pomo-dot" id="dot3"></span>
                        <span class="pomo-dot" id="dot4"></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    // --- Spremenljivke ---
    var skupajSekund = 25 * 60;
    var preostalo = 25 * 60;
    var tece = false;
    var timer = null;
    var seja = 1;

    // --- Nastavi način (fokus / odmor) ---
    function setMode(minute, naziv, gumb) {
        clearInterval(timer);
        tece         = false;
        skupajSekund = minute * 60;
        preostalo    = minute * 60;

        document.getElementById('pomoStartBtn').textContent = 'Start';
        document.getElementById('pomoLabel').textContent = naziv;
        document.getElementById('pomoBar').style.width = '100%';

        document.querySelectorAll('.pomo-mode-btn').forEach(function(b) {
            b.classList.remove('pomo-active');
        });
        gumb.classList.add('pomo-active');

        prikaziCas();
    }

    // --- Start / Pavza ---
    function toggleTimer() {
        if (tece) {
            clearInterval(timer);
            tece = false;
            document.getElementById('pomoStartBtn').textContent = 'Nadaljuj';
        } else {
            tece = true;
            document.getElementById('pomoStartBtn').textContent = 'Pavza';
            timer = setInterval(function() {
                preostalo--;
                prikaziCas();
                osvežiBar();
                if (preostalo <= 0) {
                    clearInterval(timer);
                    tece = false;
                    document.getElementById('pomoStartBtn').textContent = 'Start';
                    konecTimerja();
                }
            }, 1000);
        }
    }

    // --- Reset ---
    function resetTimer() {
        clearInterval(timer);
        tece      = false;
        preostalo = skupajSekund;
        document.getElementById('pomoStartBtn').textContent = 'Start';
        document.getElementById('pomoBar').style.width = '100%';
        prikaziCas();
    }

    // --- Pokaži čas v formatu MM:SS ---
    function prikaziCas() {
        var m = Math.floor(preostalo / 60);
        var s = preostalo % 60;
        var besedilo = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        document.getElementById('pomoTime').textContent = besedilo;
        document.title = besedilo + ' – Habit Flow';
    }

    // --- Posodobi progres vrstico ---
    function osvežiBar() {
        var procent = (preostalo / skupajSekund) * 100;
        document.getElementById('pomoBar').style.width = procent + '%';
    }

    // --- Ko timer doseže 0 ---
    function konecTimerja() {
        if (document.getElementById('pomoLabel').textContent === 'Fokus') {
            if (seja >= 4) {
                seja = 1;
            } else {
                seja++;
            }
            osvežiTocke();
        }
    }

    // --- Posodobi prikaz seje in pike ---
    function osvežiTocke() {
        document.getElementById('pomoSession').textContent = seja;
        for (var i = 1; i <= 4; i++) {
            var tocka = document.getElementById('dot' + i);
            if (i <= seja) {
                tocka.classList.add('pomo-dot-on');
            } else {
                tocka.classList.remove('pomo-dot-on');
            }
        }
    }

    // --- Hamburger meni ---
    document.getElementById('hamburgerBtn').addEventListener('click', function(e) {
        e.stopPropagation();
        document.getElementById('layout').classList.toggle('sidebar-open');
    });
    document.addEventListener('click', function() {
        document.getElementById('layout').classList.remove('sidebar-open');
    });
    document.getElementById('sidebar').addEventListener('click', function(e) {
        e.stopPropagation();
    });

</script>
</body>
</html>

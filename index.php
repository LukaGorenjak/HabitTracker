<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Flow</title>
    <link href="https://fonts.googleapis.com/css2?family=Fjalla+One&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    
    <style>
        /* ===========================================
           COMMON STYLES
           ========================================= */
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Fjalla One', Arial, sans-serif;
        }

        /* ===========================================
           STYLES FOR NON-LOGGED IN (LANDING PAGE)
           ========================================= */
        .landing-body {
            min-height: 100vh;
            background: url('ostalo/slike/kendal-hnysCJrPpkc-unsplash.jpg') no-repeat center center fixed;
            background-size: cover;
            background-color: #0a0f0d;
            color: #fff;
        }
        .landing-nav {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 32px;
        }
        @media (min-width: 1200px) {
            .landing-nav { gap: 500px; }
        }
        .landing-nav-btn {
            padding: 12px 36px;
            border: none;
            background: transparent;
            color: #fff;
            font-size: 20px;
            font-family: 'Fjalla One', Arial, sans-serif;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .landing-nav-btn:hover {
            background: rgba(255,255,255,0.1);
            color: #e0e0e0;
        }
        .landing-main-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            height: 70vh;
            margin-left: 80px;
        }
        .landing-subtitle {
            font-size: 22px;
            margin-bottom: 16px;
        }
        .landing-title {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 64px;
            font-weight: 700;
            margin-bottom: 16px;
        }
        .landing-desc {
            font-size: 20px;
            max-width: 600px;
            margin-bottom: 32px;
        }
        .register-btn {
            position: absolute;
            right: 80px;
            bottom: 80px;
            padding: 0;
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
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-sizing: border-box;
        }
        .register-btn:hover {
            background: rgba(255,255,255,0.1);
            color: #e0e0e0;
        }

        @media (max-width: 700px) {
            .landing-main-content {
                margin-left: 20px;
                height: auto;
                margin-top: 50px;
            }
            .register-btn {
                position: static;
                margin: 40px 20px;
                width: calc(100% - 40px);
            }
            .landing-title {
                font-size: 36px;
            }
        }

        /* ===========================================
           STYLES FOR LOGGED IN (DASHBOARD)
           ========================================= */
        .dashboard-body {
            background: #f5f3e7;
            height: 100vh;
            overflow: hidden;
        }
        .layout {
            display: flex;
            min-height: 100vh;
            height: 100vh;
        }
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #0a0f0d;
            height: 100vh;
        }
        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2b3a2f;
            color: #fff;
            padding: 0 24px;
            height: 8%;
            border-left: 2px solid #222;
            border-right: 2px solid #222;
        }
        .top-nav-left, .top-nav-right {
            display: flex;
            align-items: center;
            height: 100%;
        }
        .top-nav-right {
            gap: 18px;
            border-left: 2px solid #222;
        }
        .habit-focus-name {
            font-size: 20px;
            font-weight: bold;
            padding-left: 30px;
            margin-right: 285px;
        }
        .icon-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
            margin-right: 8px;
        }
        .icon-btn:last-child {
            margin-right: 0;
        }
        .calendar-date {
            font-size: 16px;
            margin-left: 6px;
            margin-right: 12px;
            color: #fff;
        }
        .nav-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 18px;
            font-family: 'Fjalla One', Arial, sans-serif;
            margin-right: 32px;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .nav-btn.active {
            border: 2px solid #3b4d3f;
        }
        .nav-btn:hover {
            background: #3b4d3f;
        }
        .search {
            margin-right: 32px;
            font-size: 22px;
            color: #fff;
        }
        .main {
            display: flex;
            flex: 1;
            min-height: 0;
            height: calc(100vh - 56px);
        }
        .habit-list {
            flex: 2;
            background: #0a0f0d;
            padding: 32px 24px;
            overflow-y: auto;
            border: 2px solid #222;
        }
        .habit-item {
            display: flex;
            align-items: center;
            background: #3b4d3f;
            color: #fff;
            border-radius: 8px;
            margin-bottom: 16px;
            padding: 12px 16px;
            font-size: 18px;
        }
        .habit-circle {
            width: 32px;
            height: 32px;
            background: #2b3a2f;
            border-radius: 50%;
            margin-right: 16px;
        }
        .habit-name {
            flex: 1;
        }
        .habit-log {
            margin-right: 16px;
            cursor: pointer;
        }
        .habit-menu {
            font-size: 22px;
            cursor: pointer;
        }
        .detail-panel {
            flex: 1;
            background: #1a1a1a;
            padding: 32px 24px;
            color: #f5f3e7; 
            border-top: 2px solid #222;
        }
        .detail-title {
            font-size: 24px;
            font-family: 'Playfair Display', serif;
            margin-bottom: 24px;
        }
        .detail-streak {
            background: #2b3a2f;
            color: #f5f3e7;
            border-radius: 8px;
            padding: 18px 0;
            font-size: 22px;
            display: block;
            width: 100%;
            text-align: center;
            margin-bottom: 16px;
            margin-top: 8px;
            font-weight: bold;
            border: 1px solid #3b4d3f;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.2);
            z-index: 5;
        }
        .overlay.active {
            display: block;
        }
    </style>
</head>
<!-- ============================================= NON-LOGGED IN HTML ============================================== -->
<?php if (!$isLoggedIn): ?>
<body class="landing-body">
    <div class="landing-nav">
        <button class="landing-nav-btn">Home</button>
        <button class="landing-nav-btn">About us</button>
        <button class="landing-nav-btn">Features</button>
    </div>
    <div class="landing-main-content">
        <div class="landing-subtitle">Gorenjak production</div>
        <div class="landing-title">Habit Flow</div>
        <div class="landing-desc">A night of inspiration, connection, and a chance to make a real impact</div>
    </div>
    <a href="avtentikacija/registracija.php" class="register-btn">Register</a>
</body>

<!-- ============================================= LOGGED IN HTML ============================================== -->
<?php else: ?>
<body class="dashboard-body">
    <div class="layout">
        <?php include 'deli_strani/navigacija.php'; ?>
        
        <div class="main-content">
            <div class="top-nav">
                <div class="top-nav-left">
                    <button class="nav-btn active">All habits</button>
                    <span class="search">&#128269;</span>
                    <button class="nav-btn">filter</button>
                    <button class="nav-btn" id="addHabitBtn">add habit</button>
                </div>
                <div class="top-nav-right">
                    <span class="habit-focus-name" id="habitFocusName">Reading</span>
                    <button class="icon-btn" title="Minimize/Maximize">&#9723;</button>
                    <button class="icon-btn" title="Calendar">&#128197;</button>
                    <span class="calendar-date" id="calendarDate"></span>
                    <button class="icon-btn" title="Exit">&#10005;</button>
                </div>
            </div>
            
            <div class="main">
                <div class="habit-list" id="habitList">
                    </div>
                
                <div class="detail-panel" id="detailPanel">
                    <div class="detail-title" id="detailTitle">Reading</div>
                    <div class="detail-streak">current streak</div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'deli_strani/dodaj_novo_navado.php'; ?>
    
    <script>
        let habits = [
            { name: 'Reading', streak: 5 },
        ];
        let selectedHabit = 0;

        function renderHabits() {
            const habitList = document.getElementById('habitList');
            habitList.innerHTML = '';
            habits.forEach((habit, idx) => {
                const item = document.createElement('div');
                item.className = 'habit-item';
                item.innerHTML = `
                    <div class="habit-circle"></div>
                    <div class="habit-name">${habit.name}</div>
                    <div class="habit-log">log</div>
                    <div class="habit-menu">&#8942;</div>
                `;
                item.onclick = () => selectHabit(idx);
                habitList.appendChild(item);
            });
        }

        function selectHabit(idx) {
            selectedHabit = idx;
            document.getElementById('detailTitle').textContent = habits[idx].name;
            document.getElementById('habitFocusName').textContent = habits[idx].name;
            document.getElementById('detailPanel').innerHTML = `
                <div class="detail-title">${habits[idx].name}</div>
                <div class="detail-streak">current streak: ${habits[idx].streak || 0}</div>
            `;
        }

        function setCalendarDate() {
            const now = new Date();
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            document.getElementById('calendarDate').textContent = now.toLocaleDateString('sl-SI', options);
        }
        setCalendarDate();

        function openAddHabitForm() {
            const form = document.getElementById('addHabitForm');
            const overlay = document.getElementById('overlay');
            if(form && overlay) {
                form.classList.add('active');
                overlay.classList.add('active');
                const today = new Date().toISOString().split('T')[0];
                const dateInput = document.getElementById('startDate');
                if(dateInput) dateInput.value = today;
            }
        }

        function closeAddHabitForm() {
            const form = document.getElementById('addHabitForm');
            const overlay = document.getElementById('overlay');
            const habitForm = document.getElementById('habitForm');
            
            if(form && overlay) {
                form.classList.remove('active');
                overlay.classList.remove('active');
            }
            if(habitForm) habitForm.reset();
        }

        const addHabitBtn = document.getElementById('addHabitBtn');
        if(addHabitBtn) addHabitBtn.onclick = openAddHabitForm;
        
        const overlay = document.getElementById('overlay');
        if(overlay) overlay.onclick = closeAddHabitForm;
        
        const cancelBtn = document.getElementById('cancelBtn');
        if(cancelBtn) cancelBtn.onclick = closeAddHabitForm;
        
        const habitForm = document.getElementById('habitForm');
        if(habitForm) {
            habitForm.onsubmit = function (e) {
                e.preventDefault();
                const nameInput = document.getElementById('habitName');
                if(nameInput) {
                    const name = nameInput.value;
                    if (name.trim()) {
                        habits.push({ name, streak: 0 });
                        renderHabits();
                        selectHabit(habits.length - 1);
                        closeAddHabitForm();
                    }
                }
            };
        }

        renderHabits();
        selectHabit(0);
    </script>
</body>
<?php endif; ?>
</html>
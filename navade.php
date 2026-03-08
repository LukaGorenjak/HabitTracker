<!DOCTYPE html>
<html lang="sl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navade</title>
    <link href="https://fonts.googleapis.com/css2?family=Fjalla+One&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            font-family: 'Fjalla One', Arial, sans-serif;
            background: #f5f3e7;
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
            background: #f5f3e7;
            height: 100vh;
        }

        .top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #6b7c5c;
            padding: 0 24px;
            height: 8%;
            border-left: 2px solid #222;
            border-right: 2px solid #222;
        }
        .top-nav-left {
            display: flex;
            align-items: center;
            height: 100%;
        }
        .top-nav-right {
            display: flex;
            align-items: center;
            height: 100%;
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
            color: #222;
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
        }

        .nav-heading {
            margin-right: 960px;
        }

        .nav-btn {
            background: none;
            border: none;
            color: #222;
            font-size: 18px;
            font-family: 'Fjalla One', Arial, sans-serif;
            margin-right: 32px;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .nav-btn.active {
            border: 2px solid #7c8c6b;
            color: #222;
        }

        .nav-btn:hover {
            background: #7c8c6b;
            color: #fff;
        }

        .search {
            margin-right: 32px;
            font-size: 22px;
        }

        .main {
            display: flex;
            flex: 1;
            min-height: 0;
            height: calc(100vh - 56px);
        }

        .habit-list {
            flex: 2;
            background: #f5f3e7;
            padding: 32px 24px;
            overflow-y: auto;
            border: 2px solid #222;
        }

        .habit-item {
            display: flex;
            align-items: center;
            background: #7c8c6b;
            color: #222;
            border-radius: 8px;
            margin-bottom: 16px;
            padding: 12px 16px;
            font-size: 18px;
        }

        .habit-circle {
            width: 32px;
            height: 32px;
            background: #5c6b5c;
            border-radius: 50%;
            margin-right: 16px;
        }

        .habit-name {
            flex: 1;
        }

        .habit-log {
            margin-right: 16px;
            color: #222;
            cursor: pointer;
        }

        .habit-menu {
            font-size: 22px;
            cursor: pointer;
        }

        .detail-panel {
            flex: 1;
            background: #7c8c6b;
            padding: 32px 24px;
            color: #222;
            border-top: 2px solid #222;
        }

        .detail-title {
            font-size: 24px;
            font-family: 'Playfair Display', serif;
            margin-bottom: 24px;
        }

        .detail-streak {
            background: #f5f3e7;
            color: #222;
            border-radius: 8px;
            padding: 18px 0;
            font-size: 22px;
            display: block;
            width: 100%;
            text-align: center;
            margin-bottom: 16px;
            margin-top: 8px;
            font-weight: bold;
            border: none;
        }

        .add-habit-form {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            padding: 32px;
            z-index: 10;
        }

        .add-habit-form.active {
            display: block;
        }

        .add-habit-form input {
            font-size: 18px;
            padding: 8px 12px;
            margin-bottom: 16px;
            width: 100%;
            border-radius: 6px;
            border: 1px solid #7c8c6b;
        }

        .add-habit-form button {
            font-size: 18px;
            padding: 8px 24px;
            border-radius: 6px;
            border: none;
            background: #7c8c6b;
            color: #fff;
            cursor: pointer;
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

<body>
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
                    <!-- Habits will be rendered here -->
                </div>
                <div class="detail-panel" id="detailPanel">
                    <div class="detail-title" id="detailTitle">Reading</div>
                    <div class="detail-streak">current streak</div>
                </div>
            </div>
        </div>
    </div>
    <div class="overlay" id="overlay"></div>
    <div class="add-habit-form" id="addHabitForm">
        <form id="habitForm">
            <label for="habitName">Habit name:</label>
            <input type="text" id="habitName" name="habitName" required>
            <button type="submit">Add</button>
        </form>
    </div>
    <script>
        // Demo habit data
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
        // Set calendar date
        function setCalendarDate() {
            const now = new Date();
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            document.getElementById('calendarDate').textContent = now.toLocaleDateString('sl-SI', options);
        }
        setCalendarDate();

        // Add habit modal
        document.getElementById('addHabitBtn').onclick = function () {
            document.getElementById('addHabitForm').classList.add('active');
            document.getElementById('overlay').classList.add('active');
        };
        document.getElementById('overlay').onclick = function () {
            document.getElementById('addHabitForm').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
        };
        document.getElementById('habitForm').onsubmit = function (e) {
            e.preventDefault();
            const name = document.getElementById('habitName').value;
            habits.push({ name, streak: 0 });
            renderHabits();
            selectHabit(habits.length - 1);
            document.getElementById('addHabitForm').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
            document.getElementById('habitName').value = '';
        };

        // Initial render
        renderHabits();
        selectHabit(0);
    </script>
</body>

</html>
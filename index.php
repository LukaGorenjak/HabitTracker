<?php
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$navade       = [];
$logged_today = [];

if ($isLoggedIn) {
    require_once 'konfiguracija/db.php';

    $stmt = $pdo->prepare("
        SELECT n.*, k.ime AS kategorija_ime, k.barva AS kategorija_barva
        FROM navade n
        LEFT JOIN kategorije k ON n.id_kategorije = k.id_kategorije
        WHERE n.id_uporabnika = ?
        ORDER BY n.ustvarjeno DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $navade = $stmt->fetchAll();

    // Which habits has the user already logged today?
    $today = date('Y-m-d');
    $stmt  = $pdo->prepare("
        SELECT d.id_navade
        FROM dnevniki d
        JOIN navade n ON d.id_navade = n.id_navade
        WHERE n.id_uporabnika = ? AND d.datum = ? AND d.opravljeno = 1
    ");
    $stmt->execute([$_SESSION['user_id'], $today]);
    $logged_today = array_map('intval', array_column($stmt->fetchAll(), 'id_navade'));

    // Current user data for settings modal
    $stmt = $pdo->prepare("SELECT uporabnisko_ime, email, profilna_slika FROM uporabniki WHERE id_uporabnika = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Flow</title>
    <link rel="stylesheet" href="ostalo/style.css">
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
                    <button class="nav-btn active">Vse navade</button>
                    <span class="search">&#128269;</span>
                    <button class="nav-btn">Filter</button>
                    <button class="nav-btn" id="addHabitBtn">+ Dodaj navado</button>
                </div>
                <div class="top-nav-right">
                    <span class="habit-focus-name" id="habitFocusName">Habit Flow</span>
                    <button class="icon-btn" title="Koledar">&#128197;</button>
                    <span class="calendar-date" id="calendarDate"></span>
                </div>
            </div>

            <div class="main">
                <div class="habit-list" id="habitList"></div>

                <?php include 'deli_strani/podrobnosti_navade.php'; ?>
            </div>
        </div>
    </div>

    <?php include 'deli_strani/dodaj_novo_navado.php'; ?>
    <?php include 'deli_strani/nastavitve.php'; ?>

    <script>
        // ---------------------------------------------------
        // DATA FROM PHP
        // ---------------------------------------------------
        const MONTH_NAMES_SL = ['Januar','Februar','Marec','April','Maj','Junij',
                                 'Julij','Avgust','September','Oktober','November','December'];
        const habits = <?php echo json_encode($navade, JSON_HEX_TAG | JSON_HEX_QUOT); ?>;
        const loggedToday = <?php echo json_encode($logged_today); ?>;
        const currentUser = <?php echo json_encode([
            'ime'           => $currentUser['uporabnisko_ime'] ?? '',
            'email'         => $currentUser['email']          ?? '',
            'profilna_slika'=> $currentUser['profilna_slika'] ?? null,
        ]); ?>;
        let selectedHabitId = null;

        // ---------------------------------------------------
        // HELPERS
        // ---------------------------------------------------
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str ?? ''));
            return div.innerHTML;
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('sl-SI', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        // ---------------------------------------------------
        // RENDER HABIT LIST
        // ---------------------------------------------------
        function renderHabits() {
            const habitList = document.getElementById('habitList');
            habitList.innerHTML = '';

            if (habits.length === 0) {
                habitList.innerHTML = '<div class="habit-list-empty">Še nimate dodanih navad.<br>Kliknite "+ Dodaj navado" za začetek.</div>';
                return;
            }

            habits.forEach((habit) => {
                const item = document.createElement('div');
                item.className = 'habit-item';
                item.dataset.id = habit.id_navade;
                const dotColor = habit.kategorija_barva || '#4a9d6f';
                const isLogged = loggedToday.includes(Number(habit.id_navade));

                item.innerHTML = `
                    <div class="habit-dot" style="background: ${dotColor};"></div>
                    <div class="habit-name">${escapeHtml(habit.ime_navade)}</div>
                    <div class="habit-streak-badge">${habit.streak || 0} 🔥</div>
                    <button class="habit-log-btn ${isLogged ? 'logged' : ''}" title="${isLogged ? 'Že zabeleženo' : 'Zabeleži za danes'}">
                        ${isLogged ? '✓' : '○'}
                    </button>
                `;

                // Log button: toggle without selecting the habit
                item.querySelector('.habit-log-btn').addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleLog(habit.id_navade);
                });

                item.addEventListener('click', () => selectHabit(habit.id_navade));
                habitList.appendChild(item);
            });
        }

        // ---------------------------------------------------
        // TOGGLE TODAY'S LOG
        // ---------------------------------------------------
        function toggleLog(id) {
            fetch('logika/zabeleznaj_navado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_navade=${encodeURIComponent(id)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const habitId = Number(id);
                    const idx = loggedToday.indexOf(habitId);
                    if (data.logged) {
                        if (idx === -1) loggedToday.push(habitId);
                    } else {
                        if (idx !== -1) loggedToday.splice(idx, 1);
                    }
                    // Update streak in local habits array
                    const habit = habits.find(h => Number(h.id_navade) === habitId);
                    if (habit) habit.streak = data.streak;
                    // Re-render list
                    renderHabits();
                    // Keep selection and update streak in detail panel
                    if (selectedHabitId && Number(selectedHabitId) === habitId) {
                        document.getElementById('detailStreak').textContent = data.streak;
                        selectHabit(id);
                    }
                }
            })
            .catch(() => {});
        }

        // ---------------------------------------------------
        // SELECT & SHOW HABIT DETAILS
        // ---------------------------------------------------
        function selectHabit(id) {
            selectedHabitId = id;
            const habit = habits.find(h => h.id_navade == id);
            if (!habit) return;

            document.getElementById('habitFocusName').textContent = habit.ime_navade;

            document.getElementById('detailEmpty').style.display = 'none';
            document.getElementById('detailContent').style.display = 'block';

            document.getElementById('detailTitle').textContent = habit.ime_navade;
            document.getElementById('detailCategoryDot').style.background = habit.kategorija_barva || '#4a9d6f';
            document.getElementById('detailStreak').textContent = habit.streak || 0;
            document.getElementById('detailKategorija').textContent = habit.kategorija_ime || '-';

            const ponavljanjeMap = { dnevno: 'Dnevno', tedensko: 'Tedensko', mesecno: 'Mesečno' };
            document.getElementById('detailPonavljanje').textContent = ponavljanjeMap[habit.ponavljanje] || habit.ponavljanje;

            const dneviRow = document.getElementById('detailDneviRow');
            if (habit.ponavljanje === 'tedensko' && habit.izbrani_dnevi && habit.izbrani_dnevi !== 'vsak_dan') {
                dneviRow.style.display = 'flex';
                const dayMap = { ponedeljek: 'Ponedeljek', torek: 'Torek', sreda: 'Sreda', cetrtek: 'Četrtek', petek: 'Petek', sobota: 'Sobota', nedelja: 'Nedelja' };
                const days = habit.izbrani_dnevi.split(',').map(d => dayMap[d.trim()] || d).join(', ');
                document.getElementById('detailDnevi').textContent = days;
            } else {
                dneviRow.style.display = 'none';
            }

            const obdobjeMap = { na_dan: 'na dan', na_teden: 'na teden', na_mesec: 'na mesec' };
            document.getElementById('detailCilj').textContent =
                `${habit.cilj_kolicina} ${habit.cilj_enota} ${obdobjeMap[habit.cilj_obdobje] || habit.cilj_obdobje}`;

            document.getElementById('detailZacetek').textContent = formatDate(habit.zacetni_datum);

            const konecRow = document.getElementById('detailKonecRow');
            if (habit.konec_tip === 'nikoli' || !habit.konec_datum) {
                konecRow.style.display = 'none';
            } else {
                konecRow.style.display = 'flex';
                document.getElementById('detailKonec').textContent = formatDate(habit.konec_datum);
            }

            const delDnevaRow = document.getElementById('detailDelDnevaRow');
            if (habit.del_dneva && habit.del_dneva.trim()) {
                delDnevaRow.style.display = 'flex';
                const delMap = { zjutraj: 'Zjutraj', popoldne: 'Popoldne', zvecer: 'Zvečer' };
                document.getElementById('detailDelDneva').textContent =
                    habit.del_dneva.split(',').map(d => delMap[d.trim()] || d).join(', ');
            } else {
                delDnevaRow.style.display = 'none';
            }

            const opisRow = document.getElementById('detailOpisRow');
            if (habit.opis && habit.opis.trim()) {
                opisRow.style.display = 'flex';
                document.getElementById('detailOpis').textContent = habit.opis;
            } else {
                opisRow.style.display = 'none';
            }

            // Highlight selected item in list
            document.querySelectorAll('.habit-item').forEach(el => el.classList.remove('selected'));
            const selectedEl = document.querySelector(`.habit-item[data-id="${id}"]`);
            if (selectedEl) selectedEl.classList.add('selected');

            // Wire up action buttons
            document.getElementById('editHabitBtn').onclick = () => openEditHabitForm(habit);
            document.getElementById('deleteHabitBtn').onclick = () => deleteHabit(habit.id_navade, habit.ime_navade);

            // Load monthly chart
            loadHabitChart(habit.id_navade);
        }

        // ---------------------------------------------------
        // DELETE HABIT
        // ---------------------------------------------------
        function deleteHabit(id, name) {
            if (!confirm(`Ste prepričani, da želite izbrisati navado "${name}"?`)) return;

            fetch('logika/izbrisi_navado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_navade=${encodeURIComponent(id)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const idx = habits.findIndex(h => h.id_navade == id);
                    if (idx !== -1) habits.splice(idx, 1);
                    renderHabits();
                    document.getElementById('detailEmpty').style.display = 'block';
                    document.getElementById('detailContent').style.display = 'none';
                    document.getElementById('habitFocusName').textContent = 'Habit Flow';
                    selectedHabitId = null;
                } else {
                    alert('Napaka pri brisanju navade.');
                }
            })
            .catch(() => alert('Napaka pri brisanju navade.'));
        }

        // ---------------------------------------------------
        // OPEN EDIT FORM (pre-fill modal with existing data)
        // ---------------------------------------------------
        function openEditHabitForm(habit) {
            document.getElementById('habitName').value = habit.ime_navade;
            document.getElementById('habitDescription').value = habit.opis || '';
            document.getElementById('frequencySelect').value = habit.ponavljanje;
            document.getElementById('startDate').value = habit.zacetni_datum || '';
            document.getElementById('editHabitId').value = habit.id_navade;
            document.getElementById('habitForm').action = 'logika/uredi_navado.php';
            document.getElementById('formTitle').textContent = 'Uredi navado';
            document.querySelector('#habitForm .btn-save').textContent = 'Shrani spremembe';

            // Days dropdown
            if (habit.ponavljanje === 'tedensko') {
                document.getElementById('daysDropdown').style.display = 'block';
                document.querySelectorAll('#daysContent input[type="checkbox"]').forEach(cb => cb.checked = false);
                if (habit.izbrani_dnevi && habit.izbrani_dnevi !== 'vsak_dan') {
                    const dayIdMap = { ponedeljek: 'monday', torek: 'tuesday', sreda: 'wednesday', cetrtek: 'thursday', petek: 'friday', sobota: 'saturday', nedelja: 'sunday' };
                    habit.izbrani_dnevi.split(',').forEach(day => {
                        const cbId = dayIdMap[day.trim()];
                        if (cbId) document.getElementById(cbId).checked = true;
                    });
                }
                updateDaysButtonText();
            } else {
                document.getElementById('daysDropdown').style.display = 'none';
            }

            // Category
            const katSelect = document.querySelector('select[name="kategorija"]');
            if (katSelect && habit.kategorija_ime) katSelect.value = habit.kategorija_ime.toLowerCase();

            // Goal
            const kolicina = document.querySelector('input[name="cilj_kolicina"]');
            const enota    = document.querySelector('select[name="cilj_enota"]');
            const obdobje  = document.querySelector('select[name="cilj_obdobje"]');
            if (kolicina) kolicina.value = habit.cilj_kolicina;
            if (enota)    enota.value    = habit.cilj_enota;
            if (obdobje)  obdobje.value  = habit.cilj_obdobje;

            // End condition
            const endSelect = document.getElementById('endConditionSelect');
            if (endSelect) {
                endSelect.value = habit.konec_tip || 'nikoli';
                endSelect.dispatchEvent(new Event('change'));
                if (habit.konec_tip === 'datum' && habit.konec_datum) {
                    document.getElementById('endDateInput').value = habit.konec_datum;
                }
            }

            openAddHabitForm();
        }

        // ---------------------------------------------------
        // ADD / CLOSE MODAL
        // ---------------------------------------------------
        function openAddHabitForm() {
            document.getElementById('addHabitForm').classList.add('active');
            document.getElementById('overlay').classList.add('active');
        }

        function closeAddHabitForm() {
            document.getElementById('addHabitForm').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
            const habitForm = document.getElementById('habitForm');
            if (habitForm) {
                habitForm.reset();
                habitForm.action = 'logika/shrani_navado.php';
                document.getElementById('editHabitId').value = '';
                document.getElementById('formTitle').textContent = 'Nova navada';
                document.querySelector('#habitForm .btn-save').textContent = 'Shrani';
                document.getElementById('daysDropdown').style.display = 'none';
            }
        }

        // ---------------------------------------------------
        // SETTINGS MODAL
        // ---------------------------------------------------
        const DEFAULT_AVATAR = 'ostalo/slike/simple-white-circle-and-drop-shadow-png.png';

        function openNastavitve() {
            document.getElementById('nastavitveIme').value   = currentUser.ime;
            document.getElementById('nastavitveEmail').value = currentUser.email;
            document.getElementById('trenutnoGeslo').value   = '';
            document.getElementById('novoGeslo').value       = '';
            document.getElementById('potrdiGeslo').value     = '';
            document.getElementById('nastavitveError').style.display   = 'none';
            document.getElementById('nastavitveSuccess').style.display = 'none';
            document.getElementById('profilPreview').src =
                currentUser.profilna_slika ? currentUser.profilna_slika : DEFAULT_AVATAR;
            document.getElementById('nastavitveModal').classList.add('active');
            document.getElementById('overlay').classList.add('active');
        }

        function closeNastavitve() {
            document.getElementById('nastavitveModal').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
        }

        // Profile picture preview
        document.getElementById('profilnaSlika').addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => document.getElementById('profilPreview').src = e.target.result;
                reader.readAsDataURL(file);
            }
        });

        // Settings form submit (AJAX with FormData to support file upload)
        document.getElementById('nastavitveFormEl').addEventListener('submit', function (e) {
            e.preventDefault();
            const errorEl   = document.getElementById('nastavitveError');
            const successEl = document.getElementById('nastavitveSuccess');
            errorEl.style.display = successEl.style.display = 'none';

            const formData = new FormData(this);

            fetch('logika/shrani_nastavitve.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        errorEl.textContent    = data.error;
                        errorEl.style.display  = 'block';
                        return;
                    }
                    // Update in-memory user data
                    currentUser.ime = data.ime;
                    if (data.profilna_slika) currentUser.profilna_slika = data.profilna_slika;

                    // Update sidebar username and avatar
                    const sidebarName = document.querySelector('.profile h2');
                    if (sidebarName) sidebarName.textContent = data.ime;
                    if (data.profilna_slika) {
                        const sidebarImg = document.querySelector('.profile img');
                        if (sidebarImg) sidebarImg.src = data.profilna_slika;
                    }

                    successEl.textContent   = 'Nastavitve so bile shranjene!';
                    successEl.style.display = 'block';
                    setTimeout(closeNastavitve, 1200);
                })
                .catch(() => {
                    errorEl.textContent   = 'Napaka pri shranjevanju.';
                    errorEl.style.display = 'block';
                });
        });

        document.getElementById('cancelNastavitveBtn').addEventListener('click', closeNastavitve);
        document.getElementById('cancelNastavitveBtn2').addEventListener('click', closeNastavitve);

        // ---------------------------------------------------
        // MONTHLY CALENDAR
        // ---------------------------------------------------
        let calYear     = new Date().getFullYear();
        let calMonth    = new Date().getMonth() + 1; // 1–12
        let calHabitId  = null;

        function loadHabitChart(habitId) {
            calHabitId = habitId;
            renderHabitChart();
        }

        function renderHabitChart() {
            if (!calHabitId) return;

            const todayDate   = new Date();
            const isThisMonth = calYear === todayDate.getFullYear() && calMonth === todayDate.getMonth() + 1;
            const todayDay    = todayDate.getDate();

            // Category color for this habit
            const habit = habits.find(h => Number(h.id_navade) === Number(calHabitId));
            const catColor = (habit && habit.kategorija_barva) ? habit.kategorija_barva : '#4a9d6f';

            document.getElementById('chartMonthLabel').textContent =
                `${MONTH_NAMES_SL[calMonth - 1]} ${calYear}`;

            fetch(`logika/mesecni_dnevnik.php?id_navade=${encodeURIComponent(calHabitId)}&leto=${calYear}&mesec=${calMonth}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    const loggedDays = data.opravljeni.map(d => parseInt(d.split('-')[2]));
                    const grid = document.getElementById('habitCalendarGrid');
                    grid.innerHTML = '';

                    // Monday-first: getDay() returns 0=Sun; shift so Mon=0 … Sun=6
                    const firstDayRaw = new Date(calYear, calMonth - 1, 1).getDay();
                    const firstDay    = (firstDayRaw + 6) % 7;
                    const daysInMonth = new Date(calYear, calMonth, 0).getDate();
                    const daysInPrev  = new Date(calYear, calMonth - 1, 0).getDate();

                    // Leading days from previous month
                    for (let i = 0; i < firstDay; i++) {
                        const cell = document.createElement('div');
                        cell.className = 'cal-day cal-day-other';
                        cell.textContent = daysInPrev - firstDay + 1 + i;
                        grid.appendChild(cell);
                    }

                    // Current month days
                    for (let d = 1; d <= daysInMonth; d++) {
                        const cell    = document.createElement('div');
                        const done    = loggedDays.includes(d);
                        const isToday = isThisMonth && d === todayDay;
                        const future  = isThisMonth && d > todayDay;

                        cell.className = 'cal-day' +
                            (isToday ? ' cal-day-today' : '') +
                            (future  ? ' cal-day-future' : '') +
                            (done    ? ' cal-day-done'   : '');
                        cell.textContent = d;

                        if (done) cell.style.background = catColor;

                        if (!future) {
                            cell.addEventListener('click', () => {
                                const datum = `${calYear}-${String(calMonth).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                                toggleChartDay(datum);
                            });
                        }
                        grid.appendChild(cell);
                    }

                    // Trailing days to fill the last row
                    const total     = firstDay + daysInMonth;
                    const remaining = total % 7 === 0 ? 0 : 7 - (total % 7);
                    for (let i = 1; i <= remaining; i++) {
                        const cell = document.createElement('div');
                        cell.className = 'cal-day cal-day-other';
                        cell.textContent = i;
                        grid.appendChild(cell);
                    }
                })
                .catch(() => {});
        }

        function toggleChartDay(datum) {
            fetch('logika/zabeleznaj_navado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_navade=${encodeURIComponent(calHabitId)}&datum=${encodeURIComponent(datum)}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                // Sync loggedToday if the toggled date is today
                const todayStr = new Date().toISOString().split('T')[0];
                if (datum === todayStr) {
                    const hid = Number(calHabitId);
                    const idx = loggedToday.indexOf(hid);
                    if (data.logged) { if (idx === -1) loggedToday.push(hid); }
                    else             { if (idx !== -1) loggedToday.splice(idx, 1); }
                }

                // Sync streak
                const habit = habits.find(h => Number(h.id_navade) === Number(calHabitId));
                if (habit) habit.streak = data.streak;
                document.getElementById('detailStreak').textContent = data.streak;
                renderHabits();
                renderHabitChart();
            })
            .catch(() => {});
        }

        // Month navigation
        document.getElementById('chartPrevMonth').addEventListener('click', () => {
            calMonth--;
            if (calMonth < 1) { calMonth = 12; calYear--; }
            renderHabitChart();
        });
        document.getElementById('chartNextMonth').addEventListener('click', () => {
            const now = new Date();
            if (calYear === now.getFullYear() && calMonth === now.getMonth() + 1) return;
            calMonth++;
            if (calMonth > 12) { calMonth = 1; calYear++; }
            renderHabitChart();
        });

        // ---------------------------------------------------
        // CALENDAR DATE
        // ---------------------------------------------------
        function setCalendarDate() {
            const now = new Date();
            document.getElementById('calendarDate').textContent =
                now.toLocaleDateString('sl-SI', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        setCalendarDate();

        // ---------------------------------------------------
        // EVENT LISTENERS
        // ---------------------------------------------------
        document.getElementById('addHabitBtn').addEventListener('click', () => {
            document.getElementById('formTitle').textContent = 'Nova navada';
            document.querySelector('#habitForm .btn-save').textContent = 'Shrani';
            document.getElementById('editHabitId').value = '';
            document.getElementById('habitForm').action = 'logika/shrani_navado.php';
            openAddHabitForm();
        });

        document.getElementById('overlay').addEventListener('click', () => {
            closeAddHabitForm();
            closeNastavitve();
        });
        document.getElementById('cancelBtn').addEventListener('click', closeAddHabitForm);

        // ---------------------------------------------------
        // INIT
        // ---------------------------------------------------
        renderHabits();
        if (habits.length > 0) selectHabit(habits[0].id_navade);
    </script>
</body>
<?php endif; ?>
</html>

<?php
require_once 'konfiguracija/seja.php';

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

    $today = date('Y-m-d');
    $stmt  = $pdo->prepare("
        SELECT d.id_navade
        FROM dnevniki d
        JOIN navade n ON d.id_navade = n.id_navade
        WHERE n.id_uporabnika = ? AND d.datum = ? AND d.opravljeno = 1
    ");
    $stmt->execute([$_SESSION['user_id'], $today]);
    $logged_today = array_map('intval', array_column($stmt->fetchAll(), 'id_navade'));

    $stmt = $pdo->prepare("SELECT uporabnisko_ime, email, profilna_slika FROM uporabniki WHERE id_uporabnika = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $currentUser = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT id_kategorije, ime, barva FROM kategorije WHERE id_uporabnika = ? ORDER BY ime ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $kategorijeList = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM dnevniki d JOIN navade n ON d.id_navade = n.id_navade WHERE n.id_uporabnika = ? AND d.opravljeno = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $statOpravljenih = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(MAX(streak), 0) FROM navade WHERE id_uporabnika = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $statMaxStreak = (int)$stmt->fetchColumn();

    $statNavad = count($navade);
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Habit Flow</title>
    <link rel="stylesheet" href="ostalo/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <?php if ($isLoggedIn): ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <?php endif; ?>
</head>
<?php if (!$isLoggedIn): ?>
<body class="landing-body">
    <nav class="landing-nav">
        <div class="landing-nav-logo">Habit Flow</div>
        <div class="landing-nav-links">
            <a href="avtentikacija/prijava.php" class="landing-nav-link">Prijava</a>
            <a href="avtentikacija/registracija.php" class="landing-nav-link landing-nav-link-cta">Registracija</a>
        </div>
    </nav>
    <div class="landing-main-content">
        <div class="landing-subtitle">Tvoj osebni tracker navad</div>
        <div class="landing-title">Habit Flow</div>
        <div class="landing-desc">Gradi boljše navade, sledi napredku in dosegaj svoje cilje — vsak dan znova.</div>
        <div class="landing-cta">
            <a href="avtentikacija/registracija.php" class="landing-btn-primary">Začni brezplačno</a>
            <a href="avtentikacija/prijava.php" class="landing-btn-secondary">Prijava</a>
        </div>
    </div>
</body>

<?php else: ?>
<body class="dashboard-body">
    <div class="layout" id="layout">
        <?php include 'deli_strani/navigacija.php'; ?>

        <div class="main-content">
            <div class="top-nav">
                <div class="top-nav-left">
                    <button class="hamburger-btn" id="hamburgerBtn">&#9776;</button>
                    <span class="nav-active-label">Vse navade</span>
                    <div class="nav-search-box">
                        <input type="text" class="nav-search-input" id="searchInput" placeholder="Išči navade...">
                    </div>
                    <div class="filter-select-wrap">
                        <select class="nav-filter-select" id="filterKategorija">
                            <option value="">Vse kategorije</option>
                        </select>
                    </div>
                    <button class="nav-btn" id="addHabitBtn">+ Dodaj navado</button>
                </div>
                <div class="top-nav-right">
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
    <?php include 'deli_strani/statistika.php'; ?>

    <script>
        // ===================================================
        // PODATKI IZ PHP
        // ===================================================
        const MONTH_NAMES_SL = ['Januar','Februar','Marec','April','Maj','Junij',
                                 'Julij','Avgust','September','Oktober','November','December'];

        const habits = <?php echo json_encode($navade, JSON_HEX_TAG | JSON_HEX_QUOT); ?>;
        const loggedToday = <?php echo json_encode($logged_today); ?>;
        const kategorijeData = <?php echo json_encode($kategorijeList ?? []); ?>;
        const currentUser = <?php echo json_encode([
            'ime'           => $currentUser['uporabnisko_ime'] ?? '',
            'email'         => $currentUser['email']          ?? '',
            'profilna_slika'=> $currentUser['profilna_slika'] ?? null,
        ]); ?>;

        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        let selectedHabitId = null;
        let searchQuery      = '';
        let filterKategorija = '';
        let filterDelDneva   = '';

        // ===================================================
        // POMOŽNE FUNKCIJE
        // ===================================================
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

        // ===================================================
        // IZRIS SEZNAMA NAVAD
        // ===================================================
        function renderHabits() {
            const habitList = document.getElementById('habitList');
            habitList.innerHTML = '';

            // Filtriraj navade glede na iskalni niz, kategorijo in del dneva
            const q = searchQuery.toLowerCase();
            const filtered = habits.filter(h => {
                const imeMatch = h.ime_navade.toLowerCase().includes(q);
                const katMatch = !filterKategorija || (h.kategorija_ime || '').toLowerCase() === filterKategorija.toLowerCase();
                const delParts = (h.del_dneva || '').split(',').map(d => d.trim());
                const delMatch = !filterDelDneva || delParts.includes(filterDelDneva);
                return imeMatch && katMatch && delMatch;
            });

            if (habits.length === 0) {
                habitList.innerHTML = '<div class="habit-list-empty">Še nimate dodanih navad.<br>Kliknite "+ Dodaj navado" za začetek.</div>';
                return;
            }
            if (filtered.length === 0) {
                habitList.innerHTML = '<div class="habit-list-empty">Nobena navada ne ustreza iskanju.</div>';
                return;
            }

            filtered.forEach((habit) => {
                const item = document.createElement('div');
                item.className = 'habit-item';
                item.dataset.id = habit.id_navade;

                const dotColor = habit.kategorija_barva || '#4a9d6f';
                const isLogged = loggedToday.includes(Number(habit.id_navade));

                const goal = parseInt(habit.cilj_dni) || 0;
                const streak = parseInt(habit.streak) || 0;
                const progressBar = goal > 0
                    ? `<div class="habit-progress-bar"><div class="habit-progress-fill" style="width:${Math.min((streak/goal)*100,100).toFixed(1)}%; background:${dotColor};"></div></div>`
                    : '';

                const iconEl = habit.emoji
                    ? `<span class="habit-emoji" style="font-size:20px; margin-right:4px; flex-shrink:0;">${escapeHtml(habit.emoji)}</span>`
                    : `<div class="habit-dot" style="background: ${dotColor};"></div>`;

                item.innerHTML = `
                    ${iconEl}
                    <div class="habit-name">${escapeHtml(habit.ime_navade)}</div>
                    <div class="habit-streak-badge">${habit.streak || 0} 🔥</div>
                    <button class="habit-log-btn ${isLogged ? 'logged' : ''}" title="${isLogged ? 'Že zabeleženo' : 'Zabeleži za danes'}">
                        ${isLogged ? '✓' : '○'}
                    </button>
                    ${progressBar}
                `;

                item.querySelector('.habit-log-btn').addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleLog(habit.id_navade);
                });

                item.addEventListener('click', () => selectHabit(habit.id_navade));
                habitList.appendChild(item);
            });
        }

        // ===================================================
        // BELEŽENJE NAVADE ZA DANES (toggle)
        // ===================================================
        function toggleLog(id) {
            fetch('logika/zabeleznaj_navado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_navade=${encodeURIComponent(id)}&csrf_token=${encodeURIComponent(CSRF)}`
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

                    const habit = habits.find(h => Number(h.id_navade) === habitId);
                    if (habit) habit.streak = data.streak;

                    renderHabits();

                    if (selectedHabitId && Number(selectedHabitId) === habitId) {
                        document.getElementById('detailStreak').textContent = data.streak;
                        selectHabit(id);
                    }
                }
            })
            .catch(() => {});
        }

        // ===================================================
        // PRIKAZ PODROBNOSTI NAVADE (desni panel)
        // ===================================================
        function selectHabit(id) {
            selectedHabitId = id;

            const habit = habits.find(h => h.id_navade == id);
            if (!habit) return;

            document.getElementById('detailEmpty').style.display = 'none';
            document.getElementById('detailContent').style.display = 'block';

            document.getElementById('detailTitle').textContent = habit.ime_navade;
            document.getElementById('detailCategoryDot').style.background = habit.kategorija_barva || '#4a9d6f';
            document.getElementById('detailStreak').textContent = habit.streak || 0;

            const progressSection = document.getElementById('detailProgressSection');
            const goalDni = parseInt(habit.cilj_dni) || 0;
            if (goalDni > 0) {
                progressSection.style.display = 'flex';
                const streakVal = parseInt(habit.streak) || 0;
                const done = Math.min(streakVal, goalDni);
                const remaining = Math.max(goalDni - done, 0);
                const pct = Math.round((done / goalDni) * 100);
                document.getElementById('progressChartLabel').textContent = `${done}/${goalDni}`;
                document.getElementById('progressGoalText').textContent = `${pct}% do cilja`;
                if (window.progressChartInstance) window.progressChartInstance.destroy();
                const ctx = document.getElementById('progressChart').getContext('2d');
                const color = habit.kategorija_barva || '#4a9d6f';
                window.progressChartInstance = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        datasets: [{ data: [done || 0.01, remaining || 0.01], backgroundColor: [color, '#2b3a2f'], borderWidth: 0 }]
                    },
                    options: {
                        cutout: '72%',
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        animation: { animateRotate: true, duration: 600 }
                    }
                });
            } else {
                progressSection.style.display = 'none';
                if (window.progressChartInstance) { window.progressChartInstance.destroy(); window.progressChartInstance = null; }
            }

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

            document.querySelectorAll('.habit-item').forEach(el => el.classList.remove('selected'));
            const selectedEl = document.querySelector(`.habit-item[data-id="${id}"]`);
            if (selectedEl) selectedEl.classList.add('selected');

            document.getElementById('editHabitBtn').onclick = () => openEditHabitForm(habit);
            document.getElementById('deleteHabitBtn').onclick = () => deleteHabit(habit.id_navade, habit.ime_navade);

            loadHabitChart(habit.id_navade);
        }

        // ===================================================
        // BRISANJE NAVADE
        // ===================================================
        function deleteHabit(id, name) {
            if (!confirm(`Ste prepričani, da želite izbrisati navado "${name}"?`)) return;

            fetch('logika/izbrisi_navado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_navade=${encodeURIComponent(id)}&csrf_token=${encodeURIComponent(CSRF)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const idx = habits.findIndex(h => h.id_navade == id);
                    if (idx !== -1) habits.splice(idx, 1);

                    renderHabits();

                    document.getElementById('detailEmpty').style.display = 'block';
                    document.getElementById('detailContent').style.display = 'none';
                    selectedHabitId = null;
                } else {
                    showToast('Napaka pri brisanju navade.', 'error');
                }
            })
            .catch(() => showToast('Napaka pri brisanju navade.', 'error'));
        }

        // ===================================================
        // UREJANJE NAVADE
        // ===================================================
        function openEditHabitForm(habit) {
            document.getElementById('habitName').value = habit.ime_navade;
            document.getElementById('habitDescription').value = habit.opis || '';
            document.getElementById('frequencySelect').value = habit.ponavljanje;
            document.getElementById('editHabitId').value = habit.id_navade;
            document.getElementById('habitForm').action = 'logika/uredi_navado.php';
            document.getElementById('formTitle').textContent = 'Uredi navado';
            document.querySelector('#habitForm .btn-save').textContent = 'Shrani spremembe';

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

            const katSelect = document.querySelector('select[name="kategorija"]');
            if (katSelect && habit.kategorija_ime) katSelect.value = habit.kategorija_ime.toLowerCase();

            const kolicina = document.querySelector('input[name="cilj_kolicina"]');
            const enota    = document.querySelector('select[name="cilj_enota"]');
            const obdobje  = document.querySelector('select[name="cilj_obdobje"]');
            if (kolicina) kolicina.value = habit.cilj_kolicina;
            if (enota)    enota.value    = habit.cilj_enota;
            if (obdobje)  obdobje.value  = habit.cilj_obdobje;
            const ciljDni = document.getElementById('ciljDniInput');
            if (ciljDni)  ciljDni.value  = habit.cilj_dni || '';

            if (typeof window.setHabitEmoji === 'function') {
                window.setHabitEmoji(habit.emoji || '');
            }

            openAddHabitForm(); // odpre modal (doda CSS razred 'active')
        }

        // ===================================================
        // MODAL ZA DODAJANJE / ZAPIRANJE
        // ===================================================
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
                if (typeof window.setHabitEmoji === 'function') window.setHabitEmoji('');
            }
        }

        // ===================================================
        // STATISTIKA
        // ===================================================
        function openStatistika() {
            document.getElementById('statistikaModal').classList.add('active');
            document.getElementById('overlay').classList.add('active');
        }
        function closeStatistika() {
            document.getElementById('statistikaModal').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
        }
        document.getElementById('closeStatistika').addEventListener('click', closeStatistika);

        // ===================================================
        // MESEČNI KOLEDAR NAVADE
        // ===================================================
        let calYear    = new Date().getFullYear();
        let calMonth   = new Date().getMonth() + 1;
        let calHabitId = null;

        function loadHabitChart(habitId) {
            calHabitId = habitId;
            renderHabitChart();
        }

        function renderHabitChart() {
            if (!calHabitId) return;

            const todayDate   = new Date();
            const isThisMonth = calYear === todayDate.getFullYear() && calMonth === todayDate.getMonth() + 1;
            const todayDay    = todayDate.getDate();

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

                    const firstDayRaw = new Date(calYear, calMonth - 1, 1).getDay();
                    const firstDay    = (firstDayRaw + 6) % 7;
                    const daysInMonth = new Date(calYear, calMonth, 0).getDate();
                    const daysInPrev  = new Date(calYear, calMonth - 1, 0).getDate();

                    for (let i = 0; i < firstDay; i++) {
                        const cell = document.createElement('div');
                        cell.className = 'cal-day cal-day-other';
                        cell.textContent = daysInPrev - firstDay + 1 + i;
                        grid.appendChild(cell);
                    }

                    for (let d = 1; d <= daysInMonth; d++) {
                        const cell = document.createElement('div');
                        const done = loggedDays.includes(d);
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
                body: `id_navade=${encodeURIComponent(calHabitId)}&datum=${encodeURIComponent(datum)}&csrf_token=${encodeURIComponent(CSRF)}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                const todayStr = new Date().toISOString().split('T')[0];
                if (datum === todayStr) {
                    const hid = Number(calHabitId);
                    const idx = loggedToday.indexOf(hid);
                    if (data.logged) { if (idx === -1) loggedToday.push(hid); }
                    else             { if (idx !== -1) loggedToday.splice(idx, 1); }
                }

                const habit = habits.find(h => Number(h.id_navade) === Number(calHabitId));
                if (habit) habit.streak = data.streak;
                document.getElementById('detailStreak').textContent = data.streak;

                renderHabits();
                renderHabitChart();
            })
            .catch(() => {});
        }

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

        // ===================================================
        // DATUM V GLAVI
        // ===================================================
        function setCalendarDate() {
            const now = new Date();
            document.getElementById('calendarDate').textContent =
                now.toLocaleDateString('sl-SI', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        setCalendarDate();

        // ===================================================
        // POSLUŠALCI DOGODKOV
        // ===================================================
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

        // ===================================================
        // ISKANJE IN FILTRIRANJE
        // ===================================================
        function setDelDnevaFilter(val) {
            filterDelDneva = (filterDelDneva === val) ? '' : val;
            document.querySelectorAll('.sidebar-time-btn').forEach(function(btn) {
                btn.classList.toggle('sidebar-link-active', btn.dataset.filter === filterDelDneva);
            });
            renderHabits();
        }

        function filterByKategorija(name) {
            filterKategorija = (filterKategorija === name) ? '' : name;
            const sel = document.getElementById('filterKategorija');
            if (sel) sel.value = filterKategorija;
            document.querySelectorAll('.sidebar-kat-link').forEach(function(a) {
                a.classList.toggle('sidebar-link-active', a.dataset.kat === filterKategorija);
            });
            renderHabits();
        }

        function openNovaKategorija() {
            const form = document.getElementById('novaKategorijaPanel');
            if (form) form.classList.toggle('active');
        }

        var novaKatForm = document.getElementById('novaKategorijaFormEl');
        if (novaKatForm) {
            novaKatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const ime   = document.getElementById('novaKatIme').value.trim();
                const barva = document.getElementById('novaKatBarva').value;
                if (!ime) return;
                fetch('logika/dodaj_kategorijo.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'ime=' + encodeURIComponent(ime) + '&barva=' + encodeURIComponent(barva) + '&csrf_token=' + encodeURIComponent(CSRF)
                })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    if (d.success) {
                        const label = ime.charAt(0).toUpperCase() + ime.slice(1);
                        const sel = document.getElementById('filterKategorija');
                        if (sel) {
                            const opt = document.createElement('option');
                            opt.value = ime; opt.textContent = label;
                            sel.appendChild(opt);
                        }
                        const habSel = document.getElementById('habitKategorijaSelect');
                        if (habSel) {
                            const opt2 = document.createElement('option');
                            opt2.value = ime; opt2.textContent = label;
                            habSel.appendChild(opt2);
                            habSel.value = ime;
                        }
                        document.getElementById('novaKatIme').value = '';
                        document.getElementById('novaKategorijaPanel').classList.remove('active');
                    } else {
                        showToast(d.error || 'Napaka pri dodajanju kategorije.', 'error');
                    }
                });
            });
        }

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

        document.getElementById('searchInput').addEventListener('input', function() {
            searchQuery = this.value;
            renderHabits();
        });

        (function populateKategorijeFilter() {
            const sel = document.getElementById('filterKategorija');
            kategorijeData.forEach(kat => {
                const opt = document.createElement('option');
                opt.value = kat.ime;
                opt.textContent = kat.ime.charAt(0).toUpperCase() + kat.ime.slice(1);
                sel.appendChild(opt);
            });
        })();

        document.getElementById('filterKategorija').addEventListener('change', function() {
            filterKategorija = this.value;
            renderHabits();
        });

        // ===================================================
        // INICIALIZACIJA
        // ===================================================
        renderHabits();
        if (habits.length > 0) selectHabit(habits[0].id_navade);
    </script>
</body>
<?php endif; ?>
</html>

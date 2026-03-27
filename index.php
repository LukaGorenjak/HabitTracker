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

    // Kategorije za sidebar
    $stmt = $pdo->prepare("SELECT id_kategorije, ime, barva FROM kategorije WHERE id_uporabnika = ? ORDER BY ime ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $kategorijeList = $stmt->fetchAll();

    // Statistika uporabnika
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
<!-- ============================================= NON-LOGGED IN HTML ============================================== -->
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

<!-- ============================================= LOGGED IN HTML ============================================== -->
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
        // PODATKI IZ PHP — PHP je že zagnal in nam dal te vrednosti
        // json_encode() pretvori PHP array → JSON niz → JS ga razume kot array objektov
        // ===================================================
        const MONTH_NAMES_SL = ['Januar','Februar','Marec','April','Maj','Junij',
                                 'Julij','Avgust','September','Oktober','November','December'];

        // habits = array vseh navad tega uporabnika (pridobljeno iz baze prek PHP)
        // JSON_HEX_TAG|JSON_HEX_QUOT zaščiti pred XSS (znaki < > " se zakodirajo)
        const habits = <?php echo json_encode($navade, JSON_HEX_TAG | JSON_HEX_QUOT); ?>;

        // loggedToday = array ID-jev navad, ki so bile danes že zabeležene (opravljeno=1)
        const loggedToday = <?php echo json_encode($logged_today); ?>;

        // kategorijeData = kategorije tega uporabnika (za sidebar in filtriranje)
        const kategorijeData = <?php echo json_encode($kategorijeList ?? []); ?>;

        // currentUser = podatki prijavljenega uporabnika (ime, email, pot do profilne slike)
        const currentUser = <?php echo json_encode([
            'ime'           => $currentUser['uporabnisko_ime'] ?? '',
            'email'         => $currentUser['email']          ?? '',
            'profilna_slika'=> $currentUser['profilna_slika'] ?? null,
        ]); ?>;

        // CSRF token za zaščito AJAX zahtevkov
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        // selectedHabitId = ID trenutno izbrane navade (null = nobena ni izbrana)
        let selectedHabitId = null;

        // Iskalni niz in filtri (prazno = prikaži vse)
        let searchQuery      = '';
        let filterKategorija = '';
        let filterDelDneva   = '';

        // ===================================================
        // POMOŽNE FUNKCIJE
        // ===================================================

        // escapeHtml: zaščiti pred XSS napadom
        // Če bi ime navade vsebovalo script alert(1) script, bi to postalo varno besedilo
        // Trik: browser sam zakodira HTML znake, ko jih vstavljamo kot textNode
        function escapeHtml(str) {
            const div = document.createElement('div');        // ustvari začasni div
            div.appendChild(document.createTextNode(str ?? '')); // vstavi kot čisto besedilo (ne HTML)
            return div.innerHTML;                             // preberi nazaj — znaki so zdaj zakodirani
        }

        // formatDate: pretvori "2025-03-13" v "13. marca 2025" (slovenščina)
        // toLocaleDateString('sl-SI') samodejno formatira glede na slovensko lokalizacijo
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('sl-SI', { day: 'numeric', month: 'long', year: 'numeric' });
        }

        // ===================================================
        // IZRIS SEZNAMA NAVAD
        // ===================================================

        // renderHabits: izriše celoten seznam navad iz array-a habits
        // Kliče se ob zagonu, po beleženju in po brisanju — vedno iz svežih podatkov
        function renderHabits() {
            const habitList = document.getElementById('habitList');
            habitList.innerHTML = ''; // pobriši obstoječi seznam

            // Filtriraj navade glede na iskalni niz, kategorijo in del dneva
            const q = searchQuery.toLowerCase();
            const filtered = habits.filter(h => {
                const imeMatch = h.ime_navade.toLowerCase().includes(q);
                const katMatch = !filterKategorija || (h.kategorija_ime || '').toLowerCase() === filterKategorija.toLowerCase();
                const delParts = (h.del_dneva || '').split(',').map(d => d.trim());
                const delMatch = !filterDelDneva || delParts.includes(filterDelDneva);
                return imeMatch && katMatch && delMatch;
            });

            // Če ni navad (ali rezultatov), prikaži sporočilo
            if (habits.length === 0) {
                habitList.innerHTML = '<div class="habit-list-empty">Še nimate dodanih navad.<br>Kliknite "+ Dodaj navado" za začetek.</div>';
                return;
            }
            if (filtered.length === 0) {
                habitList.innerHTML = '<div class="habit-list-empty">Nobena navada ne ustreza iskanju.</div>';
                return;
            }

            // Za vsako filtrirano navado ustvari HTML element in ga dodaj na stran
            filtered.forEach((habit) => {
                const item = document.createElement('div');
                item.className = 'habit-item';
                item.dataset.id = habit.id_navade; // shranimo ID za kasnejši dostop

                const dotColor = habit.kategorija_barva || '#4a9d6f'; // barva kategorije ali privzeta zelena
                // Preverimo, ali je ta navada v loggedToday array-u (== bila zabeležena danes)
                const isLogged = loggedToday.includes(Number(habit.id_navade));

                const goal = parseInt(habit.cilj_dni) || 0;
                const streak = parseInt(habit.streak) || 0;
                const progressBar = goal > 0
                    ? `<div class="habit-progress-bar"><div class="habit-progress-fill" style="width:${Math.min((streak/goal)*100,100).toFixed(1)}%; background:${dotColor};"></div></div>`
                    : '';

                // template literal: ustvari HTML niz z vstavljenimi vrednostmi
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

                // e.stopPropagation() prepreči, da bi klik na gumb sprožil tudi klik na celoten item
                item.querySelector('.habit-log-btn').addEventListener('click', (e) => {
                    e.stopPropagation();
                    toggleLog(habit.id_navade);
                });

                // Klik na celoten element odpre podrobnosti v desnem panelu
                item.addEventListener('click', () => selectHabit(habit.id_navade));
                habitList.appendChild(item);
            });
        }

        // ===================================================
        // BELEŽENJE NAVADE ZA DANES (toggle)
        // ===================================================

        // toggleLog: zabeleži ali odznači navado za danes brez ponovnega nalaganja strani (AJAX)
        function toggleLog(id) {
            // fetch() pošlje POST zahtevek na PHP — kot da bi oddali HTML formo, brez page reload
            fetch('logika/zabeleznaj_navado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_navade=${encodeURIComponent(id)}&csrf_token=${encodeURIComponent(CSRF)}`
            })
            .then(res => res.json())    // PHP odgovori z JSON nizom → pretvorimo v JS objekt
            .then(data => {
                if (data.success) {
                    const habitId = Number(id);
                    const idx = loggedToday.indexOf(habitId);

                    // Posodobimo lokalni loggedToday array glede na PHP odgovor
                    if (data.logged) {
                        if (idx === -1) loggedToday.push(habitId); // dodamo, če še ni
                    } else {
                        if (idx !== -1) loggedToday.splice(idx, 1); // odstranimo, če je
                    }

                    // Posodobimo streak v lokalnem habits array (brez ponovnega branja baze)
                    const habit = habits.find(h => Number(h.id_navade) === habitId);
                    if (habit) habit.streak = data.streak;

                    renderHabits(); // osveži seznam (ikona ○/✓ in streak badge)

                    // Če je ta navada trenutno odprta v desnem panelu, posodobi tudi tam
                    if (selectedHabitId && Number(selectedHabitId) === habitId) {
                        document.getElementById('detailStreak').textContent = data.streak;
                        selectHabit(id);
                    }
                }
            })
            .catch(() => {}); // tiho ignoriramo napake omrežja
        }

        // ===================================================
        // PRIKAZ PODROBNOSTI NAVADE (desni panel)
        // ===================================================

        // selectHabit: ko kliknemo navado, zapolni desni panel z vsemi podrobnostmi
        function selectHabit(id) {
            selectedHabitId = id;

            // Poiščemo navado v lokalnem array-u (ne gre v bazo — podatki so že v JS)
            const habit = habits.find(h => h.id_navade == id);
            if (!habit) return;

            // Skrijemo sporočilo "Izberite navado" in prikažemo vsebino
            document.getElementById('detailEmpty').style.display = 'none';
            document.getElementById('detailContent').style.display = 'block';

            // Zapolnimo osnovne podatke
            document.getElementById('detailTitle').textContent = habit.ime_navade;
            document.getElementById('detailCategoryDot').style.background = habit.kategorija_barva || '#4a9d6f';
            document.getElementById('detailStreak').textContent = habit.streak || 0;

            // Chart.js doughnut — prikaže progress do streak cilja
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

            // Prevedemo interno vrednost ponavljanja v slovensko besedilo
            const ponavljanjeMap = { dnevno: 'Dnevno', tedensko: 'Tedensko', mesecno: 'Mesečno' };
            document.getElementById('detailPonavljanje').textContent = ponavljanjeMap[habit.ponavljanje] || habit.ponavljanje;

            // Vrstica "Dnevi" — prikaže se samo pri tedenskem ponavljanju s specifičnimi dnevi
            const dneviRow = document.getElementById('detailDneviRow');
            if (habit.ponavljanje === 'tedensko' && habit.izbrani_dnevi && habit.izbrani_dnevi !== 'vsak_dan') {
                dneviRow.style.display = 'flex';
                // Prevedemo angleška imena dni v slovenščino
                const dayMap = { ponedeljek: 'Ponedeljek', torek: 'Torek', sreda: 'Sreda', cetrtek: 'Četrtek', petek: 'Petek', sobota: 'Sobota', nedelja: 'Nedelja' };
                // split(',') razdeli "ponedeljek,sreda" → ['ponedeljek', 'sreda'], nato prevedemo vsak dan
                const days = habit.izbrani_dnevi.split(',').map(d => dayMap[d.trim()] || d).join(', ');
                document.getElementById('detailDnevi').textContent = days;
            } else {
                dneviRow.style.display = 'none'; // skrijemo vrstico, če ni relevantna
            }

            // Cilj: sestavimo niz npr. "30 minut na dan"
            const obdobjeMap = { na_dan: 'na dan', na_teden: 'na teden', na_mesec: 'na mesec' };
            document.getElementById('detailCilj').textContent =
                `${habit.cilj_kolicina} ${habit.cilj_enota} ${obdobjeMap[habit.cilj_obdobje] || habit.cilj_obdobje}`;

            // Vrstica "Del dneva" — prikaže se samo, če je nastavljeno
            const delDnevaRow = document.getElementById('detailDelDnevaRow');
            if (habit.del_dneva && habit.del_dneva.trim()) {
                delDnevaRow.style.display = 'flex';
                const delMap = { zjutraj: 'Zjutraj', popoldne: 'Popoldne', zvecer: 'Zvečer' };
                // Prevedemo "zjutraj,zvecer" → "Zjutraj, Zvečer"
                document.getElementById('detailDelDneva').textContent =
                    habit.del_dneva.split(',').map(d => delMap[d.trim()] || d).join(', ');
            } else {
                delDnevaRow.style.display = 'none';
            }

            // Vrstica "Opis" — prikaže se samo, če je vnesen opis
            const opisRow = document.getElementById('detailOpisRow');
            if (habit.opis && habit.opis.trim()) {
                opisRow.style.display = 'flex';
                document.getElementById('detailOpis').textContent = habit.opis;
            } else {
                opisRow.style.display = 'none';
            }

            // Vizualno označimo izbran element v seznamu
            document.querySelectorAll('.habit-item').forEach(el => el.classList.remove('selected'));
            const selectedEl = document.querySelector(`.habit-item[data-id="${id}"]`);
            if (selectedEl) selectedEl.classList.add('selected');

            // Povežemo gumba Uredi in Izbriši s to konkretno navado
            // onclick se prepiše vsakič, ko izberemo navado — da vedno deluje na pravilno
            document.getElementById('editHabitBtn').onclick = () => openEditHabitForm(habit);
            document.getElementById('deleteHabitBtn').onclick = () => deleteHabit(habit.id_navade, habit.ime_navade);

            // Naložimo mesečni koledar za to navado
            loadHabitChart(habit.id_navade);
        }

        // ===================================================
        // BRISANJE NAVADE
        // ===================================================

        // deleteHabit: po potrditvi zbriše navado prek AJAX in jo odstrani iz UI
        function deleteHabit(id, name) {
            // Potrditveno okno — če kliknemo "Prekliči", funkcija takoj konča
            if (!confirm(`Ste prepričani, da želite izbrisati navado "${name}"?`)) return;

            fetch('logika/izbrisi_navado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_navade=${encodeURIComponent(id)}&csrf_token=${encodeURIComponent(CSRF)}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Poiščemo index navade v lokalnem array-u in jo odstranimo
                    const idx = habits.findIndex(h => h.id_navade == id);
                    if (idx !== -1) habits.splice(idx, 1); // splice(index, 1) odstrani 1 element

                    renderHabits(); // osvežimo seznam (navada izgine)

                    // Skrijemo desni panel
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
        // UREJANJE NAVADE — predizpolni modal z obstoječimi podatki
        // ===================================================

        // openEditHabitForm: odpre isti modal kot za dodajanje, a zapolnjen z obstoječimi podatki
        // To je primer "koda, ki naredi dve stvari" — isti modal za dodaj in uredi
        function openEditHabitForm(habit) {
            // Zapolnimo vsa polja z obstoječimi vrednostmi
            document.getElementById('habitName').value = habit.ime_navade;
            document.getElementById('habitDescription').value = habit.opis || '';
            document.getElementById('frequencySelect').value = habit.ponavljanje;
            // Skrito polje id_navade — PHP ga prebere in ve, katero navado posodabljamo
            document.getElementById('editHabitId').value = habit.id_navade;

            // Spremenimo action forme — namesto shrani_navado.php gre na uredi_navado.php
            document.getElementById('habitForm').action = 'logika/uredi_navado.php';
            document.getElementById('formTitle').textContent = 'Uredi navado';
            document.querySelector('#habitForm .btn-save').textContent = 'Shrani spremembe';

            // Dropdown z dnevi — prikaže se samo pri tedenskem ponavljanju
            if (habit.ponavljanje === 'tedensko') {
                document.getElementById('daysDropdown').style.display = 'block';
                // Najprej odkljukamo vse
                document.querySelectorAll('#daysContent input[type="checkbox"]').forEach(cb => cb.checked = false);
                if (habit.izbrani_dnevi && habit.izbrani_dnevi !== 'vsak_dan') {
                    // Preslikava: slovensko ime dneva → HTML id checkboxa
                    const dayIdMap = { ponedeljek: 'monday', torek: 'tuesday', sreda: 'wednesday', cetrtek: 'thursday', petek: 'friday', sobota: 'saturday', nedelja: 'sunday' };
                    habit.izbrani_dnevi.split(',').forEach(day => {
                        const cbId = dayIdMap[day.trim()];
                        if (cbId) document.getElementById(cbId).checked = true; // kljukamo pravilne
                    });
                }
                updateDaysButtonText(); // posodobimo besedilo gumba ("Ponedeljek, Sreda...")
            } else {
                document.getElementById('daysDropdown').style.display = 'none';
            }

            // Nastavimo kategorijo v selectu
            const katSelect = document.querySelector('select[name="kategorija"]');
            if (katSelect && habit.kategorija_ime) katSelect.value = habit.kategorija_ime.toLowerCase();

            // Nastavimo cilj (količina, enota, obdobje)
            const kolicina = document.querySelector('input[name="cilj_kolicina"]');
            const enota    = document.querySelector('select[name="cilj_enota"]');
            const obdobje  = document.querySelector('select[name="cilj_obdobje"]');
            if (kolicina) kolicina.value = habit.cilj_kolicina;
            if (enota)    enota.value    = habit.cilj_enota;
            if (obdobje)  obdobje.value  = habit.cilj_obdobje;
            const ciljDni = document.getElementById('ciljDniInput');
            if (ciljDni)  ciljDni.value  = habit.cilj_dni || '';

            // Predpolni emoji picker
            if (typeof window.setHabitEmoji === 'function') {
                window.setHabitEmoji(habit.emoji || '');
            }

            openAddHabitForm(); // odpre modal (doda CSS razred 'active')
        }

        // ===================================================
        // MODAL ZA DODAJANJE / ZAPIRANJE
        // ===================================================

        // openAddHabitForm: prikaže modal in overlay z dodajanjem CSS razreda 'active'
        // CSS: .add-habit-form.active { display: block } — modal postane viden
        function openAddHabitForm() {
            document.getElementById('addHabitForm').classList.add('active');
            document.getElementById('overlay').classList.add('active');
        }

        // closeAddHabitForm: zapre modal, ponastavi formo nazaj na "dodaj" stanje
        function closeAddHabitForm() {
            document.getElementById('addHabitForm').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
            const habitForm = document.getElementById('habitForm');
            if (habitForm) {
                habitForm.reset();                                          // pobriše vse vnose
                habitForm.action = 'logika/shrani_navado.php';              // ponastavi action
                document.getElementById('editHabitId').value = '';          // pobriše skrito ID polje
                document.getElementById('formTitle').textContent = 'Nova navada';
                document.querySelector('#habitForm .btn-save').textContent = 'Shrani';
                document.getElementById('daysDropdown').style.display = 'none';
                if (typeof window.setHabitEmoji === 'function') window.setHabitEmoji('');
            }
        }

        // ===================================================
        // STATISTIKA
        // ===================================================

        // openStatistika / closeStatistika
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

        // Spremenljivke za sledenje kateremu mesecu/letu in kateri navadi je odprt koledar
        let calYear     = new Date().getFullYear();
        let calMonth    = new Date().getMonth() + 1; // getMonth() vrne 0-11, zato +1
        let calHabitId  = null;

        // loadHabitChart: vstopna točka — nastavi ID navade in sproži izris
        function loadHabitChart(habitId) {
            calHabitId = habitId;
            renderHabitChart();
        }

        // renderHabitChart: naloži podatke iz baze in izriše mesečno mrežo
        function renderHabitChart() {
            if (!calHabitId) return;

            const todayDate   = new Date();
            // isThisMonth: ali gledamo tekoči mesec (za onemogočanje prihodnjih dni)
            const isThisMonth = calYear === todayDate.getFullYear() && calMonth === todayDate.getMonth() + 1;
            const todayDay    = todayDate.getDate();

            // Barva kategorije za obarvanje opravljenih dni
            const habit = habits.find(h => Number(h.id_navade) === Number(calHabitId));
            const catColor = (habit && habit.kategorija_barva) ? habit.kategorija_barva : '#4a9d6f';

            document.getElementById('chartMonthLabel').textContent =
                `${MONTH_NAMES_SL[calMonth - 1]} ${calYear}`;

            // GET zahtevek na PHP — vrne array datumov ko je bila navada opravljena v tem mesecu
            fetch(`logika/mesecni_dnevnik.php?id_navade=${encodeURIComponent(calHabitId)}&leto=${calYear}&mesec=${calMonth}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    // Pretvorimo "2026-03-05" → 5 (samo številka dneva)
                    const loggedDays = data.opravljeni.map(d => parseInt(d.split('-')[2]));
                    const grid = document.getElementById('habitCalendarGrid');
                    grid.innerHTML = ''; // počistimo prejšnji mesec

                    // Izračunamo na kateri dan v tednu pade 1. dan meseca (po. = 0, ... ne. = 6)
                    const firstDayRaw = new Date(calYear, calMonth - 1, 1).getDay(); // 0=ned
                    const firstDay    = (firstDayRaw + 6) % 7; // pretvorimo: pon=0 ... ned=6
                    const daysInMonth = new Date(calYear, calMonth, 0).getDate(); // zadnji dan meseca
                    const daysInPrev  = new Date(calYear, calMonth - 1, 0).getDate(); // dni prejšnjega

                    // Zapolnimo začetek mreže z dnevi prejšnjega meseca (sivo, neklikljivo)
                    for (let i = 0; i < firstDay; i++) {
                        const cell = document.createElement('div');
                        cell.className = 'cal-day cal-day-other';
                        cell.textContent = daysInPrev - firstDay + 1 + i;
                        grid.appendChild(cell);
                    }

                    // Izrišemo vse dni tekočega meseca
                    for (let d = 1; d <= daysInMonth; d++) {
                        const cell    = document.createElement('div');
                        const done    = loggedDays.includes(d);      // ali je dan opravljen?
                        const isToday = isThisMonth && d === todayDay; // ali je danes?
                        const future  = isThisMonth && d > todayDay;   // ali je prihodnji dan?

                        // Sestavimo CSS razred iz kombinacije pogojev
                        cell.className = 'cal-day' +
                            (isToday ? ' cal-day-today' : '') +
                            (future  ? ' cal-day-future' : '') +
                            (done    ? ' cal-day-done'   : '');
                        cell.textContent = d;

                        // Opravljeni dnevi dobijo barvo kategorije kot ozadje
                        if (done) cell.style.background = catColor;

                        // Prihodnji dnevi niso klikljivi — ne moremo beležiti v prihodnosti
                        if (!future) {
                            cell.addEventListener('click', () => {
                                // Sestavimo datum v formatu YYYY-MM-DD (MySQL format)
                                const datum = `${calYear}-${String(calMonth).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                                toggleChartDay(datum);
                            });
                        }
                        grid.appendChild(cell);
                    }

                    // Zapolnimo konec mreže z dnevi naslednjega meseca (sivo, neklikljivo)
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

        // toggleChartDay: zabeleži/odznači specifičen datum v koledarju (ne nujno danes)
        // Enako kot toggleLog, le da pošljemo tudi datum namesto da vzamemo danes
        function toggleChartDay(datum) {
            fetch('logika/zabeleznaj_navado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id_navade=${encodeURIComponent(calHabitId)}&datum=${encodeURIComponent(datum)}&csrf_token=${encodeURIComponent(CSRF)}`
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) return;

                // Če smo kliknili na danes, moramo posodobiti tudi loggedToday array
                // (ker ta array upravlja ○/✓ gumb v seznamu navad)
                const todayStr = new Date().toISOString().split('T')[0]; // "2026-03-13"
                if (datum === todayStr) {
                    const hid = Number(calHabitId);
                    const idx = loggedToday.indexOf(hid);
                    if (data.logged) { if (idx === -1) loggedToday.push(hid); }
                    else             { if (idx !== -1) loggedToday.splice(idx, 1); }
                }

                // Posodobimo streak v lokalnem array-u in v panelu
                const habit = habits.find(h => Number(h.id_navade) === Number(calHabitId));
                if (habit) habit.streak = data.streak;
                document.getElementById('detailStreak').textContent = data.streak;

                renderHabits();     // osvežimo seznam (streak badge)
                renderHabitChart(); // osvežimo koledar (obarvanost dneva)
            })
            .catch(() => {});
        }

        // Navigacija med meseci — ‹ in › gumba
        document.getElementById('chartPrevMonth').addEventListener('click', () => {
            calMonth--;
            if (calMonth < 1) { calMonth = 12; calYear--; } // december → november lani
            renderHabitChart();
        });
        document.getElementById('chartNextMonth').addEventListener('click', () => {
            const now = new Date();
            // Ne dovolimo navigacije v prihodnost (čez tekoči mesec)
            if (calYear === now.getFullYear() && calMonth === now.getMonth() + 1) return;
            calMonth++;
            if (calMonth > 12) { calMonth = 1; calYear++; } // december → januar naslednje leto
            renderHabitChart();
        });

        // ===================================================
        // DATUM V GLAVI
        // ===================================================

        // setCalendarDate: izpiše trenutni datum v zgornji navigaciji (samo enkrat ob nalaganju)
        function setCalendarDate() {
            const now = new Date();
            document.getElementById('calendarDate').textContent =
                now.toLocaleDateString('sl-SI', { year: 'numeric', month: 'short', day: 'numeric' });
        }
        setCalendarDate();

        // ===================================================
        // POSLUŠALCI DOGODKOV (Event listeners)
        // ===================================================

        // Gumb "+ Dodaj navado" — odpre prazen modal (najprej ponastavimo morebitne podatke od urejanja)
        document.getElementById('addHabitBtn').addEventListener('click', () => {
            document.getElementById('formTitle').textContent = 'Nova navada';
            document.querySelector('#habitForm .btn-save').textContent = 'Shrani';
            document.getElementById('editHabitId').value = '';
            document.getElementById('habitForm').action = 'logika/shrani_navado.php';
            openAddHabitForm();
        });

        // Klik na overlay (zatemnjen del za modalom) — zapre katerikoli odprt modal
        document.getElementById('overlay').addEventListener('click', () => {
            closeAddHabitForm();
            closeNastavitve();
        });

        // Gumb Prekliči v modalu za navade
        document.getElementById('cancelBtn').addEventListener('click', closeAddHabitForm);

        // ===================================================
        // ISKANJE IN FILTRIRANJE
        // ===================================================

        // setDelDnevaFilter: filter navad po delu dneva (zjutraj/popoldne/zvecer)
        // Drugi klik na isti gumb razklene filter (toggle)
        function setDelDnevaFilter(val) {
            filterDelDneva = (filterDelDneva === val) ? '' : val;
            // Vizualno označi aktiven gumb v sidebaru
            document.querySelectorAll('.sidebar-time-btn').forEach(function(btn) {
                btn.classList.toggle('sidebar-link-active', btn.dataset.filter === filterDelDneva);
            });
            renderHabits();
        }

        // filterByKategorija: filter navad po kategoriji (iz sidebara)
        function filterByKategorija(name) {
            filterKategorija = (filterKategorija === name) ? '' : name;
            // Sinhronizacija z dropdown v top navu
            const sel = document.getElementById('filterKategorija');
            if (sel) sel.value = filterKategorija;
            // Vizualno označi aktiven element
            document.querySelectorAll('.sidebar-kat-link').forEach(function(a) {
                a.classList.toggle('sidebar-link-active', a.dataset.kat === filterKategorija);
            });
            renderHabits();
        }

        // openNovaKategorija: pokaže/skrije mini formo za dodajanje kategorije
        function openNovaKategorija() {
            const form = document.getElementById('novaKategorijaPanel');
            if (form) form.classList.toggle('active');
        }

        // Pošlji novo kategorijo na strežnik
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
                        // Dodaj v filter dropdown zgoraj
                        const sel = document.getElementById('filterKategorija');
                        if (sel) {
                            const opt = document.createElement('option');
                            opt.value = ime; opt.textContent = label;
                            sel.appendChild(opt);
                        }
                        // Dodaj v select v formi za navado
                        const habSel = document.getElementById('habitKategorijaSelect');
                        if (habSel) {
                            const opt2 = document.createElement('option');
                            opt2.value = ime; opt2.textContent = label;
                            habSel.appendChild(opt2);
                            habSel.value = ime; // takoj izberi novo kategorijo
                        }
                        document.getElementById('novaKatIme').value = '';
                        document.getElementById('novaKategorijaPanel').classList.remove('active');
                    } else {
                        showToast(d.error || 'Napaka pri dodajanju kategorije.', 'error');
                    }
                });
            });
        }

        // Hamburger – odpri/zapri sidebar na mobilnih napravah
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

        // Filtriranje ob tipkanju v iskalno polje
        document.getElementById('searchInput').addEventListener('input', function() {
            searchQuery = this.value;
            renderHabits();
        });

        // Zapolni dropdown kategorij iz vseh kategorij uporabnika (ne samo tistih z navadami)
        (function populateKategorijeFilter() {
            const sel = document.getElementById('filterKategorija');
            kategorijeData.forEach(kat => {
                const opt = document.createElement('option');
                opt.value = kat.ime;
                opt.textContent = kat.ime.charAt(0).toUpperCase() + kat.ime.slice(1);
                sel.appendChild(opt);
            });
        })();

        // Filtriranje ob spremembi kategorije
        document.getElementById('filterKategorija').addEventListener('change', function() {
            filterKategorija = this.value;
            renderHabits();
        });

        // ===================================================
        // INICIALIZACIJA — zažene se ob nalaganju strani
        // ===================================================
        renderHabits(); // izriše seznam navad
        // Če obstajajo navade, samodejno odpre prvo (boljša UX)
        if (habits.length > 0) selectHabit(habits[0].id_navade);
    </script>
</body>
<?php endif; ?>
</html>

# Razlaga kode za zagovor — HabitFlow

---

## 1. KAK PHP SPLOH DELUJE

PHP se izvede na **strežniku** (XAMPP), PREDEN se karkoli pošlje brskalniku.
Brskalnik ne vidi PHP kode — vidi samo rezultat (HTML, JSON...).

```
Brskalnik zahteva stran
       ↓
Strežnik (XAMPP) požene PHP
       ↓
PHP prebere bazo, sestavi HTML
       ↓
Brskalnik dobi čist HTML + CSS + JS
```

JavaScript pa se izvede v **brskalniku**, POTEM ko je stran že naložena.

---

## 2. SEJE (SESSIONS) — prijava (MORAM SPREMENIT DA JE SEJA 5min)

```php
session_start(); // VEDNO mora biti prva vrstica — zažene sejo
```

Ko se uporabnik prijavi (`prijava.php`), se zgodi:

```php
$_SESSION['user_id'] = $user['id_uporabnika']; // shranimo ID v sejo
$_SESSION['username'] = $user['uporabnisko_ime'];
```

`$_SESSION` je kot "spomin" strežnika — pamti, kdo je prijavljen med zahtevki.
Ko zapremo brskalnik ali pokličemo `session_destroy()`, se seja izbriše.

Zaščita zasebnih strani:
```php
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php"); // preusmeri neprijavljene
    exit(); // OBVEZNO po header() — sicer PHP teče naprej
}
```

---

## 3. BAZA PODATKOV — PDO

PDO (PHP Data Objects) je način, kako PHP komunicira z MySQL bazo.

### Vzpostavitev povezave (`konfiguracija/db.php`):
```php
$dsn = "mysql:host=127.0.0.1;dbname=habit_tracker;charset=utf8mb4";
$pdo = new PDO($dsn, 'root', '', $options);
```
- `$dsn` = connection string (naslov baze, ime baze, nabor znakov)
- `new PDO(...)` = ustvari povezavo, shrani jo v `$pdo`
- Ta datoteka se vključi z `require_once` — `$pdo` je potem dostopen

### Prepared statements (zaščita pred SQL injection):
```php
$stmt = $pdo->prepare("SELECT * FROM navade WHERE id_uporabnika = ?");
$stmt->execute([$id_uporabnika]); // ? se zamenja z vrednostjo — varno!
$navade = $stmt->fetchAll();      // vrne vse vrstice kot array
```

**Zakaj prepared statements?** Brez njih bi lahko nekdo vpisal:
`' OR 1=1 --` in dobil vse podatke iz baze. S prepared statements to ni možno,
ker `?` nikoli ni interpretiran kot SQL koda.

### fetchAll() vs fetch():
```php
$stmt->fetchAll(); // vrne VSE vrstice (array arrayev)
$stmt->fetch();    // vrne SAMO PRVO vrstico
```

---

## 4. GESLA — password_hash

```php
// Pri registraciji — NIKOLI ne shranjujemo gesla direktno!
$password_hash = password_hash($password, PASSWORD_DEFAULT);
// Shrani se npr: $2y$10$abc123... (nečitljivo)

// Pri prijavi — primerjamo hash, ne gesla
if (password_verify($password, $user['hash_gesla'])) {
    // geslo je pravilno
}
```

`password_hash` geslo "zmeša" z algoritmom bcrypt. Tudi če heker ukrade bazo,
gesel ne more prebrati nazaj.

---

## 5. KAKO PHP PODA PODATKE JAVASCRIPTU

PHP teče na strežniku, JS v brskalniku — ne moreta direktno komunicirati.
Rešitev: PHP zapiše podatke kot JSON direktno v HTML.

```php
// V index.php — PHP pripravi podatke:
$navade = $stmt->fetchAll(); // array iz baze

// Potem v <script> bloku:
const habits = <?php echo json_encode($navade); ?>;
// Rezultat v brskalniku izgleda tako:
// const habits = [{"id_navade":1,"ime_navade":"Branje",...}, ...]
```

`json_encode()` pretvori PHP array → JSON niz, ki ga JS razume kot array objektov.

---

## 6. JAVASCRIPT — KAK DELUJE

JS se izvede v brskalniku. Nima dostopa do baze — le do tega, kar mu PHP poda.

### DOM manipulacija:
```javascript
// Najdi element po ID
document.getElementById('habitList')

// Ustvari nov element
const item = document.createElement('div');
item.className = 'habit-item';
item.innerHTML = `<div>${habit.ime_navade}</div>`;

// Dodaj v stran
habitList.appendChild(item);
```

### Event listeners (poslušanje klikov):
```javascript
// Ko kliknemo gumb, se požene funkcija
document.getElementById('addHabitBtn').addEventListener('click', () => {
    openAddHabitForm();
});

// e.stopPropagation() — prepreči, da klik "poteče" do starševskega elementa
logBtn.addEventListener('click', (e) => {
    e.stopPropagation(); // brez tega bi se sprožil tudi klik na habit-item
    toggleLog(habit.id_navade);
});
```

### fetch() — AJAX klici (brez ponovnega nalaganja strani):
```javascript
fetch('logika/zabeleznaj_navado.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id_navade=${id}` // pošljemo podatke kot POST
})
.then(res => res.json())    // odgovor pretvorimo iz JSON v JS objekt
.then(data => {
    if (data.success) {     // PHP nam je odgovoril { success: true, streak: 5 }
        // posodobimo stran brez ponovnega nalaganja
    }
});
```

---

## 7. RAZLAGA VSAKE DATOTEKE

### `index.php`
```
1-31:   PHP del — session_start, poveže bazo, naloži navade in kdo je danes zabeležil
32-75:  HTML — glava, navigacija, seznam navad, panel z detajli
76-82:  habits = ... in loggedToday = ... → PHP podatki gredo v JS
84-93:  escapeHtml() — zaščita pred XSS (če bi ime navade vsebovalo <script>)
95-135: renderHabits() — za vsako navado ustvari HTML element in ga doda na stran
137-169:toggleLog() — fetch klic, ki zabeleži/odznači navado za danes brez reload
171-230:selectHabit() — ko kliknemo navado, zapolni desni panel z detajli
232-256:deleteHabit() — fetch klic, ki zbriše navado, potem jo odstrani iz seznama
258-310:openEditHabitForm() — zapolni obstoječi modal z obstoječimi podatki za urejanje
312-340:openAddHabitForm() / closeAddHabitForm() — odpre/zapre modal
```

### `konfiguracija/db.php`
Samo vzpostavi PDO povezavo z bazo. Vključen z `require_once` v vsaki logiki datoteki.

### `logika/shrani_navado.php`
```
1-15:  session_start + require db + varnostni preveri (login, POST)
17-48: Prebere POST podatke iz forme (ime, ponavljanje, cilj, datumi...)
50-69: Kategorija — poišče obstoječo ali ustvari novo v tabeli kategorije
71-93: INSERT v tabelo navade z vsemi podatki
94:    Preusmeri nazaj na index.php
```

### `logika/uredi_navado.php`
Enako kot shrani, le da na začetku preveri lastništvo navade (varnost!),
nato pa naredi UPDATE namesto INSERT.

### `logika/izbrisi_navado.php`
```php
// Najprej izbriše dnevnike te navade (ker so vezani nanjo)
DELETE FROM dnevniki WHERE id_navade = ?

// Potem izbriše navado — AND id_uporabnika = ? je varnostni filter!
DELETE FROM navade WHERE id_navade = ? AND id_uporabnika = ?

echo json_encode(['success' => true]); // JS to prejme in posodobi stran
```

### `logika/zabeleznaj_navado.php`
```
1-20:  Varnostni preveri
21-45: Preveri, ali je navada danes že zabeležena → toggle (1→0 ali 0→1, ali INSERT)
47-70: Izračun streaka:
       - Vzame vse datume ko je bila navada opravljena, urejene po datumu (najnovejši prvi)
       - Začne od danes in šteje nazaj — vsak dan ki se ujema, poveča streak za 1
       - Takoj ko pride praznina (dan brez vnosa), se ustavi
71-72: UPDATE navade SET streak = ? — shrani nov streak
74-78: Vrne JSON z { success, logged, streak } — JS to použije za posodobitev UI
```

### `avtentikacija/registracija.php`
```
1-46:  PHP — preveri POST podatke, validira email, primerja gesli, hash gesla, INSERT
48-:   HTML — forma za registracijo
```

### `avtentikacija/prijava.php`
```
1-31:  PHP — preveri kredenciale z password_verify(), nastavi sejo, preusmeri
```

### `avtentikacija/odjava.php`
```php
session_start();
session_destroy(); // izbriše sejo
header("Location: ../index.php");
```

### `deli_strani/navigacija.php`
PHP partial — vključen v index.php z `include`. Izpiše ime iz seje: `$_SESSION['username']`.

### `deli_strani/dodaj_novo_navado.php`
HTML forma za dodajanje navad + JS za interaktivnost forme (dropdown dnevov, skrita polja).
Ko se pošlje za urejanje, JS pred oddajo nastavi `action="logika/uredi_navado.php"`
in zapolni skrito polje `id_navade`.

### `deli_strani/podrobnosti_navade.php`
Statični HTML z praznimi elementi (id="detailTitle", id="detailStreak"...).
JS jih zapolni z `document.getElementById(...).textContent = habit.ime_navade`.

---

## 8. PODATKOVNE TABELE

```
uporabniki      → id, ime, email, hash_gesla, vloga, ustvarjeno
     |
     └── navade → id, id_uporabnika, id_kategorije, ime, ponavljanje,
     |            izbrani_dnevi, del_dneva, cilj_*, datumi, streak
     |
     └── kategorije → id, id_uporabnika, ime, barva
     |
     └── (navade) → dnevniki → id, id_navade, datum, opravljeno, komentar
```

Tuji ključi (foreign keys) zagotavljajo, da ne moremo shraniti navade za
neobstoječega uporabnika.

---

## 9. VARNOST — POVZETEK

| Grožnja | Zaščita |
|---|---|
| SQL Injection | Prepared statements (`?` namesto direktnega vstavljanja) |
| XSS (škodljivi skripti v vnosih) | `htmlspecialchars()` pri izpisu, `escapeHtml()` v JS |
| Dostop tujih uporabnikov do navad | `AND id_uporabnika = ?` pri vsaki poizvedbi |
| Shranjeno geslo v bazi | `password_hash()` + `password_verify()` |
| Nepooblaščen dostop do strani | `session_start()` + preverjanje `$_SESSION['user_id']` |

---

## 10. POGOSTA VPRAŠANJA NA ZAGOVORU

**"Kaj je SQL injection in kako ga preprečiš?"**
SQL injection je napad, kjer nekdo v vnosno polje vpiše SQL kodo (npr. `' OR 1=1 --`)
in tako dobi dostop do podatkov. Preprečim ga s prepared statements — vrednosti
nikoli ne vstavljam direktno v SQL string, ampak uporabim `?` in `execute([...])`.

**"Zakaj ne shranjaš gesla direktno?"**
Ker bi ob uhajanju baze (heker ukrade bazo) vsa gesla bila takoj vidna.
`password_hash()` ustvari enoznačni "prstni odtis" gesla, ki ga ne moreš obrniti nazaj.

**"Kaj je seja in zakaj jo uporabljaš?"**
Seja je mehanizem, ki si "zapomni" prijavljenega uporabnika med različnimi
zahtevki. Brez seje bi moral vsako stran začeti znova in uporabnik ne bi bil prijavljen.
Strežnik hrani sejo, brskalnik dobi samo ID seje v piškotku.

**"Razloži razliko med PHP in JavaScript."**
PHP teče na strežniku in pripravi stran (prebere bazo, sestavi HTML).
JavaScript teče v brskalniku in naredi stran interaktivno (kliki, animacije, AJAX).
PHP se izvede ENKRAT ob nalaganju strani. JS teče ves čas, ko je stran odprta.

**"Kaj je AJAX in kje ga uporabljaš?"**
AJAX (Asynchronous JavaScript and XML) omogoča, da JS pošlje zahtevek strežniku
brez ponovnega nalaganja celotne strani. Uporabljam ga pri:
- brisanju navade (`izbrisi_navado.php`) — navada izgine brez reload
- beleženju navade (`zabeleznaj_navado.php`) — streak se posodobi takoj

**"Zakaj imaš `exit()` po `header('Location: ...')`?"**
Ker `header()` samo nastavi glavo odgovora — PHP teče naprej, dokler
ne zadene konec datoteke ali `exit()`. Brez `exit()` bi se izvajalo
vse po preusmeritvi, kar je varnostna luknja.

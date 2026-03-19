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

## 2. SEJE (SESSIONS) — časovna omejitev 10 min

```php
session_start(); // VEDNO mora biti prva vrstica — zažene sejo
```

Ko se uporabnik prijavi (`prijava.php`), se zgodi:

```php
$_SESSION['user_id'] = $user['id_uporabnika']; // shranimo ID v sejo
$_SESSION['username'] = $user['uporabnisko_ime'];
$_SESSION['last_activity'] = time(); // čas zadnje aktivnosti (za timeout)
```

`$_SESSION` je kot "spomin" strežnika — pamti, kdo je prijavljen med zahtevki.

### Časovna omejitev seje (`konfiguracija/seja.php`):
```php
ini_set('session.gc_maxlifetime', 600); // 10 minut
session_start();

// Preverimo, ali je minilo več kot 10 minut od zadnje aktivnosti
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 600) {
    session_unset();   // počistimo spremenljivke
    session_destroy(); // uničimo sejo
    header("Location: ../index.php?timeout=1"); // preusmerimo
    exit();
}
$_SESSION['last_activity'] = time(); // posodobimo čas
```

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
const habits = <?php echo json_encode($navade, JSON_HEX_TAG | JSON_HEX_QUOT); ?>;
// Rezultat v brskalniku izgleda tako:
// const habits = [{"id_navade":1,"ime_navade":"Branje",...}, ...]
```

`json_encode()` pretvori PHP array → JSON niz, ki ga JS razume kot array objektov.
`JSON_HEX_TAG | JSON_HEX_QUOT` sta varnostni zastavici — zakodira `<`, `>`, `"` za zaščito pred XSS.

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

### FormData — AJAX z datotekami (nalaganje slike):
```javascript
// V nasprotju z URLSearchParams, FormData podpira binarne datoteke
const formData = new FormData(document.getElementById('nastavitveFormEl'));
fetch('logika/shrani_nastavitve.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => { /* posodobimo UI */ });
```

### FileReader — predogled slike brez strežnika:
```javascript
// Sliko preberemo lokalno kot base64 URL — brskalnik jo prikaže takoj
const reader = new FileReader();
reader.onload = e => document.getElementById('profilPreview').src = e.target.result;
reader.readAsDataURL(file); // začnemo branje
```

---

## 7. RAZLAGA VSAKE DATOTEKE

### `index.php`
```
1-36:   PHP del — session_start, poveže bazo, naloži navade, logged_today, currentUser
37-95:  HTML — glava (CSS link), navigacija, seznam navad, desni panel, modali

JS funkcije:
escapeHtml()          — zaščita pred XSS: vstavi besedilo kot textNode, ne kot HTML
formatDate()          — pretvori "2026-03-13" v "13. marca 2026" (sl-SI lokalizacija)
renderHabits()        — izriše celoten seznam navad iz array-a, za vsako ustvari DOM element
toggleLog(id)         — AJAX: zabeleži/odznači navado za danes, posodobi loggedToday + streak
selectHabit(id)       — zapolni desni panel z detajli, prikaže/skrije vrstice glede na podatke
deleteHabit(id, name) — AJAX: zbriše navado, jo odstrani iz array-a in UI
openEditHabitForm()   — predizpolni modal za urejanje z obstoječimi podatki
openAddHabitForm()    — doda CSS razred 'active' → modal postane viden
closeAddHabitForm()   — odstrani 'active', ponastavi formo na "dodaj" stanje
openNastavitve()      — odpre nastavitve modal, predizpolni z currentUser podatki
closeNastavitve()     — zapre nastavitve modal
loadHabitChart()      — nastavi calHabitId in sproži renderHabitChart()
renderHabitChart()    — GET fetch za mesečne podatke, izriše 7-stolpčno mrežo dni
toggleChartDay(datum) — AJAX: zabeleži/odznači specifičen datum, osveži streak + mrežo
```

### `konfiguracija/db.php`
Samo vzpostavi PDO povezavo z bazo. Vključen z `require_once` v vsaki logiki datoteki.

### `konfiguracija/seja.php`
Nastavi 10-minutni timeout seje. Preveri `last_activity` in preusmeri, če je seja potekla.
Vključen namesto `session_start()` na zasebnih straneh.

### `logika/shrani_navado.php`
```
1-15:  session_start + require db + varnostni preveri (login, POST)
17-48: Prebere POST podatke iz forme (ime, ponavljanje, cilj, datumi, del_dneva...)
50-69: Kategorija — poišče obstoječo ali ustvari novo v tabeli kategorije
71-93: INSERT v tabelo navade z vsemi podatki
94:    Preusmeri nazaj na index.php
```

### `logika/uredi_navado.php`
Enako kot shrani, le da na začetku preveri lastništvo navade (varnost!),
nato pa naredi UPDATE namesto INSERT.

### `logika/izbrisi_navado.php`
```php
// Najprej izbriše dnevnike te navade (ker so vezani nanjo — tuj ključ)
DELETE FROM dnevniki WHERE id_navade = ?

// Potem izbriše navado — AND id_uporabnika = ? je varnostni filter!
DELETE FROM navade WHERE id_navade = ? AND id_uporabnika = ?

echo json_encode(['success' => true]); // JS to prejme in posodobi stran
```

### `logika/zabeleznaj_navado.php`
```
1-24:  Varnostni preveri + opcijski datum parameter (privzeto danes)
25-50: Preveri, ali je navada na ta datum že zabeležena → toggle (1→0 ali 0→1, ali INSERT)
52-67: Izračun streaka:
       - Vzame vse datume ko je bila navada opravljena, urejene po datumu (najnovejši prvi)
       - Začne od danes in šteje nazaj — vsak dan ki se ujema, poveča streak za 1
       - Takoj ko pride praznina (dan brez vnosa), se ustavi
68-71: UPDATE navade SET streak = ? — shrani nov streak
73-77: Vrne JSON z { success, logged, streak } — JS to uporabi za posodobitev UI
```

### `logika/mesecni_dnevnik.php`
```
GET endpoint — vrne seznam opravljenih datumov za določeno navado in mesec
Varnostni preveri: lastništvo navade
SQL: SELECT datum FROM dnevniki WHERE id_navade=? AND datum BETWEEN ? AND ? AND opravljeno=1
Odgovor: { success: true, opravljeni: ["2026-03-01", "2026-03-05", ...] }
JS ta array pretvori v številke dni: [1, 5, ...]
```

### `logika/shrani_nastavitve.php`
```
POST endpoint za posodobitev profila
1. Validacija: ime in email obvezna, email format, email ni zaseden pri drugem userju
2. Geslo (opcijsko): preveri trenutno geslo, zhasira novo
3. Slika (opcijsko): preveri MIME tip (ne samo končnico!), max 2MB, shrani v ostalo/slike/profil/
4. UPDATE uporabniki — dinamično glede na to, kaj je bilo posodobljeno
5. Posodobi $_SESSION['username']
6. Vrne JSON z { success, ime, profilna_slika? }
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
PHP partial — vključen v index.php z `include`.
Prikaže profilno sliko iz `$currentUser['profilna_slika']` ali privzeto sliko.
Gumb "Nastavitve" kliče JS funkcijo `openNastavitve()` namesto da gre na drugo stran.

### `deli_strani/dodaj_novo_navado.php`
HTML forma za dodajanje navad + JS za interaktivnost forme (dropdown dnevov, skrita polja).
Ko se pošlje za urejanje, JS pred oddajo nastavi `action="logika/uredi_navado.php"`
in zapolni skrito polje `id_navade`.

### `deli_strani/podrobnosti_navade.php`
Statični HTML z praznimi elementi (id="detailTitle", id="detailStreak"...).
JS jih zapolni z `document.getElementById(...).textContent = habit.ime_navade`.
Vključuje `deli_strani/graf_navade.php` — mesečni koledar.

### `deli_strani/graf_navade.php`
HTML mrežni koledar (7 stolpcev = 7 dni v tednu).
JS (`renderHabitChart`) ga dinamično zapolni z dnevi meseca.
Vsak klikljiv dan pokliče `toggleChartDay(datum)`.

### `deli_strani/nastavitve.php`
Modal za nastavitve profila — enak stil kot "Nova navada" modal.
Polja: profilna slika (s predogledom), ime, email, geslo (3 polja).
Forma z `enctype="multipart/form-data"` — potrebno za nalaganje datotek.
Submit je prestregnen z JS (`e.preventDefault()`) in poslan prek `fetch()` z `FormData`.

---

## 8. PODATKOVNE TABELE

```
uporabniki      → id, ime, email, hash_gesla, vloga, profilna_slika, ustvarjeno
     |
     └── navade → id, id_uporabnika, id_kategorije, ime, ponavljanje,
     |            izbrani_dnevi, del_dneva, cilj_*, datumi, streak
     |
     └── kategorije → id, id_uporabnika, ime, barva
     |
     └── (navade) → dnevniki → id, id_navade, datum, opravljeno, komentar
```

**Zakaj je dnevniki tabela logična?**
Vsaka vrstica = 1 dan × 1 navada. To je pravilen pristop (log/fact tabela).
Alternativa (datumi kot CSV v navade tabeli) bi kršila 1. normalno formo in
bi bila nemogoča za poizvedbe.

**Zakaj ni id_uporabnika v dnevniki?**
Ker prideš do njega skozi JOIN: `dnevniki → navade → id_uporabnika`.
Dodajanje bi bila denormalizacija — redundantni podatki, ki povzročajo neskladnosti.

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
| Seja brez izteka | `$_SESSION['last_activity']` + 10-minutni timeout v `seja.php` |
| Nevarna slika pri uploadu | Preverjamo MIME tip z `mime_content_type()`, ne samo končnico |
| Prevelika slika | Preverjamo `$_FILES['...']['size'] > 2MB` |

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
- beleženju navade (`zabeleznaj_navado.php`) — streak se posodobi takoj
- brisanju navade (`izbrisi_navado.php`) — navada izgine brez reload
- klikanju dni v koledarju (`zabeleznaj_navado.php` z datumom) — dan se obarva takoj
- shranjevanju nastavitev (`shrani_nastavitve.php`) — ime/slika se posodobita brez reload

**"Zakaj imaš `exit()` po `header('Location: ...')`?"**
Ker `header()` samo nastavi glavo odgovora — PHP teče naprej, dokler
ne zadene konec datoteke ali `exit()`. Brez `exit()` bi se izvajalo
vse po preusmeritvi, kar je varnostna luknja.

**"Kako preneseš podatke iz PHP v JavaScript?"**
Z `json_encode()` — PHP array pretvorimo v JSON niz in ga zapišemo direktno
v `<script>` blok: `const habits = <?php echo json_encode($navade); ?>;`
Brskalnik to prebere kot navaden JS array.

**"Kako deluješ z nalaganjem datotek (profilna slika)?"**
Forma mora imeti `enctype="multipart/form-data"`. PHP datoteko dobi v `$_FILES`.
Preverim MIME tip z `mime_content_type()` (ne samo končnice — to bi bilo nevarno),
preverim velikost, nato `move_uploaded_file()` prestavi datoteko iz začasne lokacije.

**"Zakaj uporabljaš FormData namesto URLSearchParams za nastavitve?"**
`URLSearchParams` podpira samo besedilne vrednosti.
`FormData` podpira tudi binarne datoteke (slike), kar je potrebno za upload.

**"Kaj je streak in kako ga izračunaš?"**
Streak = število zaporednih dni, ko je bila navada opravljena.
Izračun: vzamem vse datume ko je bila navada opravljena, urejene od novejšega.
Začnem od danes in štejem nazaj — vsak dan ki se ujema z pričakovanim, +1.
Ko pride praznina, se ustavim.

**"Zakaj imaš v dnevniki tabeli en zapis na dan na navado?"**
To je standardni pristop (log/fact tabela). Vsaka vrstica = 1 dan × 1 navada.
Alternativa (datumi kot CSV) bi kršila normalizacijo in otežila poizvedbe kot
"koliko dni v marcu je bila navada opravljena".

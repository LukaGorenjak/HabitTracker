# Habit Flow – Tracker navad

Spletna aplikacija za sledenje dnevnim navadam, zgrajena s PHP, MySQL in JavaScript.
Projekt je razvit kot šolska naloga z uporabo XAMPP lokalnega strežnika.

---

## Funkcionalnosti

- Registracija in prijava uporabnikov
- Dodajanje, urejanje in brisanje navad z emoji ikono
- Filtriranje navad po delu dneva (zjutraj, popoldne, zvečer) in kategoriji
- Iskanje navad po imenu
- Beleženje opravljenih navad po dnevih (toggle)
- Streak sledenje (zaporedje opravljenih dni) s ciljem
- Progress bar na kartici navade glede na streak cilj
- Mesečni grafikon opravljenih navad (klik na dan za beleženje)
- Chart.js doughnut grafikon napredka do streak cilja
- Statistika: skupno opravljenih, najdaljši streak, število aktivnih navad
- Kategorije z barvami (dodajanje iz stranske vrstice)
- Pomodoro timer (fokus / kratki odmor / dolgi odmor) s sledenjem sej
- Urejanje profila: uporabniško ime, e-pošta, geslo, profilna slika
- Admin panel: pregled statistik, upravljanje uporabnikov in vlog
- Toast obvestila za povratne informacije (brez alert())
- Seja z avtomatičnim potekom po 10 minutah neaktivnosti
- CSRF zaščita na vseh obrazcih in AJAX zahtevkih

---

## Tehnologije

| Plast       | Tehnologija                        |
|-------------|------------------------------------|
| Backend     | PHP 8 + PDO (prepared statements)  |
| Baza        | MySQL 8 (XAMPP)                    |
| Frontend    | HTML5, CSS3, Vanilla JavaScript    |
| Grafi       | Chart.js 4.4                       |
| Strežnik    | Apache (XAMPP)                     |

---

## Namestitev

### Zahteve
- XAMPP (Apache + MySQL + PHP 8)
- Brskalnik

### Koraki

1. Kloniraj ali skopiraj projekt v mapo `C:\xampp\htdocs\HabitTracker`

2. Zaženi XAMPP – vklopi **Apache** in **MySQL**

3. Odpri **phpMyAdmin** → ustvari bazo `habit_tracker`

4. Uvozi SQL datoteko:
   ```
   phpMyAdmin → habit_tracker → Uvozi → habit_tracker.sql
   ```

5. Dodaj stolpec `emoji` v tabelo `navade` (če ni v SQL datoteki):
   ```sql
   ALTER TABLE navade ADD COLUMN emoji VARCHAR(10) DEFAULT NULL;
   ```

6. Odpri aplikacijo v brskalniku:
   ```
   http://localhost/HabitTracker/
   ```

---

## Struktura projekta

```
HabitTracker/
├── index.php                        # Vstopna točka (landing + dashboard)
├── admin.php                        # Admin panel
├── pomodoro.php                     # Pomodoro timer
├── profil.php                       # Urejanje profila
│
├── avtentikacija/
│   ├── prijava.php                  # Prijava
│   ├── registracija.php             # Registracija
│   └── odjava.php                   # Odjava
│
├── konfiguracija/
│   ├── db.php                       # PDO povezava z bazo
│   └── seja.php                     # Upravljanje seje (timeout, CSRF token)
│
├── logika/
│   ├── shrani_navado.php            # Dodaj novo navado
│   ├── uredi_navado.php             # Uredi obstoječo navado
│   ├── izbrisi_navado.php           # Izbriši navado (AJAX/JSON)
│   ├── zabeleznaj_navado.php        # Označi navado kot opravljeno (AJAX/JSON)
│   ├── mesecni_dnevnik.php          # Podatki za mesečni grafikon
│   ├── shrani_nastavitve.php        # Shrani nastavitve profila (AJAX/JSON)
│   ├── dodaj_kategorijo.php         # Dodaj novo kategorijo (AJAX/JSON)
│   ├── admin_vloga.php              # Spremeni vlogo uporabnika (AJAX/JSON)
│   └── admin_izbrisi_uporabnika.php # Izbriši uporabnika (AJAX/JSON)
│
├── deli_strani/
│   ├── navigacija.php               # Stranska vrstica (sidebar) + toast JS
│   ├── dodaj_novo_navado.php        # Modal za dodajanje/urejanje navade
│   ├── podrobnosti_navade.php       # Panel z detajli navade
│   ├── statistika.php               # Modal s statistiko
│   └── nastavitve.php               # Modal za nastavitve profila
│
└── ostalo/
    ├── style.css                    # Celoten CSS projekta
    └── slike/                       # Profilne fotografije
```

---

## Podatkovna baza

### Tabele

**`uporabniki`**
| Stolpec          | Tip          | Opis                        |
|------------------|--------------|-----------------------------|
| id_uporabnika    | INT PK AI    | Primarni ključ              |
| uporabnisko_ime  | VARCHAR(50)  | Prikazno ime                |
| email            | VARCHAR(100) | E-poštni naslov (unikaten)  |
| hash_gesla       | VARCHAR(255) | Bcrypt hash gesla           |
| profilna_slika   | VARCHAR(255) | Pot do slike (nullable)     |
| vloga            | ENUM         | `uporabnik` ali `admin`     |
| ustvarjeno       | TIMESTAMP    | Datum registracije          |

**`navade`**
| Stolpec        | Tip          | Opis                                      |
|----------------|--------------|-------------------------------------------|
| id_navade      | INT PK AI    | Primarni ključ                            |
| id_uporabnika  | INT FK       | Lastnik navade                            |
| id_kategorije  | INT FK       | Kategorija navade                         |
| ime_navade     | VARCHAR(100) | Ime navade                                |
| ponavljanje    | VARCHAR(20)  | `dnevno`, `tedensko`, `mesecno`           |
| izbrani_dnevi  | VARCHAR(100) | Vejicami ločeni dnevi ali `vsak_dan`      |
| del_dneva      | VARCHAR(50)  | `zjutraj`, `popoldne`, `zvecer` (nullable)|
| cilj_kolicina  | INT          | Količina cilja (npr. 30)                  |
| cilj_enota     | VARCHAR(20)  | `krat`, `litrov`, `minut`, ...            |
| cilj_obdobje   | VARCHAR(20)  | `na_dan`, `na_teden`, `na_mesec`          |
| cilj_dni       | INT          | Streak cilj v dnevih (nullable)           |
| streak         | INT          | Trenutni streak                           |
| opis           | TEXT         | Opis navade (nullable)                    |
| emoji          | VARCHAR(10)  | Emoji ikona navade (nullable)             |
| ustvarjeno     | TIMESTAMP    | Datum dodajanja                           |

**`kategorije`**
| Stolpec       | Tip         | Opis                    |
|---------------|-------------|-------------------------|
| id_kategorije | INT PK AI   | Primarni ključ          |
| id_uporabnika | INT FK      | Lastnik kategorije      |
| ime           | VARCHAR(50) | Ime kategorije          |
| barva         | VARCHAR(7)  | HEX barva (npr. #4a9d6f)|

**`dnevniki`**
| Stolpec     | Tip       | Opis                        |
|-------------|-----------|-----------------------------|
| id_dnevnika | INT PK AI | Primarni ključ              |
| id_navade   | INT FK    | Katera navada               |
| datum       | DATE      | Datum beleženja             |
| opravljeno  | TINYINT   | `1` = opravljeno, `0` = ni  |

---

## Varnost

- Gesla so shranjena kot **bcrypt hash** (`password_hash` / `password_verify`)
- Vse SQL poizvedbe uporabljajo **PDO prepared statements** (zaščita pred SQL injection)
- **CSRF zaščita** na vseh obrazcih in AJAX zahtevkih (`hash_equals` na `$_SESSION['csrf_token']`)
- Vsaka logika preverja `$_SESSION['user_id']` pred dostopom do podatkov
- Vse operacije preverijo lastništvo z `WHERE id_uporabnika = ?`
- Admin funkcije preverijo `$_SESSION['role'] === 'admin'`
- Seja poteče po **10 minutah** neaktivnosti

---

## Admin dostop

Admin uporabnik ima dostop do `/admin.php` kjer lahko:
- Pregleda statistike (število uporabnikov, navad, opravljenih vnosov)
- Pregleda seznam vseh uporabnikov z datumom registracije
- Spremeni vlogo uporabnika (`uporabnik` ↔ `admin`)
- Izbriše uporabnika (kaskadno briše vse njegove navade in dnevnike)

Vlogo `admin` nastavi ročno v bazi ali prek obstoječega admin računa.

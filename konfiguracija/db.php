<?php
// Podatki za povezavo z MySQL strežnikom
$host = '127.0.0.1';       // naslov strežnika (localhost)
$db   = 'habit_tracker';   // ime podatkovne baze
$user = 'root';            // uporabniško ime za MySQL
$pass = ''; // XAMPP privzeto nima gesla

$charset = 'utf8mb4'; // nabor znakov – podpira slovensko abecedo in emoji

// DSN (Data Source Name) – niz, ki pove PDO-ju, kako se povezati
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Nastavitve za PDO objekt
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // ob napaki vrže izjemo (ne molče napake)
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // fetch() vrne asociativni niz (ime_stolpca => vrednost)
    PDO::ATTR_EMULATE_PREPARES   => false,                    // prave PDO prepared statements (varnejše, ne PHP emulacija)
];

try {
    // Ustvari novo PDO povezavo z zgornjimi nastavitvami
     $pdo = new PDO($dsn, $user, $pass, $options);
     // echo "Povezava uspela!"; // Odkomentiraj za test
} catch (\PDOException $e) {
    // Če povezava ne uspe, vrže isto napako naprej z enako kodo
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>

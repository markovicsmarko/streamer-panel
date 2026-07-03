<?php
define('ALLOW_ACCESS', true);
require_once 'db.php';
require_once 'functions.php';

// Only admin or superadmin can run this
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'sadmin'])) {
    die("Hiba: Nincs jogosultságod a szkript futtatásához!");
}

echo "<h2>" . htmlspecialchars(ucfirst(TWITCH_USERNAME)) . "Tracker Twitch Bot Diagnosztika</h2>";
echo "<hr>";

// 1. Check background process (locking)
$lock_file = @fopen(__DIR__ . '/twitch_bot.lock', 'c');
if ($lock_file) {
    if (!flock($lock_file, LOCK_EX | LOCK_NB)) {
        echo "<b style='color: #10b981;'>[OK]</b> A Twitch bot jelenleg FUT a háttérben (a zárolási fájl aktív).<br>";
    } else {
        echo "<b style='color: #ef4444;'>[HIBA]</b> A Twitch bot jelenleg NEM fut a háttérben (a zárolási fájl szabad).<br>";
        flock($lock_file, LOCK_UN);
    }
    fclose($lock_file);
} else {
    echo "<b style='color: #ef4444;'>[HIBA]</b> Nem sikerült megnyitni a zárolási fájlt.<br>";
}

// 2. Check last heartbeat in the database
$bot_last_ping = (int)get_cache_value($pdo, 'twitch_bot_last_ping');
if ($bot_last_ping > 0) {
    $diff = time() - $bot_last_ping;
    echo "Legutóbbi életjel az adatbázisban: <b>" . date('Y-m-d H:i:s', $bot_last_ping) . "</b> ({$diff} másodperce).<br>";
} else {
    echo "Még nincs életjel bejegyzés az adatbázisban.<br>";
}

// 3. Test Twitch connection from the server
$server = 'ssl://irc.chat.twitch.tv';
$port = 443;
echo "Twitch IRC Szerver kapcsolódás tesztelése ({$server}:{$port})...<br>";

$socket = @fsockopen($server, $port, $errno, $errstr, 10);
if ($socket) {
    echo "<b style='color: #10b981;'>[OK]</b> A szerver sikeresen tud csatlakozni a Twitch IRC szerveréhez!<br>";
    fclose($socket);
} else {
    echo "<b style='color: #ef4444;'>[HIBA]</b> A szerver NEM tud csatlakozni a Twitchhez.<br>";
    echo "Hibaüzenet: {$errstr} (kód: {$errno})<br>";
    echo "Megjegyzés: A tárhelyszolgáltatód valószínűleg blokkolja a kifelé menő kapcsolatokat ezen a porton.<br>";
}

// 4. PHP path diagnostics
echo "<br><b>Szerver információk a Cron beállításhoz:</b><br>";
echo "Abszolút elérési út a bot fájlhoz: <code>" . htmlspecialchars(__DIR__ . '/twitch_bot.php') . "</code><br>";
echo "Ajánlott PHP parancs (cPanelben): <code>php -q " . htmlspecialchars(__DIR__ . '/twitch_bot.php') . "</code><br>";
echo "Aktuális PHP binary elérési útja: <code>" . htmlspecialchars(PHP_BINARY) . "</code><br>";
?>

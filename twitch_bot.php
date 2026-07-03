<?php
// The bot can only be run directly from the command line (CLI)
if (php_sapi_name() !== 'cli') {
    die("Hiba: Ez a szkript csak parancssorból futtatható.");
}

// Prevent duplicate executions with file locking (flock)
$lock_file = fopen(__DIR__ . '/twitch_bot.lock', 'c');
if (!flock($lock_file, LOCK_EX | LOCK_NB)) {
    echo "A Twitch bot már fut egy másik folyamatban. Kilépés.\n";
    exit(0);
}

// Load configuration and database connection
define('ALLOW_ACCESS', true);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Check Twitch settings
if (!defined('TWITCH_USERNAME') || empty(TWITCH_USERNAME)) {
    die("Twitch felhasználónév nincs konfigurálva a config.php fájlban.\n");
}

// Ensure that the chat log table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS twitch_chat_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
} catch (PDOException $e) {
    echo "Hiba a tábla létrehozásakor: " . $e->getMessage() . "\n";
}

$channel = '#' . strtolower(TWITCH_USERNAME);
$nickname = 'justinfan' . rand(10000, 99999); // Anonymous login name

// List of bots we don't want to log (lowercase)
$blacklist = [
    'streamelements',
    'nightbot',
    'wizebot',
    'moobot',
    strtolower(TWITCH_USERNAME) // Do not save own messages either
];

// Max execution time: 4 minutes 30 seconds
// The cron restarts it every 5 minutes, so if the bot reaches this, it shuts down cleanly
// and restarts at the next cron run – uninterrupted continuity!
$max_run_time = 270; // in seconds
$start_time = time();

// Connect to the Twitch IRC server via SSL
$server = 'ssl://irc.chat.twitch.tv';
$port = 443;

echo "Kapcsolódás a Twitch chathez ({$server}:{$port}) anonim módon: {$nickname}...\n";
$socket = @fsockopen($server, $port, $errno, $errstr, 30);

if (!$socket) {
    die("Kapcsolódási hiba: {$errstr} ({$errno})\n");
}

// CRITICAL: 20 second read timeout on the socket.
// Without this, fgets() blocks waiting for new messages forever,
// and cPanel/CloudLinux kills the entire PHP process in the meantime!
stream_set_timeout($socket, 20);

// Send login messages
fwrite($socket, "PASS guest\r\n");
fwrite($socket, "NICK {$nickname}\r\n");
fwrite($socket, "JOIN {$channel}\r\n");

echo "Sikeresen csatlakozva a {$channel} csatornához!\n";

$last_ping_update = 0;
$last_irc_ping_received = time(); // Time when the last server PING was received

// Continuous read loop
while (!feof($socket)) {

    // Check max execution time – if expired, shut down cleanly
    if (time() - $start_time >= $max_run_time) {
        echo "Max futási idő (" . $max_run_time . "s) elérve, leállás (cron újraindítja).\n";
        break;
    }

    // Send heartbeat to the database every 30 seconds
    if (time() - $last_ping_update >= 30) {
        set_cache_value($pdo, 'twitch_bot_last_ping', time());
        $last_ping_update = time();
    }

    $line = fgets($socket, 1024);

    // If false is returned, check why
    if ($line === false) {
        $meta = stream_get_meta_data($socket);
        if ($meta['timed_out']) {
            // Normal timeout (20s passed without message) – this is completely OK
            // However, if no PING has arrived from the server for 5+ minutes, the connection is likely dead
            if (time() - $last_irc_ping_received > 300) {
                echo "Twitch szerver nem küldött PING-et 5 perce, kapcsolat elveszett.\n";
                break;
            }
            continue; // Continue, everything is fine
        }
        // Real EOF – the server closed the connection
        break;
    }

    $line = trim($line);
    if (empty($line)) {
        continue;
    }

    // PING-PONG handling to keep connection alive
    if (substr($line, 0, 4) === 'PING') {
        fwrite($socket, "PONG " . substr($line, 5) . "\r\n");
        $last_irc_ping_received = time();
        continue;
    }

    // Process PRIVMSG (chat message)
    // Pattern: :nick!nick@nick.tmi.twitch.tv PRIVMSG #channel :message text
    if (preg_match('/^:([^!]+)!.*?\s+PRIVMSG\s+#[^\s]+\s+:(.*)$/s', $line, $matches)) {
        $user = strtolower(trim($matches[1]));
        $msg = trim($matches[2]);

        // If the chatter is not on the blacklist, save it
        if (!in_array($user, $blacklist)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO twitch_chat_logs (username, message) VALUES (?, ?)");
                $stmt->execute([$user, $msg]);
            } catch (PDOException $e) {
                echo "Adatbázis mentési hiba: " . $e->getMessage() . "\n";
            }
        }
    }
}

fclose($socket);
flock($lock_file, LOCK_UN);
fclose($lock_file);
echo "Kapcsolat lezárva.\n";

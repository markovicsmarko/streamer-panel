<?php
/**
 * StreamerPanel - Interactive Installer
 * Supports Hungarian and English.
 */

session_start();

// 1. Language Detection & Selection
$lang = 'hu'; // default
if (isset($_GET['lang']) && in_array($_GET['lang'], ['hu', 'en'])) {
    $lang = $_GET['lang'];
    $_SESSION['install_lang'] = $lang;
} elseif (isset($_SESSION['install_lang'])) {
    $lang = $_SESSION['install_lang'];
} else {
    $accept_lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (stripos($accept_lang, 'hu') === false) {
        $lang = 'en';
    }
}

// Translation dictionary
$texts = [
    'hu' => [
        'title' => 'StreamerPanel - Telepítő',
        'subtitle' => 'Játékszerver Követő & Közösségi Portál Beállítása',
        'step_req' => 'Rendszer',
        'step_db' => 'Adatbázis',
        'step_config' => 'Beállítások',
        'step_install' => 'Telepítés',
        'step_done' => 'Kész',
        'next' => 'Tovább',
        'back' => 'Vissza',
        'retry' => 'Újra',
        'btn_test' => 'Kapcsolat tesztelése',
        'btn_install' => 'Telepítés indítása',
        'btn_go_site' => 'Ugrás a Főoldalra',
        'status_ok' => 'Megfelelő',
        'status_fail' => 'Nem megfelelő',
        'req_php' => 'PHP Verzió (>= 7.4)',
        'req_pdo' => 'PDO MySQL kiterjesztés',
        'req_curl' => 'cURL kiterjesztés',
        'req_write' => 'Írási jogosultság a mappára',
        'req_write_desc' => 'Ha nem írható, a config.php fájlt manuálisan kell letöltened és feltöltened.',
        'db_title' => 'Adatbázis Kapcsolódási Adatok',
        'db_desc' => 'Add meg a MySQL adatbázisod adatait. A telepítő megkísérel csatlakozni és létrehozni a táblákat.',
        'db_host' => 'Adatbázis Kiszolgáló (Host)',
        'db_name' => 'Adatbázis Neve',
        'db_user' => 'Adatbázis Felhasználó',
        'db_pass' => 'Adatbázis Jelszó',
        'db_success' => 'Sikeres kapcsolat az adatbázissal!',
        'db_error' => 'Kapcsolódási hiba: ',
        'config_title' => 'Rendszer & API Beállítások',
        'config_desc' => 'Add meg a weboldal működéséhez és a külső integrációkhoz szükséges beállításokat.',
        'conf_steam_key' => 'Steam API Kulcs (STEAM_API_KEY)',
        'conf_steam_key_desc' => 'A Steam bejelentkezéshez szükséges. Beszerezhető: steamcommunity.com/dev/apikey',
        'conf_admin_id' => 'Fő Adminisztrátor Steam ID-ja (ADMIN_STEAM_ID)',
        'conf_admin_id_desc' => 'A 17 jegyű SteamID64 számod (pl. 76561198...). Ezzel a Steam ID-val bejelentkező automatikusan superadmin lesz.',
        'conf_admin_name' => 'Fő Adminisztrátor Felhasználóneve',
        'conf_admin_name_desc' => 'A felhasználónév, amivel az adminisztrátor bekerül a felhasználói adatbázisba.',
        'conf_yt_key' => 'YouTube API Kulcs',
        'conf_yt_key_desc' => 'A legújabb videó automatikus lekéréséhez a YouTube-ról.',
        'conf_yt_channel' => 'YouTube Csatorna ID (Channel ID)',
        'conf_yt_channel_desc' => 'A YouTube csatornád egyedi azonosítója (pl. UC...).',
        'conf_twitch_id' => 'Twitch Client ID',
        'conf_twitch_secret' => 'Twitch Client Secret',
        'conf_twitch_secret_desc' => 'Twitch API-k használatához. Beszerezhető: dev.twitch.tv',
        'conf_twitch_user' => 'Twitch Csatorna (Felhasználónév)',
        'conf_twitch_user_desc' => 'A Twitch csatornád neve csupa kisbetűvel (pl. yourname).',
        'conf_discord_guild' => 'Discord Szerver ID (DISCORD_GUILD_ID)',
        'conf_discord_guild_desc' => 'A Discord szervered egyedi azonosítója a ranglistához (engau.ge).',
        'conf_discord_bot_notice' => 'FONTOS: Ahhoz, hogy a Discord aktivitás ranglista működjön, be kell hívnod az <a href="https://engau.ge/" target="_blank" class="text-blue-400 hover:underline">engau.ge botot</a> a Discord szerveredre!',
        'conf_streamer_discord' => 'Streamer Discord ID-ja (STREAMER_DISCORD_ID)',
        'conf_streamer_discord_desc' => 'A Discord ID azonosítód, amivel a rendszer kiszűr a közösségi ranglistáról (ne magadat lásd az első helyen).',
        'conf_cron_key' => 'Cron Biztonsági Kulcs',
        'conf_cron_key_desc' => 'A cron.php weben keresztüli meghívásakor használt titkos kulcs a jogosulatlan futtatások ellen.',
        'conf_panels_title' => 'Kezdőlapi Panelek Láthatósága',
        'conf_panels_desc' => 'Válaszd ki, mely szekciók jelenjenek meg alapértelmezetten a főoldalon (ezeket később az Admin Panelben is módosíthatod).',
        'conf_panel_social' => 'Közösségi Linkek (Linktree)',
        'conf_panel_twitch' => 'Twitch Élő Stream lejátszó',
        'conf_panel_youtube' => 'Legújabb YouTube Videó lejátszó',
        'conf_panel_servers' => 'Játékszerver Lista',
        'conf_panel_activity' => 'Discord Aktivitás Ranglista',
        'install_progress' => 'A telepítés folyamatban van...',
        'install_success' => 'A StreamerPanel telepítése sikeresen befejeződött!',
        'install_delete_warn' => 'FIGYELEM: A biztonság érdekében azonnal TÖRÖLD az install.php fájlt a szerverről!',
        'config_write_err' => 'Hiba: Nem sikerült a config.php fájlt automatikusan létrehozni jogosultsági okok miatt.',
        'config_write_err_desc' => 'Másold ki a lenti kódot, mentsd el config.php néven, és töltsd fel a weboldal gyökérkönyvtárába, majd kattintson a Tovább gombra!',
        'config_download' => 'config.php Letöltése',
        'err_empty' => 'Minden kötelező mező kitöltése kötelező!',
        'err_steam_id' => 'A Steam ID-nak 17 jegyű számnak kell lennie!',
        'install_tables_ok' => 'Adatbázis táblák sikeresen létrehozva.',
        'install_settings_ok' => 'Alapértelmezett rendszerbeállítások importálva.',
        'install_admin_ok' => 'Fő adminisztrátor létrehozva a felhasználók között.',
        'install_links_ok' => 'Alapértelmezett navigációs és közösségi linkek beszúrva.',
        'cron_info_title' => 'Rendszer Cron Jobok Beállítása',
        'cron_info_desc' => 'A játékszerverek státuszának követéséhez és a ranglisták frissítéséhez állítsd be az alábbi parancsokat a tárhelyeden (pl. cPanel Cron Jobs) 5 perces futási gyakorisággal:',
        'cron_cmd_cron' => 'Szerverek & API frissítés (cron.php):',
        'cron_cmd_bot' => 'Twitch Chat Bot indítás (twitch_bot.php):',
        'cron_discord_bot_remind' => 'Győződj meg róla, hogy meghívtad az <a href="https://engau.ge/" target="_blank" class="text-blue-400 hover:underline">engau.ge botot</a> a Discord szerveredre a ranglista szekció működéséhez.'
    ],
    'en' => [
        'title' => 'StreamerPanel - Installer',
        'subtitle' => 'Game Server Tracker & Community Portal Setup',
        'step_req' => 'System',
        'step_db' => 'Database',
        'step_config' => 'Settings',
        'step_install' => 'Installation',
        'step_done' => 'Done',
        'next' => 'Next',
        'back' => 'Back',
        'retry' => 'Retry',
        'btn_test' => 'Test Connection',
        'btn_install' => 'Start Installation',
        'btn_go_site' => 'Go to Homepage',
        'status_ok' => 'Passed',
        'status_fail' => 'Failed',
        'req_php' => 'PHP Version (>= 7.4)',
        'req_pdo' => 'PDO MySQL Extension',
        'req_curl' => 'cURL Extension',
        'req_write' => 'Folder Write Permission',
        'req_write_desc' => 'If not writable, you will need to manually download and upload config.php.',
        'db_title' => 'Database Connection Details',
        'db_desc' => 'Enter your MySQL database details. The installer will attempt to connect and create the tables.',
        'db_host' => 'Database Server (Host)',
        'db_name' => 'Database Name',
        'db_user' => 'Database User',
        'db_pass' => 'Database Password',
        'db_success' => 'Successfully connected to the database!',
        'db_error' => 'Connection error: ',
        'config_title' => 'System & API Settings',
        'config_desc' => 'Provide settings for web functionalities and external API integrations.',
        'conf_steam_key' => 'Steam API Key',
        'conf_steam_key_desc' => 'Required for Steam login. Get one from: steamcommunity.com/dev/apikey',
        'conf_admin_id' => 'Main Admin Steam ID (ADMIN_STEAM_ID)',
        'conf_admin_id_desc' => 'Your 17-digit SteamID64 (e.g. 76561198...). Anyone logging in with this ID automatically becomes superadmin.',
        'conf_admin_name' => 'Main Admin Username',
        'conf_admin_name_desc' => 'The display name used for the administrator in the database.',
        'conf_yt_key' => 'YouTube API Key',
        'conf_yt_key_desc' => 'Used for automatically caching the latest YouTube video.',
        'conf_yt_channel' => 'YouTube Channel ID',
        'conf_yt_channel_desc' => 'The unique identifier of your YouTube channel (e.g. UC...).',
        'conf_twitch_id' => 'Twitch Client ID',
        'conf_twitch_secret' => 'Twitch Client Secret',
        'conf_twitch_secret_desc' => 'Used to interact with Twitch APIs. Get one from: dev.twitch.tv',
        'conf_twitch_user' => 'Twitch Channel (Username)',
        'conf_twitch_user_desc' => 'Your Twitch channel name in lowercase (e.g. yourname).',
        'conf_discord_guild' => 'Discord Server ID (DISCORD_GUILD_ID)',
        'conf_discord_guild_desc' => 'The unique identifier of your Discord server for the leaderboard (engau.ge).',
        'conf_discord_bot_notice' => 'IMPORTANT: For the Discord activity leaderboard to work, you must invite the <a href="https://engau.ge/" target="_blank" class="text-blue-400 hover:underline">engau.ge bot</a> to your Discord server!',
        'conf_streamer_discord' => 'Streamer Discord ID (STREAMER_DISCORD_ID)',
        'conf_streamer_discord_desc' => 'Your Discord ID to filter you out from the community leaderboard (so you don\'t appear at first place).',
        'conf_cron_key' => 'Cron Security Key',
        'conf_cron_key_desc' => 'Secret token used when calling cron.php via web browser to prevent unauthorized runs.',
        'conf_panels_title' => 'Homepage Panel Visibility',
        'conf_panels_desc' => 'Select which sections to display on the homepage by default (you can change this later in the Admin Panel).',
        'conf_panel_social' => 'Social Links (Linktree)',
        'conf_panel_twitch' => 'Twitch Live Stream Embed',
        'conf_panel_youtube' => 'Latest YouTube Video Embed',
        'conf_panel_servers' => 'Game Server List',
        'conf_panel_activity' => 'Discord Activity Leaderboard',
        'install_progress' => 'Installation in progress...',
        'install_success' => 'StreamerPanel installation completed successfully!',
        'install_delete_warn' => 'WARNING: For security reasons, DELETE the install.php file from your server immediately!',
        'config_write_err' => 'Error: Failed to create config.php automatically due to permission restrictions.',
        'config_write_err_desc' => 'Copy the code below, save it as config.php, upload it to your website root directory, and click Next!',
        'config_download' => 'Download config.php',
        'err_empty' => 'All required fields must be filled!',
        'err_steam_id' => 'Steam ID must be a 17-digit number!',
        'install_tables_ok' => 'Database tables created successfully.',
        'install_settings_ok' => 'Default system settings imported.',
        'install_admin_ok' => 'Main admin created in user list.',
        'install_links_ok' => 'Default navigation and social links inserted.',
        'cron_info_title' => 'System Cron Jobs Settings',
        'cron_info_desc' => 'To query game servers and update activity leaderboards, configure the following cron commands on your server (e.g. cPanel Cron Jobs) to run every 5 minutes:',
        'cron_cmd_cron' => 'Server & API updates (cron.php):',
        'cron_cmd_bot' => 'Twitch Chat Bot launch (twitch_bot.php):',
        'cron_discord_bot_remind' => 'Make sure you invited the <a href="https://engau.ge/" target="_blank" class="text-blue-400 hover:underline">engau.ge bot</a> to your Discord server for the leaderboard section to function.'
    ]
];

// Helper translate function
function __t($key) {
    global $texts, $lang;
    return $texts[$lang][$key] ?? $key;
}

// 2. AJAX Database Connection Tester
if (isset($_POST['action']) && $_POST['action'] === 'test_db') {
    header('Content-Type: application/json');
    $host = $_POST['host'] ?? '';
    $name = $_POST['name'] ?? '';
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if (empty($host) || empty($name) || empty($user)) {
        echo json_encode(['success' => false, 'message' => __t('err_empty')]);
        exit;
    }

    try {
        $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo json_encode(['success' => true, 'message' => __t('db_success')]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => __t('db_error') . $e->getMessage()]);
    }
    exit;
}

// 3. Action handling for AJAX / POST installer execution
$errors = [];
$install_logs = [];
$generated_config_code = '';
$config_write_success = true;

// Determine current step
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// If we are at Step 1, run requirement checks
$req_php_ok = version_compare(PHP_VERSION, '7.4.0', '>=');
$req_pdo_ok = class_exists('PDO');
$req_curl_ok = function_exists('curl_init');
$req_write_ok = is_writable(__DIR__);
$req_all_ok = $req_php_ok && $req_pdo_ok && $req_curl_ok;

if ($step === 1 && isset($_POST['submit_req'])) {
    if ($req_all_ok) {
        header('Location: install.php?step=2');
        exit;
    }
}

// Process Database Setup and Connection Check
if ($step === 2 && isset($_POST['submit_db'])) {
    $db_host = trim($_POST['db_host'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = $_POST['db_pass'] ?? '';

    if (empty($db_host) || empty($db_name) || empty($db_user)) {
        $errors[] = __t('err_empty');
    } else {
        try {
            $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $_SESSION['db_host'] = $db_host;
            $_SESSION['db_name'] = $db_name;
            $_SESSION['db_user'] = $db_user;
            $_SESSION['db_pass'] = $db_pass;
            
            header('Location: install.php?step=3');
            exit;
        } catch (PDOException $e) {
            $errors[] = __t('db_error') . $e->getMessage();
        }
    }
}

// Process Config details
if ($step === 3 && isset($_POST['submit_config'])) {
    $steam_key = trim($_POST['steam_key'] ?? '');
    $admin_id = trim($_POST['admin_id'] ?? '');
    $admin_name = trim($_POST['admin_name'] ?? '');
    $yt_key = trim($_POST['yt_key'] ?? '');
    $yt_channel = trim($_POST['yt_channel'] ?? '');
    $twitch_id = trim($_POST['twitch_id'] ?? '');
    $twitch_secret = trim($_POST['twitch_secret'] ?? '');
    $twitch_user = trim($_POST['twitch_user'] ?? '');
    $discord_guild = trim($_POST['discord_guild'] ?? '');
    $streamer_discord = trim($_POST['streamer_discord'] ?? '');
    $cron_key = trim($_POST['cron_key'] ?? '');

    // Form inputs visibility settings
    $show_social = isset($_POST['show_social']) ? '1' : '0';
    $show_twitch = isset($_POST['show_twitch']) ? '1' : '0';
    $show_youtube = isset($_POST['show_youtube']) ? '1' : '0';
    $show_servers = isset($_POST['show_servers']) ? '1' : '0';
    $show_activity = isset($_POST['show_activity']) ? '1' : '0';

    if (empty($steam_key) || empty($admin_id) || empty($admin_name) || empty($cron_key)) {
        $errors[] = __t('err_empty');
    } elseif (!preg_match('/^\d{17}$/', $admin_id)) {
        $errors[] = __t('err_steam_id');
    } else {
        $_SESSION['steam_key'] = $steam_key;
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['admin_name'] = $admin_name;
        $_SESSION['yt_key'] = $yt_key;
        $_SESSION['yt_channel'] = $yt_channel;
        $_SESSION['twitch_id'] = $twitch_id;
        $_SESSION['twitch_secret'] = $twitch_secret;
        $_SESSION['twitch_user'] = $twitch_user;
        $_SESSION['discord_guild'] = $discord_guild;
        $_SESSION['streamer_discord'] = $streamer_discord;
        $_SESSION['cron_key'] = $cron_key;

        // Visibility array
        $_SESSION['vis_social'] = $show_social;
        $_SESSION['vis_twitch'] = $show_twitch;
        $_SESSION['vis_youtube'] = $show_youtube;
        $_SESSION['vis_servers'] = $show_servers;
        $_SESSION['vis_activity'] = $show_activity;

        header('Location: install.php?step=4');
        exit;
    }
}

// Handle Direct Config Download
if (isset($_GET['action']) && $_GET['action'] === 'download_config') {
    if (!isset($_SESSION['db_host'])) {
        die('No config session active.');
    }
    $code = "<?php\n"
          . "// --- STREAMERPANEL CENTRAL CONFIGURATION FILE ---\n\n"
          . "if (basename(\$_SERVER['SCRIPT_FILENAME']) === 'config.php') {\n"
          . "    header('HTTP/1.0 403 Forbidden');\n"
          . "    exit('Access Denied');\n"
          . "}\n\n"
          . "define('DB_HOST', '" . addslashes($_SESSION['db_host']) . "');\n"
          . "define('DB_NAME', '" . addslashes($_SESSION['db_name']) . "');\n"
          . "define('DB_USER', '" . addslashes($_SESSION['db_user']) . "');\n"
          . "define('DB_PASS', '" . addslashes($_SESSION['db_pass']) . "');\n\n"
          . "define('STEAM_API_KEY', '" . addslashes($_SESSION['steam_key']) . "');\n"
          . "define('ADMIN_STEAM_ID', '" . addslashes($_SESSION['admin_id']) . "');\n\n"
          . "define('YT_API_KEY', '" . addslashes($_SESSION['yt_key']) . "');\n"
          . "define('YT_CHANNEL_ID', '" . addslashes($_SESSION['yt_channel']) . "');\n\n"
          . "define('TWITCH_CLIENT_ID', '" . addslashes($_SESSION['twitch_id']) . "');\n"
          . "define('TWITCH_CLIENT_SECRET', '" . addslashes($_SESSION['twitch_secret']) . "');\n"
          . "define('TWITCH_USERNAME', '" . addslashes($_SESSION['twitch_user']) . "');\n\n"
          . "define('DISCORD_GUILD_ID', '" . addslashes($_SESSION['discord_guild']) . "');\n"
          . "define('STREAMER_DISCORD_ID', '" . addslashes($_SESSION['streamer_discord']) . "');\n\n"
          . "define('CRON_KEY', '" . addslashes($_SESSION['cron_key']) . "');\n"
          . "?>";
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="config.php"');
    echo $code;
    exit;
}

// Perform Installation Execution
if ($step === 4) {
    // Generate config code
    $generated_config_code = "<?php\n"
                           . "// --- STREAMERPANEL CENTRAL CONFIGURATION FILE ---\n\n"
                           . "if (basename(\$_SERVER['SCRIPT_FILENAME']) === 'config.php') {\n"
                           . "    header('HTTP/1.0 403 Forbidden');\n"
                           . "    exit('Access Denied');\n"
                           . "}\n\n"
                           . "define('DB_HOST', '" . addslashes($_SESSION['db_host']) . "');\n"
                           . "define('DB_NAME', '" . addslashes($_SESSION['db_name']) . "');\n"
                           . "define('DB_USER', '" . addslashes($_SESSION['db_user']) . "');\n"
                           . "define('DB_PASS', '" . addslashes($_SESSION['db_pass']) . "');\n\n"
                           . "define('STEAM_API_KEY', '" . addslashes($_SESSION['steam_key']) . "');\n"
                           . "define('ADMIN_STEAM_ID', '" . addslashes($_SESSION['admin_id']) . "');\n\n"
                           . "define('YT_API_KEY', '" . addslashes($_SESSION['yt_key']) . "');\n"
                           . "define('YT_CHANNEL_ID', '" . addslashes($_SESSION['yt_channel']) . "');\n\n"
                           . "define('TWITCH_CLIENT_ID', '" . addslashes($_SESSION['twitch_id']) . "');\n"
                           . "define('TWITCH_CLIENT_SECRET', '" . addslashes($_SESSION['twitch_secret']) . "');\n"
                           . "define('TWITCH_USERNAME', '" . addslashes($_SESSION['twitch_user']) . "');\n\n"
                           . "define('DISCORD_GUILD_ID', '" . addslashes($_SESSION['discord_guild']) . "');\n"
                           . "define('STREAMER_DISCORD_ID', '" . addslashes($_SESSION['streamer_discord']) . "');\n\n"
                           . "define('CRON_KEY', '" . addslashes($_SESSION['cron_key']) . "');\n"
                           . "?>";

    // If check config action is triggered (user manually uploaded it)
    if (isset($_POST['check_config']) || isset($_POST['execute_install'])) {
        if (file_exists('config.php')) {
            $config_write_success = true;
        } else {
            // Try to write
            $bytes = @file_put_contents('config.php', $generated_config_code);
            if ($bytes !== false) {
                $config_write_success = true;
            } else {
                $config_write_success = false;
            }
        }

        if ($config_write_success) {
            // Run database queries
            try {
                $db = new PDO("mysql:host={$_SESSION['db_host']};dbname={$_SESSION['db_name']};charset=utf8mb4", $_SESSION['db_user'], $_SESSION['db_pass']);
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Create Tables
                $queries = [
                    "CREATE TABLE IF NOT EXISTS `links` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `title` varchar(100) NOT NULL,
                      `url` varchar(255) NOT NULL,
                      `sort_order` int(11) DEFAULT '0',
                      `is_visible` tinyint(1) NOT NULL DEFAULT '1',
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

                    "CREATE TABLE IF NOT EXISTS `servers` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `ip` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `port` int(11) NOT NULL,
                      `invite_link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `query_port` int(11) DEFAULT NULL,
                      `game` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Ismeretlen szerver',
                      `map` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `status` tinyint(1) NOT NULL DEFAULT '0',
                      `players` int(11) NOT NULL DEFAULT '0',
                      `max_players` int(11) NOT NULL DEFAULT '0',
                      `last_update` datetime DEFAULT NULL,
                      `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      `is_visible` tinyint(1) NOT NULL DEFAULT '1',
                      `rcon_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `player_list` text COLLATE utf8mb4_unicode_ci,
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `ip_port` (`ip`,`port`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

                    "CREATE TABLE IF NOT EXISTS `site_cache` (
                      `key_name` varchar(50) NOT NULL,
                      `value` text NOT NULL,
                      `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      PRIMARY KEY (`key_name`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

                    "CREATE TABLE IF NOT EXISTS `site_settings` (
                      `setting_key` varchar(50) NOT NULL,
                      `setting_value` varchar(255) NOT NULL,
                      PRIMARY KEY (`setting_key`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

                    "CREATE TABLE IF NOT EXISTS `social_links` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `icon` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fa-link',
                      `sort_order` int(11) NOT NULL DEFAULT '10',
                      `is_visible` tinyint(1) NOT NULL DEFAULT '1',
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

                    "CREATE TABLE IF NOT EXISTS `twitch_chat_logs` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
                      `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

                    "CREATE TABLE IF NOT EXISTS `users` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `steam_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                      `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                      `role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
                      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                      `is_banned` tinyint(1) NOT NULL DEFAULT '0',
                      `perm_links` tinyint(1) NOT NULL DEFAULT '0',
                      `perm_servers` tinyint(1) NOT NULL DEFAULT '0',
                      `perm_rcon` tinyint(1) NOT NULL DEFAULT '0',
                      `perm_users` tinyint(1) NOT NULL DEFAULT '0',
                      `perm_setup` tinyint(1) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`),
                      UNIQUE KEY `steam_id` (`steam_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
                ];

                foreach ($queries as $q) {
                    $db->exec($q);
                }
                $install_logs[] = __t('install_tables_ok');

                // Insert site settings with user-configured visibilities
                $db->exec("TRUNCATE TABLE `site_settings`;");
                $stmtSettings = $db->prepare("INSERT INTO `site_settings` (`setting_key`, `setting_value`) VALUES (?, ?)");
                $default_settings = [
                    ['fixed_language', 'hu'],
                    ['language_mode', 'auto'],
                    ['setup_case', ''],
                    ['setup_cpu', ''],
                    ['setup_gpu', ''],
                    ['setup_headset', ''],
                    ['setup_keyboard', ''],
                    ['setup_mic', ''],
                    ['setup_mobo', ''],
                    ['setup_monitor1', ''],
                    ['setup_monitor2', ''],
                    ['setup_mouse', ''],
                    ['setup_mousepad', ''],
                    ['setup_psu', ''],
                    ['setup_ram', ''],
                    ['setup_storage1', ''],
                    ['setup_storage2', ''],
                    ['show_social', $_SESSION['vis_social']],
                    ['show_twitch', $_SESSION['vis_twitch']],
                    ['show_youtube', $_SESSION['vis_youtube']],
                    ['show_servers', $_SESSION['vis_servers']],
                    ['show_activity', $_SESSION['vis_activity']],
                    ['site_version', '1.0']
                ];
                foreach ($default_settings as $s) {
                    $stmtSettings->execute($s);
                }
                $install_logs[] = __t('install_settings_ok');

                // Insert admin user
                $db->exec("TRUNCATE TABLE `users`;");
                $stmtAdmin = $db->prepare("INSERT INTO `users` (`steam_id`, `username`, `avatar`, `role`, `perm_links`, `perm_servers`, `perm_rcon`, `perm_users`, `perm_setup`) VALUES (?, ?, ?, 'sadmin', 1, 1, 1, 1, 1)");
                $stmtAdmin->execute([
                    $_SESSION['admin_id'],
                    $_SESSION['admin_name'],
                    'https://avatars.steamstatic.com/fef49e7fa7e1997310d705b2a6158ff8dc1cdfeb_full.jpg'
                ]);
                $install_logs[] = __t('install_admin_ok');

                // Insert default social links
                $db->exec("TRUNCATE TABLE `social_links`;");
                $stmtSocial = $db->prepare("INSERT INTO `social_links` (`id`, `title`, `url`, `icon`, `sort_order`, `is_visible`) VALUES (?, ?, ?, ?, ?, ?)");
                $social_data = [
                    [1, 'Twitch', "https://www.twitch.tv/{$_SESSION['twitch_user']}", 'fab fa-twitch', 1, 1],
                    [2, 'Discord', 'https://discord.gg/yourinvite', 'fab fa-discord', 2, 1],
                    [3, 'YouTube', 'https://www.youtube.com/channel/' . $_SESSION['yt_channel'], 'fab fa-youtube', 3, 1],
                    [4, 'Steam', "https://steamcommunity.com/profiles/{$_SESSION['admin_id']}", 'fab fa-steam', 4, 1]
                ];
                foreach ($social_data as $s) {
                    $stmtSocial->execute($s);
                }

                // Insert default links
                $db->exec("TRUNCATE TABLE `links`;");
                $stmtLink = $db->prepare("INSERT INTO `links` (`id`, `title`, `url`, `sort_order`, `is_visible`) VALUES (?, ?, ?, ?, ?)");
                $link_data = [
                    [1, 'SETUP', 'pc-setup', 1, 1]
                ];
                foreach ($link_data as $l) {
                    $stmtLink->execute($l);
                }
                $install_logs[] = __t('install_links_ok');

                // Save absolute path for step 5 display
                $_SESSION['absolute_path'] = __DIR__;
                header('Location: install.php?step=5');
                exit;

            } catch (PDOException $e) {
                $errors[] = "Database error during installation: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __t('title'); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #0a0a0a; color: #cbd5e1; }
        .bg-card { background-color: #121212; }
    </style>
</head>
<body class="flex flex-col min-h-screen justify-center items-center py-10 px-4">

    <!-- Language Selector -->
    <div class="mb-6 flex gap-2">
        <a href="install.php?step=<?php echo $step; ?>&lang=hu" class="px-3 py-1.5 rounded-lg text-sm font-bold border transition-all <?php echo $lang === 'hu' ? 'bg-blue-600 border-blue-500 text-white shadow-lg' : 'bg-card border-gray-800 text-gray-400 hover:text-white' ?>">HU</a>
        <a href="install.php?step=<?php echo $step; ?>&lang=en" class="px-3 py-1.5 rounded-lg text-sm font-bold border transition-all <?php echo $lang === 'en' ? 'bg-blue-600 border-blue-500 text-white shadow-lg' : 'bg-card border-gray-800 text-gray-400 hover:text-white' ?>">EN</a>
    </div>

    <div class="w-full max-w-2xl bg-card border border-gray-800 rounded-2xl shadow-2xl p-6 sm:p-8">
        
        <!-- Logo and Header -->
        <div class="text-center mb-8 border-b border-gray-800 pb-6">
            <h1 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-blue-600 tracking-tight"><?php echo __t('title'); ?></h1>
            <p class="text-gray-400 text-sm mt-1"><?php echo __t('subtitle'); ?></p>
        </div>

        <!-- Progress Steps Tracker -->
        <div class="flex items-center justify-between mb-8 text-xs font-semibold text-gray-500 px-2 sm:px-6">
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center border-2 <?php echo $step >= 1 ? 'border-blue-500 bg-blue-900/40 text-blue-400' : 'border-gray-800'; ?>">1</div>
                <span><?php echo __t('step_req'); ?></span>
            </div>
            <div class="flex-grow border-t border-dashed border-gray-800 mx-2 mb-4"></div>
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center border-2 <?php echo $step >= 2 ? 'border-blue-500 bg-blue-900/40 text-blue-400' : 'border-gray-800'; ?>">2</div>
                <span><?php echo __t('step_db'); ?></span>
            </div>
            <div class="flex-grow border-t border-dashed border-gray-800 mx-2 mb-4"></div>
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center border-2 <?php echo $step >= 3 ? 'border-blue-500 bg-blue-900/40 text-blue-400' : 'border-gray-800'; ?>">3</div>
                <span><?php echo __t('step_config'); ?></span>
            </div>
            <div class="flex-grow border-t border-dashed border-gray-800 mx-2 mb-4"></div>
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center border-2 <?php echo $step >= 4 ? 'border-blue-500 bg-blue-900/40 text-blue-400' : 'border-gray-800'; ?>">4</div>
                <span><?php echo __t('step_install'); ?></span>
            </div>
            <div class="flex-grow border-t border-dashed border-gray-800 mx-2 mb-4"></div>
            <div class="flex flex-col items-center gap-1.5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center border-2 <?php echo $step >= 5 ? 'border-emerald-500 bg-emerald-900/40 text-emerald-400' : 'border-gray-800'; ?>">5</div>
                <span><?php echo __t('step_done'); ?></span>
            </div>
        </div>

        <!-- Feedback Messages -->
        <?php if (!empty($errors)): ?>
            <div class="mb-6 bg-red-900/30 border border-red-800 text-red-400 px-4 py-3 rounded-lg text-sm">
                <?php foreach ($errors as $err): ?>
                    <p class="flex items-center gap-2"><i class="fas fa-exclamation-triangle"></i> <?php echo $err; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- STEP 1: Requirements Check -->
        <?php if ($step === 1): ?>
            <div>
                <h3 class="text-xl font-bold text-white mb-4"><i class="fas fa-microchip mr-2 text-blue-500"></i> Rendszerkövetelmények ellenőrzése / Prerequisite Check</h3>
                
                <div class="space-y-3.5 mb-8">
                    <!-- PHP Version -->
                    <div class="flex justify-between items-center bg-gray-900/40 border border-gray-800 p-4 rounded-xl">
                        <span class="font-medium"><?php echo __t('req_php'); ?></span>
                        <span class="flex items-center gap-2 text-sm font-bold <?php echo $req_php_ok ? 'text-green-400' : 'text-red-400'; ?>">
                            <?php if ($req_php_ok): ?><i class="fas fa-check-circle"></i> <?php echo PHP_VERSION; ?> (<?php echo __t('status_ok'); ?>)<?php else: ?><i class="fas fa-times-circle"></i> <?php echo PHP_VERSION; ?> (<?php echo __t('status_fail'); ?>)<?php endif; ?>
                        </span>
                    </div>

                    <!-- PDO Module -->
                    <div class="flex justify-between items-center bg-gray-900/40 border border-gray-800 p-4 rounded-xl">
                        <span class="font-medium"><?php echo __t('req_pdo'); ?></span>
                        <span class="flex items-center gap-2 text-sm font-bold <?php echo $req_pdo_ok ? 'text-green-400' : 'text-red-400'; ?>">
                            <?php if ($req_pdo_ok): ?><i class="fas fa-check-circle"></i> <?php echo __t('status_ok'); ?><?php else: ?><i class="fas fa-times-circle"></i> <?php echo __t('status_fail'); ?><?php endif; ?>
                        </span>
                    </div>

                    <!-- cURL Module -->
                    <div class="flex justify-between items-center bg-gray-900/40 border border-gray-800 p-4 rounded-xl">
                        <span class="font-medium"><?php echo __t('req_curl'); ?></span>
                        <span class="flex items-center gap-2 text-sm font-bold <?php echo $req_curl_ok ? 'text-green-400' : 'text-red-400'; ?>">
                            <?php if ($req_curl_ok): ?><i class="fas fa-check-circle"></i> <?php echo __t('status_ok'); ?><?php else: ?><i class="fas fa-times-circle"></i> <?php echo __t('status_fail'); ?><?php endif; ?>
                        </span>
                    </div>

                    <!-- Write permissions -->
                    <div class="flex flex-col bg-gray-900/40 border border-gray-800 p-4 rounded-xl gap-1">
                        <div class="flex justify-between items-center">
                            <span class="font-medium"><?php echo __t('req_write'); ?></span>
                            <span class="flex items-center gap-2 text-sm font-bold <?php echo $req_write_ok ? 'text-green-400' : 'text-yellow-400'; ?>">
                                <?php if ($req_write_ok): ?><i class="fas fa-check-circle"></i> <?php echo __t('status_ok'); ?><?php else: ?><i class="fas fa-exclamation-circle"></i> <?php echo __t('status_fail'); ?><?php endif; ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1"><?php echo __t('req_write_desc'); ?></p>
                    </div>
                </div>

                <form method="POST">
                    <button type="submit" name="submit_req" <?php echo !$req_all_ok ? 'disabled' : ''; ?> 
                            class="w-full flex items-center justify-center py-3 bg-blue-600 hover:bg-blue-500 disabled:bg-gray-800 disabled:text-gray-600 disabled:cursor-not-allowed text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-500/20">
                        <?php echo __t('next'); ?> <i class="fas fa-arrow-right ml-2"></i>
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- STEP 2: Database config -->
        <?php if ($step === 2): ?>
            <div>
                <h3 class="text-xl font-bold text-white mb-2"><i class="fas fa-database mr-2 text-blue-500"></i> <?php echo __t('db_title'); ?></h3>
                <p class="text-gray-400 text-sm mb-6 leading-relaxed"><?php echo __t('db_desc'); ?></p>

                <form method="POST" id="db-form" class="space-y-4">
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('db_host'); ?></label>
                        <input type="text" name="db_host" id="db_host" value="<?php echo htmlspecialchars($_POST['db_host'] ?? ($_SESSION['db_host'] ?? 'localhost')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('db_name'); ?></label>
                        <input type="text" name="db_name" id="db_name" value="<?php echo htmlspecialchars($_POST['db_name'] ?? ($_SESSION['db_name'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. streamerpanel">
                    </div>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('db_user'); ?></label>
                        <input type="text" name="db_user" id="db_user" value="<?php echo htmlspecialchars($_POST['db_user'] ?? ($_SESSION['db_user'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>
                    <div class="mb-6">
                        <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('db_pass'); ?></label>
                        <input type="password" name="db_pass" id="db_pass" value="<?php echo htmlspecialchars($_POST['db_pass'] ?? ($_SESSION['db_pass'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors">
                    </div>

                    <div id="test-feedback" class="hidden mb-4 p-3 rounded-lg text-sm"></div>

                    <div class="flex gap-4">
                        <a href="install.php?step=1" class="flex items-center justify-center px-6 py-3 border border-gray-800 text-gray-400 hover:text-white rounded-xl transition-all"><i class="fas fa-arrow-left mr-2"></i> <?php echo __t('back'); ?></a>
                        <button type="button" id="btn-test" class="flex-grow py-3 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-xl transition-all"><?php echo __t('btn_test'); ?></button>
                        <button type="submit" name="submit_db" class="flex-grow py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-500/20"><?php echo __t('next'); ?> <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </form>
            </div>
            
            <script>
                document.getElementById('btn-test').addEventListener('click', function() {
                    const host = document.getElementById('db_host').value;
                    const name = document.getElementById('db_name').value;
                    const user = document.getElementById('db_user').value;
                    const pass = document.getElementById('db_pass').value;
                    const feedback = document.getElementById('test-feedback');
                    const btn = this;

                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                    const formData = new FormData();
                    formData.append('action', 'test_db');
                    formData.append('host', host);
                    formData.append('name', name);
                    formData.append('user', user);
                    formData.append('pass', pass);

                    fetch('install.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        feedback.classList.remove('hidden', 'bg-green-900/30', 'border-green-800', 'text-green-400', 'bg-red-900/30', 'border-red-800', 'text-red-400');
                        if(data.success) {
                            feedback.classList.add('bg-green-900/30', 'border', 'border-green-800', 'text-green-400');
                            feedback.innerHTML = '<i class="fas fa-check-circle mr-1"></i> ' + data.message;
                        } else {
                            feedback.classList.add('bg-red-900/30', 'border', 'border-red-800', 'text-red-400');
                            feedback.innerHTML = '<i class="fas fa-times-circle mr-1"></i> ' + data.message;
                        }
                        btn.disabled = false;
                        btn.textContent = '<?php echo addslashes(__t('btn_test')); ?>';
                    })
                    .catch(err => {
                        feedback.classList.remove('hidden', 'bg-green-900/30', 'border-green-800', 'text-green-400');
                        feedback.classList.add('bg-red-900/30', 'border', 'border-red-800', 'text-red-400');
                        feedback.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Kapcsolódási hiba az API-val.';
                        btn.disabled = false;
                        btn.textContent = '<?php echo addslashes(__t('btn_test')); ?>';
                    });
                });
            </script>
        <?php endif; ?>

        <!-- STEP 3: Config settings -->
        <?php if ($step === 3): ?>
            <div>
                <h3 class="text-xl font-bold text-white mb-2"><i class="fas fa-cogs mr-2 text-blue-500"></i> <?php echo __t('config_title'); ?></h3>
                <p class="text-gray-400 text-sm mb-6 leading-relaxed"><?php echo __t('config_desc'); ?></p>

                <form method="POST" class="space-y-4">
                    
                    <h4 class="text-sm font-bold text-blue-400 border-b border-gray-800 pb-1 mt-4">Steam & Admin Beállítások</h4>
                    <div>
                        <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_steam_key'); ?></label>
                        <input type="text" name="steam_key" value="<?php echo htmlspecialchars($_POST['steam_key'] ?? ($_SESSION['steam_key'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. 1910812A5680...">
                        <p class="text-[10px] text-gray-500 mt-1"><?php echo __t('conf_steam_key_desc'); ?></p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_admin_id'); ?></label>
                            <input type="text" name="admin_id" value="<?php echo htmlspecialchars($_POST['admin_id'] ?? ($_SESSION['admin_id'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. 76561198055223413">
                            <p class="text-[10px] text-gray-500 mt-1"><?php echo __t('conf_admin_id_desc'); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_admin_name'); ?></label>
                            <input type="text" name="admin_name" value="<?php echo htmlspecialchars($_POST['admin_name'] ?? ($_SESSION['admin_name'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. AdminName">
                            <p class="text-[10px] text-gray-500 mt-1"><?php echo __t('conf_admin_name_desc'); ?></p>
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-red-500 border-b border-gray-800 pb-1 mt-6">YouTube Integration</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_yt_key'); ?></label>
                            <input type="text" name="yt_key" value="<?php echo htmlspecialchars($_POST['yt_key'] ?? ($_SESSION['yt_key'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="YouTube API Key">
                            <p class="text-[10px] text-gray-500 mt-1"><?php echo __t('conf_yt_key_desc'); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_yt_channel'); ?></label>
                            <input type="text" name="yt_channel" value="<?php echo htmlspecialchars($_POST['yt_channel'] ?? ($_SESSION['yt_channel'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="YouTube Channel ID">
                            <p class="text-[10px] text-gray-500 mt-1"><?php echo __t('conf_yt_channel_desc'); ?></p>
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-purple-400 border-b border-gray-800 pb-1 mt-6">Twitch Integration</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_twitch_user'); ?></label>
                            <input type="text" name="twitch_user" value="<?php echo htmlspecialchars($_POST['twitch_user'] ?? ($_SESSION['twitch_user'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="e.g. username">
                            <p class="text-[10px] text-gray-500 mt-1"><?php echo __t('conf_twitch_user_desc'); ?></p>
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_twitch_id'); ?></label>
                            <input type="text" name="twitch_id" value="<?php echo htmlspecialchars($_POST['twitch_id'] ?? ($_SESSION['twitch_id'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="Client ID">
                        </div>
                        <div class="sm:col-span-1">
                            <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_twitch_secret'); ?></label>
                            <input type="password" name="twitch_secret" value="<?php echo htmlspecialchars($_POST['twitch_secret'] ?? ($_SESSION['twitch_secret'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="Client Secret">
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-blue-500 border-b border-gray-800 pb-1 mt-6">Discord & Leaderboard Integration</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_discord_guild'); ?></label>
                            <input type="text" name="discord_guild" value="<?php echo htmlspecialchars($_POST['discord_guild'] ?? ($_SESSION['discord_guild'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="Discord Server ID">
                            <p class="text-[10px] text-gray-500 mt-1"><?php echo __t('conf_discord_guild_desc'); ?></p>
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_streamer_discord'); ?></label>
                            <input type="text" name="streamer_discord" value="<?php echo htmlspecialchars($_POST['streamer_discord'] ?? ($_SESSION['streamer_discord'] ?? '')); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors" placeholder="Discord User ID">
                            <p class="text-[10px] text-gray-500 mt-1"><?php echo __t('conf_streamer_discord_desc'); ?></p>
                        </div>
                    </div>
                    
                    <!-- Discord Bot early invite warning -->
                    <div class="bg-blue-950/30 border border-blue-900 text-blue-300 px-4 py-3 rounded-xl text-xs flex items-center gap-2">
                        <i class="fas fa-robot text-base text-blue-500"></i>
                        <p><?php echo __t('conf_discord_bot_notice'); ?></p>
                    </div>

                    <!-- Panel Visibility Selectors -->
                    <h4 class="text-sm font-bold text-indigo-400 border-b border-gray-800 pb-1 mt-6"><?php echo __t('conf_panels_title'); ?></h4>
                    <p class="text-[10px] text-gray-500 mt-0.5"><?php echo __t('conf_panels_desc'); ?></p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-gray-900/30 border border-gray-800 p-4 rounded-xl">
                        <div class="flex items-center">
                            <input type="checkbox" name="show_social" id="show_social" value="1" checked class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                            <label for="show_social" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __t('conf_panel_social'); ?></label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="show_twitch" id="show_twitch" value="1" checked class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                            <label for="show_twitch" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __t('conf_panel_twitch'); ?></label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="show_youtube" id="show_youtube" value="1" checked class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                            <label for="show_youtube" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __t('conf_panel_youtube'); ?></label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" name="show_servers" id="show_servers" value="1" checked class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                            <label for="show_servers" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __t('conf_panel_servers'); ?></label>
                        </div>
                        <div class="flex items-center sm:col-span-2">
                            <input type="checkbox" name="show_activity" id="show_activity" value="1" checked class="w-4 h-4 bg-dark border-gray-700 rounded text-primary focus:ring-primary cursor-pointer">
                            <label for="show_activity" class="ml-2 text-sm text-gray-400 cursor-pointer hover:text-gray-300"><?php echo __t('conf_panel_activity'); ?></label>
                        </div>
                    </div>

                    <h4 class="text-sm font-bold text-gray-400 border-b border-gray-800 pb-1 mt-6">Cron Task Security</h4>
                    <div class="mb-8">
                        <label class="block text-xs uppercase tracking-wider text-gray-400 font-bold mb-1.5"><?php echo __t('conf_cron_key'); ?></label>
                        <input type="text" name="cron_key" value="<?php echo htmlspecialchars($_POST['cron_key'] ?? ($_SESSION['cron_key'] ?? bin2hex(random_bytes(8)))); ?>" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-blue-500 transition-colors">
                        <p class="text-[10px] text-gray-500 mt-1"><?php echo __t('conf_cron_key_desc'); ?></p>
                    </div>

                    <div class="flex gap-4">
                        <a href="install.php?step=2" class="flex items-center justify-center px-6 py-3 border border-gray-800 text-gray-400 hover:text-white rounded-xl transition-all"><i class="fas fa-arrow-left mr-2"></i> <?php echo __t('back'); ?></a>
                        <button type="submit" name="submit_config" class="flex-grow py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-500/20"><?php echo __t('next'); ?> <i class="fas fa-arrow-right ml-2"></i></button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- STEP 4: Execution Screen -->
        <?php if ($step === 4): ?>
            <div>
                <h3 class="text-xl font-bold text-white mb-4"><i class="fas fa-file-invoice mr-2 text-blue-500"></i> <?php echo __t('step_install'); ?></h3>

                <!-- Manual config copy block (Shown initially if cannot write, or if writing failed) -->
                <?php if (!$config_write_success || !file_exists('config.php')): ?>
                    <div class="mb-6 bg-yellow-900/20 border border-yellow-800 text-yellow-200 px-4 py-3 rounded-lg text-sm">
                        <p class="font-bold flex items-center gap-1.5"><i class="fas fa-exclamation-triangle"></i> <?php echo __t('config_write_err'); ?></p>
                        <p class="mt-1"><?php echo __t('config_write_err_desc'); ?></p>
                    </div>

                    <div class="mb-6">
                        <textarea readonly class="w-full h-48 bg-gray-900 border border-gray-800 rounded-xl p-3 font-mono text-xs text-gray-400 focus:outline-none"><?php echo htmlspecialchars($generated_config_code); ?></textarea>
                    </div>

                    <div class="flex gap-4 mb-6">
                        <a href="install.php?action=download_config" class="flex-grow py-3 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-xl text-center shadow-lg transition-all"><i class="fas fa-download mr-2"></i> <?php echo __t('config_download'); ?></a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($install_logs)): ?>
                    <div class="mb-6 bg-gray-900/40 border border-gray-800 rounded-xl p-4 font-mono text-xs text-gray-400 space-y-2.5 max-h-48 overflow-y-auto">
                        <?php foreach($install_logs as $log): ?>
                            <p class="flex items-center gap-2"><span class="text-emerald-500">[OK]</span> <?php echo $log; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="flex gap-4">
                        <a href="install.php?step=3" class="flex items-center justify-center px-6 py-3 border border-gray-800 text-gray-400 hover:text-white rounded-xl transition-all"><i class="fas fa-arrow-left mr-2"></i> <?php echo __t('back'); ?></a>
                        <?php if (file_exists('config.php')): ?>
                            <button type="submit" name="execute_install" class="flex-grow py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-500/20"><?php echo __t('btn_install'); ?></button>
                        <?php else: ?>
                            <button type="submit" name="check_config" class="flex-grow py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-all shadow-lg shadow-blue-500/20"><?php echo __t('next'); ?></button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- STEP 5: Done screen -->
        <?php if ($step === 5): ?>
            <div class="text-center py-4">
                <div class="w-16 h-16 bg-emerald-900/30 border border-emerald-500 text-emerald-400 rounded-full flex items-center justify-center text-3xl mx-auto mb-5 shadow-lg shadow-emerald-500/25">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2"><?php echo __t('install_success'); ?></h3>
                
                <div class="my-4 bg-red-950/40 border border-red-800 text-red-300 font-bold px-4 py-3.5 rounded-xl text-xs flex items-center justify-center gap-2 max-w-lg mx-auto">
                    <i class="fas fa-exclamation-triangle text-lg text-red-500 shrink-0"></i>
                    <p class="text-left leading-relaxed"><?php echo __t('install_delete_warn'); ?></p>
                </div>

                <!-- Cron Job Setup Information -->
                <div class="my-6 text-left max-w-lg mx-auto bg-gray-900/40 border border-gray-800 p-5 rounded-2xl">
                    <h4 class="font-bold text-white text-sm mb-2 flex items-center gap-2"><i class="fas fa-clock text-indigo-400"></i> <?php echo __t('cron_info_title'); ?></h4>
                    <p class="text-gray-400 text-xs leading-relaxed mb-4"><?php echo __t('cron_info_desc'); ?></p>

                    <?php
                        $root_path = $_SESSION['absolute_path'] ?? '/your/website/root';
                    ?>
                    <div class="space-y-3">
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-gray-500 mb-1"><?php echo __t('cron_cmd_cron'); ?></span>
                            <code class="block bg-black/50 border border-gray-800 px-3 py-2 rounded-lg text-xs text-indigo-300 font-mono break-all selection:bg-indigo-900">*/5 * * * * php -q <?php echo htmlspecialchars($root_path); ?>/cron.php</code>
                        </div>
                        <div>
                            <span class="block text-[10px] uppercase font-bold text-gray-500 mb-1"><?php echo __t('cron_cmd_bot'); ?></span>
                            <code class="block bg-black/50 border border-gray-800 px-3 py-2 rounded-lg text-xs text-indigo-300 font-mono break-all selection:bg-indigo-900">*/5 * * * * php -q <?php echo htmlspecialchars($root_path); ?>/twitch_bot.php</code>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-gray-800/60 pt-3 flex items-center gap-2 text-xs text-gray-400 leading-normal">
                        <i class="fas fa-info-circle text-blue-500 shrink-0"></i>
                        <p><?php echo __t('cron_discord_bot_remind'); ?></p>
                    </div>
                </div>

                <a href="index" class="inline-flex items-center px-8 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg shadow-blue-500/20 transition-all transform hover:-translate-y-0.5 mt-2">
                    <?php echo __t('btn_go_site'); ?> <i class="fas fa-chevron-right ml-2"></i>
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>

<?php
// Security protection: disable direct access
if (basename($_SERVER['SCRIPT_FILENAME']) === 'localization.php') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access Denied');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
    ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
    session_start();
}

// Get client IP address (moved to the beginning of the file so it already exists when called)
if (!function_exists('get_client_ip')) {
    function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }
}

// URL-based language switching for testing and direct switching
if (isset($_GET['lang'])) {
    $requested_lang = strtolower($_GET['lang']);
    if (in_array($requested_lang, ['hu', 'en'])) {
        $_SESSION['lang'] = $requested_lang;
        // Clear any potential POST or other cached states from session
    }
}

// Language loading logic
if (!isset($_SESSION['lang'])) {
    $lang_mode = 'auto';
    $fixed_lang = 'hu';
    $stmt_settings = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('language_mode', 'fixed_language')");
    $db_settings = $stmt_settings ? $stmt_settings->fetchAll(PDO::FETCH_KEY_PAIR) : [];
    $lang_mode = $db_settings['language_mode'] ?? 'auto';
    $fixed_lang = $db_settings['fixed_language'] ?? 'hu';

    if ($lang_mode === 'fixed') {
        $_SESSION['lang'] = $fixed_lang;
    } else {
        // Auto (Geolocation)
        $client_ip = get_client_ip();
        
        // Local IP test
        if ($client_ip === '127.0.0.1' || $client_ip === '::1' || strpos($client_ip, '192.168.') === 0 || strpos($client_ip, '10.') === 0) {
            $_SESSION['lang'] = 'hu';
        } else {
            $detected_country = '';
            // Check Cloudflare country code header first (if available)
            if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
                $detected_country = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
            } else {
                // Otherwise query ip-api
                $ctx = stream_context_create([
                    'http' => [
                        'timeout' => 2
                    ]
                ]);
                $json = @file_get_contents("http://ip-api.com/json/{$client_ip}", false, $ctx);
                if ($json) {
                    $geo_data = json_decode($json, true);
                    if (isset($geo_data['status']) && $geo_data['status'] === 'success') {
                        $detected_country = strtoupper($geo_data['countryCode'] ?? '');
                    }
                }
            }

            if ($detected_country === 'HU') {
                $_SESSION['lang'] = 'hu';
            } else {
                $possible_lang = strtolower($detected_country);
                if (!empty($possible_lang) && file_exists(__DIR__ . '/lang/' . $possible_lang . '.json')) {
                    $_SESSION['lang'] = $possible_lang;
                } else {
                    $_SESSION['lang'] = 'en';
                }
            }
        }
    }
}

// Validate selected language
$lang = $_SESSION['lang'] ?? 'en';
if (!file_exists(__DIR__ . "/lang/{$lang}.json")) {
    $lang = 'en';
}

// Load translations
$translations = [];
$json_content = @file_get_contents(__DIR__ . "/lang/{$lang}.json");
if ($json_content) {
    $translations = json_decode($json_content, true) ?: [];
}

// Fallback to English keys if the chosen language has no translation
if ($lang !== 'en') {
    $en_json_content = @file_get_contents(__DIR__ . "/lang/en.json");
    if ($en_json_content) {
        $en_translations = json_decode($en_json_content, true) ?: [];
        $translations = array_merge($en_translations, $translations);
    }
}

// Global translation function
if (!function_exists('__')) {
    function __($key, $replacements = []) {
        global $translations;
        $text = $translations[$key] ?? $key;
        if (!empty($replacements)) {
            foreach ($replacements as $search => $replace) {
                $text = str_replace($search, $replace, $text);
            }
        }
        return $text;
    }
}
?>

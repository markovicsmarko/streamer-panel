<?php
require_once 'db.php';
require_once 'functions.php';

// Security check: only admin or sadmin can enter
if (!isset($_SESSION['steam_id']) || !in_array($_SESSION['role'], ['admin', 'sadmin'])) {
    header('Location: /');
    exit;
}

// CSRF TOKEN INITIALIZATION
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF verification for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Hiba: Érvénytelen CSRF token! A művelet megakadályozva a biztonságod érdekében.");
    }
}

// CSRF verification for GET-based operations
if (isset($_GET['delete']) || isset($_GET['toggle_server']) || isset($_GET['delete_link']) || isset($_GET['delete_social_link']) || isset($_GET['delete_map'])) {
    if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Hiba: Érvénytelen CSRF token! A művelet megakadályozva a biztonságod érdekében.");
    }
}

$success_msg = '';
$error_msg = '';
$link_success_msg = '';
$link_error_msg = '';
$map_success_msg = '';
$map_error_msg = '';

// --- SAVE SYSTEM SETTINGS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_settings'])) {
        if (!has_permission('perm_setup')) {
            die("Hiba: Nincs jogosultságod a beállítások módosításához!");
        }
        
        $show_social   = isset($_POST['show_social']) ? '1' : '0';
        $show_twitch   = isset($_POST['show_twitch']) ? '1' : '0';
        $show_youtube  = isset($_POST['show_youtube']) ? '1' : '0';
        $show_servers  = isset($_POST['show_servers']) ? '1' : '0';
        $show_activity = isset($_POST['show_activity']) ? '1' : '0';
        
        set_setting_value($pdo, 'show_social', $show_social);
        set_setting_value($pdo, 'show_twitch', $show_twitch);
        set_setting_value($pdo, 'show_youtube', $show_youtube);
        set_setting_value($pdo, 'show_servers', $show_servers);
        set_setting_value($pdo, 'show_activity', $show_activity);
        
        if (isset($_POST['site_version'])) {
            set_setting_value($pdo, 'site_version', trim($_POST['site_version']));
        }
        
        // LANGUAGE SETTINGS
        if (isset($_POST['language_mode'])) {
            set_setting_value($pdo, 'language_mode', $_POST['language_mode']);
        }
        if (isset($_POST['fixed_language'])) {
            set_setting_value($pdo, 'fixed_language', $_POST['fixed_language']);
        }
        
        // Clear session language for immediate update
        unset($_SESSION['lang']);
        
        $success_msg = __('admin_success_both');
    }
    
    if (isset($_POST['save_setup_specs'])) {
        if (!has_permission('perm_setup')) {
            die("Hiba: Nincs jogosultságod a beállítások módosításához!");
        }
        
        // SAVE SETUP SPECIFICATIONS
        $setup_keys = [
            'setup_cpu', 'setup_gpu', 'setup_ram', 'setup_mobo', 'setup_psu', 'setup_case',
            'setup_storage1', 'setup_storage2', 'setup_monitor1', 'setup_monitor2',
            'setup_keyboard', 'setup_mouse', 'setup_mousepad', 'setup_headset', 'setup_mic'
        ];
        foreach ($setup_keys as $key) {
            if (isset($_POST[$key])) {
                set_setting_value($pdo, $key, trim($_POST[$key]));
            }
        }
        
        $success_msg = __('admin_setup_specs_success_updated');
    }
}

// --- ADD SERVER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_server'])) {
    if (!has_permission('perm_servers')) {
        die("Hiba: Nincs jogosultságod a szerverek módosításához!");
    }
    $game = trim($_POST['game']);
    $name = trim($_POST['name']);
    $ip = trim($_POST['ip']);
    $port = (int)$_POST['port'];
    $invite_link = trim($_POST['invite_link']);
    $is_visible = isset($_POST['is_visible']) ? 1 : 0;
    
    if ($game === 'discord') {
        if (empty($invite_link)) {
            $error_msg = __('admin_error_discord_invite');
        } elseif (strpos($invite_link, 'discord.com/invite/') === false && strpos($invite_link, 'discord.gg/') === false) {
            $error_msg = __('admin_error_discord_invalid');
        }
        if (empty($ip)) $ip = 'discord.com';
        if ($port == 0) $port = 443;
    }

    if (empty($error_msg) && !empty($game) && !empty($name) && !empty($ip)) {
        $rcon_password = isset($_POST['rcon_password']) ? trim($_POST['rcon_password']) : null;
        if (empty($rcon_password) || !in_array($game, ['cod2', 'cod4', 'mw2', 'mw3', 'cs2', 'dayz'])) {
            $rcon_password = null;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO servers (game, name, ip, port, invite_link, is_visible, rcon_password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$game, $name, $ip, $port, $invite_link, $is_visible, $rcon_password]);
            $success_msg = __('admin_success_server_added');
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error_msg = __('admin_error_server_exists');
            } else {
                $error_msg = __('admin_error_db') . $e->getMessage();
            }
        }
    } elseif(empty($error_msg)) {
        $error_msg = __('admin_error_required');
    }
}

// --- MODIFY RCON PASSWORD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_rcon'])) {
    if (!has_permission('perm_servers')) {
        die("Hiba: Nincs jogosultságod a szerverek módosításához!");
    }
    $server_id = (int)$_POST['server_id'];
    $rcon_password = trim($_POST['rcon_password']);
    
    try {
        $stmt = $pdo->prepare("UPDATE servers SET rcon_password = ? WHERE id = ?");
        $stmt->execute([$rcon_password === '' ? null : $rcon_password, $server_id]);
        $success_msg = __('admin_rcon_success_updated');
    } catch (PDOException $e) {
        $error_msg = "Hiba az RCON jelszó frissítésekor: " . $e->getMessage();
    }
}

$edit_server = null;
if (isset($_GET['edit_rcon'])) {
    if (!has_permission('perm_servers')) {
        die("Hiba: Nincs jogosultságod a szerverek szerkesztéséhez!");
    }
    $edit_id = (int)$_GET['edit_rcon'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM servers WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_server = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_msg = "Hiba a szerver betöltésekor: " . $e->getMessage();
    }
}

// --- DELETE SERVER ---
if (isset($_GET['delete'])) {
    if (!has_permission('perm_servers')) {
        die("Hiba: Nincs jogosultságod a szerverek törléséhez!");
    }
    $delete_id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM servers WHERE id = ?");
        $stmt->execute([$delete_id]);
        header('Location: admin?tab=servers');
        exit;
    } catch (PDOException $e) {
        $error_msg = __('admin_error_delete_server') . $e->getMessage();
    }
}

// --- TOGGLE SERVER VISIBILITY ---
if (isset($_GET['toggle_server'])) {
    if (!has_permission('perm_servers')) {
        die("Hiba: Nincs jogosultságod a szerverek módosításához!");
    }
    $toggle_id = (int)$_GET['toggle_server'];
    try {
        $stmt = $pdo->prepare("UPDATE servers SET is_visible = NOT is_visible WHERE id = ?");
        $stmt->execute([$toggle_id]);
        header('Location: admin?tab=servers');
        exit;
    } catch (PDOException $e) {
        $error_msg = __('admin_error_toggle_server') . $e->getMessage();
    }
}

// --- ADD LINK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_link'])) {
    if (!has_permission('perm_links')) {
        die("Hiba: Nincs jogosultságod a linkek módosításához!");
    }
    $title = trim($_POST['link_title']);
    $url = trim($_POST['link_url']);
    $sort_order = (int)$_POST['link_order'];
    $is_visible = isset($_POST['link_is_visible']) ? 1 : 0;

    if (!empty($title) && !empty($url)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO links (title, url, sort_order, is_visible) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $url, $sort_order, $is_visible]);
            $link_success_msg = __('admin_success_link_added');
        } catch (PDOException $e) {
            $link_error_msg = __('admin_error_link_added') . $e->getMessage();
        }
    } else {
        $link_error_msg = __('admin_error_link_required');
    }
}

// --- UPDATE LINK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_links'])) {
    if (!has_permission('perm_links')) {
        die("Hiba: Nincs jogosultságod a linkek módosításához!");
    }
    if (!empty($_POST['links']) && is_array($_POST['links'])) {
        try {
            $stmt = $pdo->prepare("UPDATE links SET sort_order = ?, title = ?, url = ?, is_visible = ? WHERE id = ?");
            foreach ($_POST['links'] as $id => $data) {
                $is_visible = isset($data['is_visible']) ? 1 : 0;
                $stmt->execute([(int)$data['sort_order'], trim($data['title']), trim($data['url']), $is_visible, (int)$id]);
            }
            $link_success_msg = __('admin_success_links_updated');
        } catch (PDOException $e) {
            $link_error_msg = __('admin_error_links_updated') . $e->getMessage();
        }
    }
}

// --- DELETE LINK ---
if (isset($_GET['delete_link'])) {
    if (!has_permission('perm_links')) {
        die("Hiba: Nincs jogosultságod a linkek törléséhez!");
    }
    $delete_id = (int)$_GET['delete_link'];
    try {
        $stmt = $pdo->prepare("DELETE FROM links WHERE id = ?");
        $stmt->execute([$delete_id]);
        header('Location: admin?tab=links');
        exit;
    } catch (PDOException $e) {
        $link_error_msg = __('admin_error_db') . $e->getMessage();
    }
}

// --- ADD SOCIAL LINK ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_social_link'])) {
    if (!has_permission('perm_links')) {
        die("Hiba: Nincs jogosultságod a linkek módosításához!");
    }
    $title = trim($_POST['social_title']);
    $url = trim($_POST['social_url']);
    $icon = trim($_POST['social_icon']);
    $sort_order = (int)$_POST['social_order'];
    $is_visible = isset($_POST['social_is_visible']) ? 1 : 0;

    if (!empty($title) && !empty($url)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO social_links (title, url, icon, sort_order, is_visible) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $url, $icon, $sort_order, $is_visible]);
            $link_success_msg = __('admin_success_social_added');
        } catch (PDOException $e) {
            $link_error_msg = __('admin_error_social_added') . $e->getMessage();
        }
    } else {
        $link_error_msg = __('admin_error_link_required');
    }
}

// --- UPDATE SOCIAL LINKS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_social_links'])) {
    if (!has_permission('perm_links')) {
        die("Hiba: Nincs jogosultságod a linkek módosításához!");
    }
    if (!empty($_POST['social_links']) && is_array($_POST['social_links'])) {
        try {
            $stmt = $pdo->prepare("UPDATE social_links SET sort_order = ?, title = ?, url = ?, icon = ?, is_visible = ? WHERE id = ?");
            foreach ($_POST['social_links'] as $id => $data) {
                $is_visible = isset($data['is_visible']) ? 1 : 0;
                $stmt->execute([(int)$data['sort_order'], trim($data['title']), trim($data['url']), trim($data['icon']), $is_visible, (int)$id]);
            }
            $link_success_msg = __('admin_success_social_updated');
        } catch (PDOException $e) {
            $link_error_msg = __('admin_error_social_updated') . $e->getMessage();
        }
    }
}

// --- DELETE SOCIAL LINK ---
if (isset($_GET['delete_social_link'])) {
    if (!has_permission('perm_links')) {
        die("Hiba: Nincs jogosultságod a linkek törléséhez!");
    }
    $delete_id = (int)$_GET['delete_social_link'];
    try {
        $stmt = $pdo->prepare("DELETE FROM social_links WHERE id = ?");
        $stmt->execute([$delete_id]);
        header('Location: admin?tab=links');
        exit;
    } catch (PDOException $e) {
        $link_error_msg = __('admin_error_db') . $e->getMessage();
    }
}



// --- UPDATE USER PERMISSIONS AND BANNING ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    if (!has_permission('perm_users')) {
        die(__('admin_users_error_no_permission'));
    }
    
    $user_steam_id = trim($_POST['user_steam_id']);
    $role = trim($_POST['role']);
    $is_banned = isset($_POST['is_banned']) ? 1 : 0;
    
    $perm_links = isset($_POST['perm_links']) ? 1 : 0;
    $perm_servers = isset($_POST['perm_servers']) ? 1 : 0;
    $perm_rcon = isset($_POST['perm_rcon']) ? 1 : 0;
    $perm_users = isset($_POST['perm_users']) ? 1 : 0;
    $perm_setup = isset($_POST['perm_setup']) ? 1 : 0;
    
    try {
        if ($user_steam_id === ADMIN_STEAM_ID) {
            $error_msg = __('admin_users_error_self_edit');
        } elseif ($user_steam_id === $_SESSION['steam_id']) {
            $error_msg = __('admin_users_error_own_edit');
        } else {
            if ($role === 'sadmin' && $_SESSION['role'] !== 'sadmin') {
                $role = 'admin';
            }
            
            $stmt = $pdo->prepare("
                UPDATE users 
                SET role = ?, is_banned = ?, perm_links = ?, perm_servers = ?, perm_rcon = ?, perm_users = ?, perm_setup = ? 
                WHERE steam_id = ?
            ");
            $stmt->execute([$role, $is_banned, $perm_links, $perm_servers, $perm_rcon, $perm_users, $perm_setup, $user_steam_id]);
            $success_msg = __('admin_users_success_updated');
        }
    } catch (PDOException $e) {
        $error_msg = "Hiba a felhasználó frissítésekor: " . $e->getMessage();
    }
}

// Determine available tabs based on permissions
$available_tabs = [];
if (has_permission('perm_setup')) {
    $available_tabs['setup'] = __('admin_tab_setup');
    $available_tabs['pc_setup'] = __('admin_tab_pc_setup');
}
if (has_permission('perm_servers')) $available_tabs['servers'] = __('admin_tab_servers');
if (has_permission('perm_links'))   $available_tabs['links']   = __('admin_tab_links');
if (has_permission('perm_rcon'))    $available_tabs['rcon']    = __('admin_tab_rcon');
if (has_permission('perm_users'))   $available_tabs['users']   = __('admin_tab_users');

if (empty($available_tabs)) {
    die("Hiba: Nincs jogosultságod egyik adminisztrációs modulhoz sem.");
}

$active_tab = isset($_GET['tab']) && isset($available_tabs[$_GET['tab']]) ? $_GET['tab'] : (isset($_GET['edit_rcon']) && isset($available_tabs['servers']) ? 'servers' : array_key_first($available_tabs));

// Tab-specific data loading (Optimized)
$servers = [];
$links = [];
$social_links = [];
$all_users = [];

if ($active_tab === 'setup') {
    // For statistics, only load IDs/counts, not full rows
    $stmt = $pdo->query("SELECT id FROM servers");
    $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->query("SELECT id FROM links");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->query("SELECT id FROM social_links");
    $social_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (in_array($active_tab, ['servers', 'rcon'])) {
    $stmt = $pdo->query("SELECT * FROM servers ORDER BY id DESC");
    $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($active_tab === 'links') {
    $stmt = $pdo->query("SELECT * FROM links ORDER BY sort_order ASC, id ASC");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC, id ASC");
    $social_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif ($active_tab === 'users') {
    if (has_permission('perm_users')) {
        try {
            $stmt = $pdo->query("SELECT * FROM users ORDER BY FIELD(role, 'sadmin', 'admin', 'user') ASC, username ASC");
            $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Suppress error
        }
    }
}

// Load system settings for checkboxes (global settings, may be needed for any tab)
$stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$sys_show_social  = ($settings['show_social'] ?? '1') === '1';
$sys_show_twitch  = ($settings['show_twitch'] ?? '1') === '1';
$sys_show_youtube = ($settings['show_youtube'] ?? '1') === '1';
$sys_show_servers = ($settings['show_servers'] ?? '1') === '1';
$sys_show_activity = ($settings['show_activity'] ?? '1') === '1';

// Default values for diagnostic and status variables
$last_cron_status = __('admin_cron_not_run');
$twitch_bot_status = __('admin_bot_offline');
$twitch_bot_online = false;
$last_cron_results = [];
$twitch_irc_conn_ok = false;
$twitch_bot_active_lock = false;
$yt_last_success = null;
$yt_last_error = null;
$twitch_last_success = null;
$twitch_last_error = null;
if ($active_tab === 'setup') {
    try {
        // First, try to retrieve the dedicated 'last_cron_run' entry
        $stmtCron = $pdo->prepare("SELECT updated_at as last_updated, TIMESTAMPDIFF(SECOND, updated_at, NOW()) as diff_secs FROM site_cache WHERE key_name = ?");
        $stmtCron->execute(['last_cron_run']);
        $cron_info = $stmtCron->fetch(PDO::FETCH_ASSOC);
        
        // If the modified cron has not run yet, retrieve the last cache modification as a fallback
        if (!$cron_info || empty($cron_info['last_updated'])) {
            $stmtFallback = $pdo->query("SELECT MAX(updated_at) as last_updated, TIMESTAMPDIFF(SECOND, MAX(updated_at), NOW()) as diff_secs FROM site_cache");
            $cron_info = $stmtFallback->fetch(PDO::FETCH_ASSOC);
        }
        
        if ($cron_info && $cron_info['last_updated']) {
            $diff_secs = (int)$cron_info['diff_secs'];
            if ($diff_secs < 0) {
                $diff_secs = 0;
            }
            $diff_mins = (int)round($diff_secs / 60);
            
            if ($diff_mins < 1) {
                $last_cron_status = __('admin_cron_just_now');
            } elseif ($diff_mins < 60) {
                $last_cron_status = __('admin_cron_mins', ['{mins}' => $diff_mins]);
            } else {
                $diff_hours = (int)round($diff_mins / 60);
                if ($diff_hours < 24) {
                    $last_cron_status = __('admin_cron_hours', ['{hours}' => $diff_hours]);
                } else {
                    $last_cron_status = date('Y-m-d H:i', strtotime($cron_info['last_updated']));
                }
            }
            $last_cron_status .= " (" . date('H:i', strtotime($cron_info['last_updated'])) . ")";
        } else {
            $last_cron_status = __('admin_cron_not_run');
        }
    } catch (PDOException $e) {
        // Suppress error
    }
    
    // Retrieve Twitch bot synchronization status
    try {
        $stmtBot = $pdo->prepare("SELECT value as last_ping, TIMESTAMPDIFF(SECOND, FROM_UNIXTIME(value), NOW()) as diff_secs FROM site_cache WHERE key_name = ?");
        $stmtBot->execute(['twitch_bot_last_ping']);
        $bot_info = $stmtBot->fetch(PDO::FETCH_ASSOC);
        
        if ($bot_info && !empty($bot_info['last_ping'])) {
            $diff_secs = (int)$bot_info['diff_secs'];
            if ($diff_secs < 0) {
                $diff_secs = 0;
            }
            if ($diff_secs < 180) { // Online within 3 minutes
                $twitch_bot_online = true;
                $twitch_bot_status = __('admin_bot_online') . " (" . $diff_secs . "s)";
            } else {
                $diff_mins = (int)round($diff_secs / 60);
                if ($diff_mins < 60) {
                    $twitch_bot_status = __('admin_bot_offline_mins', ['{mins}' => $diff_mins]);
                } else {
                    $twitch_bot_status = __('admin_bot_offline');
                }
            }
        }
    } catch (PDOException $e) {
        // Suppress error
    }
    
    // Retrieve latest Cron output log and diagnostics
    try {
        // 1. Retrieve Cron log from cache
        $stmtRes = $pdo->prepare("SELECT value FROM site_cache WHERE key_name = ?");
        $stmtRes->execute(['last_cron_results']);
        $res_info = $stmtRes->fetch(PDO::FETCH_ASSOC);
        if ($res_info) {
            $last_cron_results = json_decode($res_info['value'], true);
        }
        
        // Load API last success/failure run times
        $yt_last_success = get_cache_value($pdo, 'youtube_api_last_success');
        $yt_last_error = get_cache_value($pdo, 'youtube_api_last_error');
        $twitch_last_success = get_cache_value($pdo, 'twitch_api_last_success');
        $twitch_last_error = get_cache_value($pdo, 'twitch_api_last_error');
        
        // 2. Test real-time Twitch IRC connection
        $test_socket = @fsockopen('ssl://irc.chat.twitch.tv', 443, $errno, $errstr, 2);
        if ($test_socket) {
            $twitch_irc_conn_ok = true;
            @fclose($test_socket);
        }
        
        // 3. Test real-time background lock
        $test_lock_file = @fopen(__DIR__ . '/twitch_bot.lock', 'c');
        if ($test_lock_file) {
            if (!flock($test_lock_file, LOCK_EX | LOCK_NB)) {
                $twitch_bot_active_lock = true; // If we cannot lock, it is running in the background
            } else {
                flock($test_lock_file, LOCK_UN);
            }
            fclose($test_lock_file);
        }
    } catch (Exception $e) {
        // Suppress error
    }
}

// INCLUDE HEADER
require_once 'header.php';
?>

<main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Title and System Feedback -->
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-white uppercase tracking-wider"><?php echo __('admin_title'); ?></h1>
    </div>

    <!-- Tab navigation -->
    <div class="flex flex-wrap border-b border-gray-800 mb-8 gap-1">
        <?php foreach ($available_tabs as $tab_key => $tab_title): ?>
            <a href="admin?tab=<?php echo $tab_key; ?>" 
               class="px-5 py-3 font-semibold text-sm transition-all rounded-t-lg border-b-2 <?php echo $active_tab === $tab_key ? 'border-primary text-primary bg-primary/5' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-800/30' ?>">
               <?php if ($tab_key === 'setup'): ?><i class="fas fa-cog mr-2"></i><?php endif; ?>
               <?php if ($tab_key === 'pc_setup'): ?><i class="fas fa-desktop mr-2"></i><?php endif; ?>
               <?php if ($tab_key === 'servers'): ?><i class="fas fa-server mr-2"></i><?php endif; ?>
               <?php if ($tab_key === 'links'): ?><i class="fas fa-link mr-2"></i><?php endif; ?>
               <?php if ($tab_key === 'rcon'): ?><i class="fas fa-terminal mr-2"></i><?php endif; ?>
               <?php if ($tab_key === 'users'): ?><i class="fas fa-users mr-2"></i><?php endif; ?>
               <?php echo $tab_title; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($success_msg): ?>
        <div class="mb-6 bg-green-900/50 border border-green-800 text-green-400 px-4 py-3 rounded relative shadow-lg">
            <span class="block sm:inline"><?php echo $success_msg; ?></span>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="mb-6 bg-red-900/50 border border-red-800 text-red-400 px-4 py-3 rounded relative shadow-lg">
            <span class="block sm:inline"><?php echo $error_msg; ?></span>
        </div>
    <?php endif; ?>

    <?php
    // Security constant to prevent direct access to view files
    define('ADMIN_LOADED', true);

    $view_path = __DIR__ . "/admin_views/tab_{$active_tab}.php";
    if (file_exists($view_path)) {
        include $view_path;
    } else {
        echo "<div class='text-red-500 font-bold p-4 bg-red-900/10 border border-red-800/30 rounded-lg'>" . __('admin_error_view_not_found', ['{tab}' => htmlspecialchars($active_tab)]) . "</div>";
    }
    ?>
</main>
<?php require_once 'footer.php'; ?>

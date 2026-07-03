<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
    ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
    session_start();
}

// Real-time permission and ban check
if (isset($_SESSION['steam_id']) && isset($pdo)) {
    try {
        $stmtStatus = $pdo->prepare("SELECT role, is_banned, perm_links, perm_servers, perm_rcon, perm_users, perm_setup FROM users WHERE steam_id = ?");
        $stmtStatus->execute([$_SESSION['steam_id']]);
        $userStatus = $stmtStatus->fetch(PDO::FETCH_ASSOC);
        if ($userStatus) {
            if ($userStatus['is_banned'] == 1) {
                // If banned, log out immediately
                session_destroy();
                header('Location: index.php?banned=1');
                exit;
            }
            $_SESSION['role'] = $userStatus['role'];
            $_SESSION['perm_links'] = (int)$userStatus['perm_links'];
            $_SESSION['perm_servers'] = (int)$userStatus['perm_servers'];
            $_SESSION['perm_rcon'] = (int)$userStatus['perm_rcon'];
            $_SESSION['perm_users'] = (int)$userStatus['perm_users'];
            $_SESSION['perm_setup'] = (int)$userStatus['perm_setup'];
        } else {
            session_destroy();
            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        // Suppress error
    }
}

// Fetch links for navigation
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'links'");
    $header_links = [];
    if ($stmt->rowCount() > 0) {
        $stmtLinks = $pdo->query("SELECT * FROM links WHERE is_visible = 1 ORDER BY sort_order ASC, id ASC");
        while ($row = $stmtLinks->fetch(PDO::FETCH_ASSOC)) {
            $header_links[] = $row;
        }
    }
} catch (PDOException $e) {
    $header_links = [];
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($_SESSION['lang'] ?? 'hu'); ?>" class="dark h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(ucfirst(TWITCH_USERNAME)); ?> StreamerPanel</title>
    <meta name="description" content="<?php echo htmlspecialchars(__('meta_description')); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(__('meta_keywords')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="assets/tailwind.config.js"></script>
    
    <style>
        /* Responsive tables on mobile (card view) */
        @media (max-width: 768px) {
            .responsive-table thead { display: none; }
            .responsive-table, .responsive-table tbody, .responsive-table tr { display: block; width: 100%; }
            .responsive-table tr.hidden { display: none !important; }
            
            /* Base server card (if the player list is not open) */
            .responsive-table > tbody > tr:not([id^="players_row_"]) { 
                margin-bottom: 1.5rem; 
                border: 2px solid #4b5563; 
                border-radius: 0.75rem; 
                background-color: rgba(30, 41, 59, 0.25);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
                overflow: hidden; 
                transition: border-color 0.2s ease, background-color 0.2s ease;
            }
            
            /* If the player list directly following it is open, we flatten the bottom of the server card */
            .responsive-table > tbody > tr:not([id^="players_row_"]):has(+ tr:not(.hidden)[id^="players_row_"]) {
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
                border-bottom: 2px dashed #4b5563;
                margin-bottom: 0;
            }
 
            /* Style of the open player list on mobile, which attaches to the server card */
            .responsive-table > tbody > tr[id^="players_row_"]:not(.hidden) {
                margin-bottom: 1.5rem;
                border: 2px solid #4b5563;
                border-top: none;
                border-top-left-radius: 0;
                border-top-right-radius: 0;
                border-bottom-left-radius: 0.75rem;
                border-bottom-right-radius: 0.75rem;
                background-color: rgba(15, 23, 42, 0.6); /* darker background */
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }

            .responsive-table td[data-label] { 
                display: flex; 
                justify-content: space-between; 
                align-items: center; 
                padding: 0.75rem 1rem; 
                border-bottom: 1px solid #1f2937; 
                text-align: right; 
            }
            .responsive-table td[data-label]:last-child { border-bottom: none; }
            .responsive-table td[data-label]::before { 
                content: attr(data-label); 
                font-weight: bold; 
                text-transform: uppercase; 
                font-size: 0.75rem; 
                color: #9ca3af; 
                margin-right: 1rem; 
                text-align: left; 
            }
            .responsive-table td:not([data-label]) { display: block; padding: 0.5rem; }
        }
    </style>

    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">
</head>
<body class="bg-darker text-gray-200 font-sans antialiased flex flex-col min-h-screen">

    <nav class="bg-dark border-b border-gray-800 shadow-lg relative z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-6">
                    <a href="/" class="text-white font-bold text-xl tracking-wider uppercase bg-clip-text text-transparent bg-gradient-to-r from-blue-400 to-primary">
                        <?php echo htmlspecialchars(ucfirst(TWITCH_USERNAME)); ?>
                    </a>
                    <div class="hidden md:flex space-x-4 items-center">
                        
                        
                        <?php if (!empty($header_links)): ?>
                            <?php foreach ($header_links as $link): ?>
                                
                                <?php 
                                    $target = (strpos($link['url'], 'http') === 0) ? 'target="_blank"' : ''; 
                                    $href = $link['url'];
                                    // If it is an internal link and ends in .php, strip the extension
                                    if (strpos($href, 'http') !== 0 && substr(strtolower($href), -4) === '.php') {
                                        $href = substr($href, 0, -4);
                                    }
                                ?>
                                <a href="<?php echo htmlspecialchars($href); ?>" <?php echo $target; ?> class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors">
                                    <?php echo htmlspecialchars($link['title']); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['steam_id'])): ?>
                        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'sadmin')): ?>
                            <a href="admin" class="text-primary hover:text-blue-400 px-3 py-2 rounded-md text-sm font-bold transition-colors border border-gray-700 hover:border-primary">
                                <?php echo __('nav_admin_panel'); ?>
                            </a>
                        <?php endif; ?>
                        
                        <div class="flex items-center space-x-3 border-l border-gray-700 pl-4">
                            <img src="<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="Avatar" class="w-8 h-8 rounded-full border border-gray-600 shadow-sm">
                            <span class="text-sm font-medium text-gray-300 hidden sm:block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <a href="logout" class="text-red-400 hover:text-red-300 text-sm font-medium transition-colors ml-2 px-2 py-1 bg-red-900/20 rounded hover:bg-red-900/40">
                                <?php echo __('nav_logout'); ?>
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="steam_login" class="flex items-center px-4 py-2 bg-[#171a21] hover:bg-[#2a303c] text-white text-sm font-medium rounded-md transition-colors border border-[#171a21] hover:border-gray-500 shadow-lg">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/8/83/Steam_icon_logo.svg" alt="Steam" class="w-5 h-5 mr-2 hidden sm:block">
                            <span class="hidden sm:inline"><?php echo __('nav_login_steam'); ?></span>
                            <span class="sm:hidden"><?php echo __('nav_login'); ?></span>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile menu button -->
                    <div class="md:hidden flex items-center ml-2 border-l border-gray-700 pl-4">
                        <button id="mobile-menu-btn" class="text-gray-400 hover:text-white focus:outline-none p-2 rounded-md hover:bg-gray-800 transition-colors">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
            
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-darker border-b border-gray-800 absolute w-full shadow-2xl">
            <div class="px-4 pt-2 pb-4 space-y-1 sm:px-6">
                <?php if (!empty($header_links)): ?>
                    <?php foreach ($header_links as $link): ?>
                        <?php 
                            $target = (strpos($link['url'], 'http') === 0) ? 'target="_blank"' : ''; 
                            $href = $link['url'];
                            if (strpos($href, 'http') !== 0 && substr(strtolower($href), -4) === '.php') {
                                $href = substr($href, 0, -4);
                            }
                        ?>
                        <a href="<?php echo htmlspecialchars($href); ?>" <?php echo $target; ?> class="text-gray-300 hover:text-white hover:bg-gray-800 block px-3 py-3 rounded-md text-base font-bold transition-colors">
                            <?php echo htmlspecialchars($link['title']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Close the mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const btn = document.getElementById('mobile-menu-btn');
            if (menu && !menu.classList.contains('hidden') && !menu.contains(event.target) && !btn.contains(event.target)) {
                menu.classList.add('hidden');
            }
        });
    </script>

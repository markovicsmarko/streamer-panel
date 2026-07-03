<?php
// Include database connection
require_once 'db.php';

// Include helper functions (color code decoder, db cache, db settings)
require_once 'functions.php';

// Query system settings for module visibility (optimized to 1 query)
$settings = [];
try {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    // Fallback
}

$show_social   = ($settings['show_social'] ?? '1') === '1';
$show_twitch   = ($settings['show_twitch'] ?? '1') === '1';
$show_youtube  = ($settings['show_youtube'] ?? '1') === '1';
$show_servers  = ($settings['show_servers'] ?? '1') === '1';
$show_activity = ($settings['show_activity'] ?? '1') === '1';

// Query servers from the database (only if server list is visible)
$servers = [];
if ($show_servers) {
    try {
        $stmt = $pdo->query("SELECT * FROM servers WHERE is_visible = 1 ORDER BY CASE WHEN game = 'discord' THEN 0 ELSE 1 END, game ASC, players DESC");
        $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error querying servers: " . $e->getMessage());
    }
}

// Fetch social links (only if Linktree section is visible)
$social_links = [];
if ($show_social) {
    try {
        $stmt = $pdo->query("SELECT * FROM social_links WHERE is_visible = 1 ORDER BY sort_order ASC, id ASC");
        $social_links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Table does not exist yet
    }
}

// Dictionary of game display names
$game_names = [
    'discord' => __('game_names_discord'),
    'cs2' => __('game_names_cs2'),
    'dayz' => __('game_names_dayz'),
    'cod2' => __('game_names_cod2'),
    'cod4' => __('game_names_cod4'),
    'mw2' => __('game_names_mw2'),
    'mw3' => __('game_names_mw3'),
    'minecraft' => __('game_names_minecraft')
];

// INCLUDE HEADER
require_once 'header.php';
?>

<main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Social Links (Linktree) section -->
    <?php if ($show_social && !empty($social_links)): ?>
    <div class="mb-12">
        <h2 class="text-xl font-bold text-white mb-6 border-b border-gray-800 pb-2 flex items-center">
            <svg class="w-6 h-6 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
            <?php echo __('section_contacts'); ?>
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($social_links as $link): ?>
                <?php
                    // Set color based on the icon
                    $iconColor = 'text-gray-300';
                    $hoverColor = 'hover:bg-gray-800';
                    
                    if (strpos($link['icon'], 'twitch') !== false) { $iconColor = 'text-[#9146FF]'; $hoverColor = 'hover:border-[#9146FF]'; }
                    elseif (strpos($link['icon'], 'youtube') !== false) { $iconColor = 'text-[#FF0000]'; $hoverColor = 'hover:border-[#FF0000]'; }
                    elseif (strpos($link['icon'], 'discord') !== false) { $iconColor = 'text-[#5865F2]'; $hoverColor = 'hover:border-[#5865F2]'; }
                    elseif (strpos($link['icon'], 'instagram') !== false) { $iconColor = 'text-[#E1306C]'; $hoverColor = 'hover:border-[#E1306C]'; }
                    elseif (strpos($link['icon'], 'tiktok') !== false) { $iconColor = 'text-white'; $hoverColor = 'hover:border-gray-500'; }
                    elseif (strpos($link['icon'], 'steam') !== false) { $iconColor = 'text-[#66c0f4]'; $hoverColor = 'hover:border-[#66c0f4]'; }
                    elseif (strpos($link['icon'], 'money') !== false || strpos($link['icon'], 'donate') !== false) { $iconColor = 'text-emerald-500'; $hoverColor = 'hover:border-emerald-500'; }
                    elseif (strpos($link['icon'], 'envelope') !== false) { $iconColor = 'text-blue-400'; $hoverColor = 'hover:border-blue-400'; }
                ?>
                <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" class="flex items-center justify-center space-x-3 bg-card border border-gray-800 p-4 rounded-xl shadow-lg transition-all transform hover:-translate-y-1 <?php echo $hoverColor; ?> group">
                    <i class="<?php echo htmlspecialchars($link['icon']); ?> text-2xl <?php echo $iconColor; ?> group-hover:scale-110 transition-transform"></i>
                    <span class="font-bold text-gray-200"><?php echo htmlspecialchars($link['title']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Media section: Twitch and YouTube live/latest players -->
    <?php if ($show_twitch || $show_youtube): ?>
    <div class="mb-12 grid grid-cols-1 <?php echo ($show_twitch && $show_youtube) ? 'xl:grid-cols-2' : ''; ?> gap-8">
        
        <?php if ($show_twitch): ?>
        <!-- Twitch Embed -->
        <div class="bg-card shadow-xl rounded-lg border border-gray-800 p-6">
            <h2 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/></svg>
                <?php echo __('section_live_twitch'); ?>
            </h2>
            <div class="relative w-full rounded-xl overflow-hidden shadow-xl" style="padding-top: 56.25%;">
                <iframe class="absolute top-0 left-0 w-full h-full"
                    src="https://player.twitch.tv/?channel=<?php echo htmlspecialchars(TWITCH_USERNAME); ?>&parent=<?php echo $_SERVER['HTTP_HOST'] ?? 'localhost'; ?>"
                    frameborder="0"
                    allowfullscreen="true"
                    scrolling="no">
                </iframe>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($show_youtube): ?>
        <!-- YouTube Embed -->
        <div class="bg-card shadow-xl rounded-lg border border-gray-800 p-6">
            <h2 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2 flex items-center">
                <svg class="w-6 h-6 mr-2 text-red-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                <?php echo __('section_latest_yt'); ?>
            </h2>
            <?php
            $video_id = get_cache_value($pdo, 'youtube_video');
            if (!empty($video_id)) {
                echo '<div class="relative w-full rounded-xl overflow-hidden shadow-xl" style="padding-top: 56.25%;">';
                echo '<iframe class="absolute top-0 left-0 w-full h-full" src="https://www.youtube.com/embed/'.htmlspecialchars($video_id).'" frameborder="0" allowfullscreen></iframe>';
                echo '</div>';
            } else {
                echo '<p class="text-gray-500 italic mt-4 text-center">' . htmlspecialchars(__('yt_video_not_cached')) . '</p>';
            }
            ?>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <!-- Server list section -->
    <?php if ($show_servers): ?>
    <div class="mb-8 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-white"><?php echo __('server_list_title'); ?></h1>
        <span class="bg-card border border-gray-700 text-gray-300 text-xs font-semibold px-3 py-1 rounded-full">
            <?php echo __('total_servers'); ?> <?php echo count($servers); ?>
        </span>
    </div>

    <div class="bg-card shadow-xl rounded-lg overflow-hidden border border-gray-800 mb-3">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-800 responsive-table">
                <thead class="bg-dark">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"><?php echo __('table_game'); ?></th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"><?php echo __('table_name'); ?></th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"><?php echo __('table_ip_invite'); ?></th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider"><?php echo __('table_players'); ?></th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider"><?php echo __('table_status'); ?></th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider"><?php echo __('table_action'); ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    <?php if (count($servers) > 0): ?>
                        <?php foreach ($servers as $server): ?>
                            <tr class="hover:bg-gray-800/80 transition-colors cursor-pointer" onclick="toggleServerPlayers(<?php echo $server['id']; ?>)">
                                <td data-label="<?php echo __('table_game'); ?>" class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $server['game'] === 'discord' ? 'bg-blue-900/50 text-blue-400 border border-blue-800' : 'bg-gray-700 text-gray-300 border border-gray-600'; ?> uppercase">
                                        <?php echo isset($game_names[$server['game']]) ? $game_names[$server['game']] : htmlspecialchars($server['game']); ?>
                                    </span>
                                </td>
                                
                                <td data-label="<?php echo __('table_name'); ?>" class="px-6 py-4 break-words min-w-[200px] max-w-[350px]">
                                    <div class="text-sm font-medium text-white tracking-wide leading-tight mb-1"><?php echo parseGameColors($server['name'], $server['game']); ?></div>
                                    <div class="text-xs text-gray-500">
                                        <?php 
                                            if ($server['game'] === 'discord') echo __('discord_community'); 
                                            else echo htmlspecialchars($server['map'] ?? __('no_data')); 
                                        ?>
                                    </div>
                                </td>
                                
                                <td data-label="<?php echo __('table_ip_invite'); ?>" class="px-6 py-4 text-sm text-gray-300 font-mono break-all max-w-[200px]">
                                    <?php 
                                        if ($server['game'] === 'discord') {
                                            echo "<span class='text-blue-400'>" . htmlspecialchars($server['invite_link']) . "</span>";
                                        } else {
                                            echo htmlspecialchars($server['ip'] . ':' . $server['port']); 
                                        }
                                    ?>
                                </td>
                                <td data-label="<?php echo __('table_players'); ?>" class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-300">
                                    <span class="font-bold <?php echo $server['game'] === 'discord' ? 'text-green-400' : ''; ?>">
                                         <?php 
                                             $display_players = $server['players'];
                                             if ($server['status'] && in_array($server['game'], ['mw2', 'mw3'])) {
                                                 if ($display_players <= 10) {
                                                     $display_players = 10;
                                                 }
                                             }
                                             echo $display_players;
                                         ?>
                                     </span> 
                                    / 
                                    <span class="text-gray-500">
                                        <?php echo $server['max_players']; ?>
                                    </span>
                                </td>
                                <td data-label="<?php echo __('table_status'); ?>" class="px-6 py-4 whitespace-nowrap text-center">
                                    <?php if ($server['status']): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-900/50 text-green-400 border border-green-800">
                                            <?php echo __('status_online'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-900/50 text-red-400 border border-red-800">
                                            <?php echo __('status_offline'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="<?php echo __('table_action'); ?>" class="px-6 py-4 whitespace-nowrap text-center">
                                
                                <?php if ($server['game'] === 'discord'): ?>
                                    <a href="<?php echo htmlspecialchars($server['invite_link']); ?>" target="_blank" onclick="event.stopPropagation();"
                                       class="inline-flex items-center px-3 py-1.5 bg-[#5865F2] hover:bg-[#4752C4] text-white text-xs font-bold rounded shadow-lg shadow-blue-500/30 transition-all transform hover:scale-105">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"/>
                                        </svg>
                                        <?php echo __('action_join'); ?>
                                    </a>
                                <?php elseif (in_array($server['game'], ['mw2', 'mw3', 'bo2'])): ?>
                                    <?php 
                                        $protocol = $server['game'] === 'mw2' ? 'iw4x' : 'plutonium';
                                        $ipPort = htmlspecialchars($server['ip'] . ':' . $server['port']);
                                    ?>
                                    <a href="<?php echo $protocol; ?>://connect/<?php echo $ipPort; ?>" onclick="event.stopPropagation(); copyToClipboard('<?php echo $ipPort; ?>')"
                                       class="inline-flex items-center px-3 py-1.5 bg-yellow-600 hover:bg-yellow-500 text-white text-xs font-bold rounded shadow-lg shadow-yellow-600/30 transition-all transform hover:scale-105">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <?php echo __('action_join_copy'); ?>
                                    </a>
                                <?php elseif (in_array($server['game'], ['minecraft', 'cod2', 'cod4'])): ?>
                                    <button onclick="event.stopPropagation(); copyToClipboard('<?php echo htmlspecialchars($server['ip'] . ':' . $server['port']); ?>')" 
                                       class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded shadow-lg shadow-emerald-500/30 transition-all transform hover:scale-105">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <?php echo __('action_copy_ip'); ?>
                                    </button>
                                <?php else: ?>
                                    <a href="steam://connect/<?php echo htmlspecialchars($server['ip'] . ':' . $server['port']); ?>" onclick="event.stopPropagation();"
                                       class="inline-flex items-center px-3 py-1.5 bg-primary hover:bg-blue-600 text-white text-xs font-bold rounded shadow-lg shadow-blue-500/30 transition-all transform hover:scale-105">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                        <?php echo __('action_join'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            </tr>
                            <!-- Dropdown player list row -->
                            <tr id="players_row_<?php echo $server['id']; ?>" class="hidden bg-dark/30">
                                <td colspan="6" class="px-6 py-4">
                                    <?php                                      $players_data = [];
                                     if ($server['status'] && !empty($server['player_list'])) {
                                         $players_data = json_decode($server['player_list'], true) ?: [];
                                     }
                                     
                                     // For CoD games, fill with bots up to 10 if the number of real players is less
                                     if ($server['status'] && in_array($server['game'], ['cod2', 'cod4', 'mw2', 'mw3'])) {
                                         $target_count = 10;
                                         $real_count = count($players_data);
                                         if ($real_count < $target_count) {
                                             $bot_names = [
                                                 'Raptor', 'Slayer', 'Phantom', 'Viper', 'Specter', 
                                                 'Shadow', 'Ghost', 'Soap', 'Price', 'Gaz', 
                                                 'Roach', 'Grinch', 'Nikto', 'Bale', 'Krueger',
                                                 'Striker', 'Hunter', 'Falcon', 'Apex', 'Titan'
                                             ];
                                             for ($i = $real_count; $i < $target_count; $i++) {
                                                 // Deterministic name selection based on server ID and index,
                                                 // so names do not change on page refresh
                                                 $name_index = ($server['id'] + $i) % count($bot_names);
                                                 $players_data[] = [
                                                     'name' => $bot_names[$name_index] . ' (BOT)',
                                                     'score' => 0,
                                                     'ping' => 999, // Set bot ping to 999, which the frontend will draw as a BOT
                                                     'time' => ''
                                                 ];
                                             }
                                         }
                                     }

                                     // Sort players in descending order by score
                                     if (!empty($players_data) && $server['game'] !== 'discord') {
                                         usort($players_data, function($a, $b) {
                                             $scoreA = isset($a['score']) ? (int)$a['score'] : 0;
                                             $scoreB = isset($b['score']) ? (int)$b['score'] : 0;
                                             if ($scoreA === $scoreB) {
                                                 return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
                                             }
                                             return $scoreB <=> $scoreA;
                                         });
                                     }
                                    
                                    if ($server['game'] === 'discord'):
                                    ?>
                                        <!-- DISCORD MEMBER LIST -->
                                        <div class="max-w-5xl mx-auto">
                                            <h4 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-3 flex items-center">
                                                <i class="fab fa-discord mr-2"></i> <?php echo __('table_discord_status'); ?>
                                            </h4>
                                            <?php if (count($players_data) > 0): ?>
                                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                                    <?php foreach ($players_data as $m): ?>
                                                        <div class="flex items-center justify-between bg-dark/60 border border-gray-800 p-2 rounded-lg text-xs">
                                                            <div class="flex items-center space-x-2">
                                                                <span class="w-2.5 h-2.5 rounded-full <?php 
                                                                    if ($m['status'] === 'online') echo 'bg-green-500 shadow-sm shadow-green-500/50';
                                                                    elseif ($m['status'] === 'dnd') echo 'bg-red-500 shadow-sm shadow-red-500/50';
                                                                    else echo 'bg-yellow-500 shadow-sm shadow-yellow-500/50';
                                                                ?>"></span>
                                                                <span class="font-semibold text-white"><?php echo htmlspecialchars($m['name']); ?></span>
                                                            </div>
                                                            <?php if (!empty($m['activity'])): ?>
                                                                <span class="text-[10px] text-gray-400 bg-gray-900/50 px-2 py-0.5 rounded border border-gray-800 max-w-[150px] truncate" title="<?php echo htmlspecialchars($m['activity']); ?>">
                                                                    <?php echo htmlspecialchars($m['activity']); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <div class="text-[10px] text-gray-500 mt-4 text-right">
                                                    * <?php echo __('discord_max_players_note'); ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-xs text-gray-500 italic text-center py-2"><?php echo __('discord_empty'); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- GAME SERVER PLAYER LIST -->
                                        <div class="max-w-2xl mx-auto overflow-hidden">
                                            <?php if ($server['status'] && count($players_data) > 0): ?>
                                                <div class="border border-gray-800 rounded-lg overflow-hidden">
                                                    <table class="min-w-full divide-y divide-gray-800 text-xs responsive-table">
                                                        <thead class="bg-dark/60">
                                                            <tr>
                                                                <th scope="col" class="px-4 py-2.5 text-left font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('table_player_name'); ?></th>
                                                                <th scope="col" class="px-4 py-2.5 text-center font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('table_player_score'); ?></th>
                                                                <?php if ($server['game'] === 'cs2'): ?>
                                                                    <th scope="col" class="px-4 py-2.5 text-center font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('table_player_time'); ?></th>
                                                                <?php elseif ($server['game'] !== 'minecraft'): ?>
                                                                    <th scope="col" class="px-4 py-2.5 text-center font-semibold text-gray-400 uppercase tracking-wider"><?php echo __('table_player_ping'); ?></th>
                                                                <?php endif; ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-800/50 bg-dark/20">
                                                            <?php foreach ($players_data as $p): ?>
                                                                <tr class="hover:bg-gray-800/30 transition-colors">
                                                                    <td data-label="<?php echo __('table_player_name'); ?>" class="px-4 py-2 text-white font-medium"><?php echo htmlspecialchars($p['name']); ?></td>
                                                                    <td data-label="<?php echo __('table_player_score'); ?>" class="px-4 py-2 text-center text-gray-300 font-mono"><?php echo $p['score']; ?></td>
                                                                    <?php if ($server['game'] === 'cs2'): ?>
                                                                        <td data-label="<?php echo __('table_player_time'); ?>" class="px-4 py-2 text-center text-gray-400 font-mono">
                                                                            <?php 
                                                                                $seconds = (int)$p['time'];
                                                                                $hours = floor($seconds / 3600);
                                                                                $minutes = floor(($seconds % 3600) / 60);
                                                                                if ($hours > 0) {
                                                                                    echo $lang === 'hu' ? sprintf("%dó %dp", $hours, $minutes) : sprintf("%dh %dm", $hours, $minutes);
                                                                                } else {
                                                                                    echo $lang === 'hu' ? sprintf("%dp", $minutes) : sprintf("%dm", $minutes);
                                                                                }
                                                                            ?>
                                                                        </td>
                                                                    <?php elseif ($server['game'] !== 'minecraft'): ?>
                                                                        <td data-label="<?php echo __('table_player_ping'); ?>" class="px-4 py-2 text-center font-mono <?php 
                                                                            if ($p['ping'] == 999 || $p['ping'] == 0) echo 'text-blue-400 font-semibold';
                                                                            elseif ($p['ping'] < 50) echo 'text-green-400';
                                                                            elseif ($p['ping'] < 100) echo 'text-yellow-400';
                                                                            else echo 'text-red-400';
                                                                        ?>">
                                                                            <?php 
                                                                                if ($p['ping'] == 999 || $p['ping'] == 0) {
                                                                                    echo 'BOT';
                                                                                } else {
                                                                                    echo $p['ping'] . ' ms';
                                                                                }
                                                                            ?>
                                                                        </td>
                                                                    <?php endif; ?>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-xs text-gray-500 italic text-center py-2"><?php echo __('server_empty'); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                <?php echo __('no_servers_db'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Media section: Most viewed clips and videos -->
    <?php if ($show_twitch || $show_youtube): ?>
    <div class="mt-12 mb-12 grid grid-cols-1 <?php echo ($show_twitch && $show_youtube) ? 'xl:grid-cols-2' : ''; ?> gap-8">
        
        <?php if ($show_twitch): ?>
        <!-- Twitch Clip Embed -->
        <div class="bg-card shadow-xl rounded-lg border border-gray-800 p-6">
            <h2 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/></svg>
                <?php echo __('section_most_viewed_clip'); ?>
            </h2>
            <?php
            $clip_id = get_cache_value($pdo, 'twitch_clip');
            if (!empty($clip_id)) {
                echo '<div class="relative w-full rounded-xl overflow-hidden shadow-xl" style="padding-top: 56.25%;">';
                echo '<iframe class="absolute top-0 left-0 w-full h-full" src="https://clips.twitch.tv/embed?clip='.htmlspecialchars($clip_id).'&parent='.($_SERVER['HTTP_HOST'] ?? 'localhost').'" frameborder="0" allowfullscreen="true" scrolling="no" loading="lazy"></iframe>';
                echo '</div>';
            } else {
                echo '<p class="text-gray-500 italic mt-4 text-center">' . htmlspecialchars(__('clip_not_cached')) . '</p>';
            }
            ?>
        </div>
        <?php endif; ?>

        <?php if ($show_youtube): ?>
        <!-- YouTube Embed -->
        <div class="bg-card shadow-xl rounded-lg border border-gray-800 p-6">
            <h2 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2 flex items-center">
                <svg class="w-6 h-6 mr-2 text-red-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                <?php echo __('section_most_viewed_yt'); ?>
            </h2>
            <?php
            $mv_video_id = get_cache_value($pdo, 'youtube_most_viewed');
            if (!empty($mv_video_id)) {
                echo '<div class="relative w-full rounded-xl overflow-hidden shadow-xl" style="padding-top: 56.25%;">';
                echo '<iframe class="absolute top-0 left-0 w-full h-full" src="https://www.youtube.com/embed/'.htmlspecialchars($mv_video_id).'" frameborder="0" allowfullscreen loading="lazy"></iframe>';
                echo '</div>';
            } else {
                echo '<p class="text-gray-500 italic mt-4 text-center">' . htmlspecialchars(__('yt_video_not_cached')) . '</p>';
            }
            ?>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

    <!-- Activity statistics section -->
    <?php if ($show_activity && ($show_twitch || $show_youtube)): ?>
    <div class="mb-12 grid grid-cols-1 <?php echo ($show_twitch && $show_youtube) ? 'xl:grid-cols-2' : ''; ?> gap-8">
        
        <?php if ($show_twitch): ?>
        <!-- Twitch top active chatters -->
        <div class="bg-card shadow-xl rounded-lg border border-gray-800 p-6">
            <h2 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2 flex items-center">
                <svg class="w-6 h-6 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714Z"/></svg>
                <?php echo __('twitch_top_chatters'); ?> <span class="text-xs text-gray-500 ml-2 mt-1 font-normal">(<?php echo __('last_30_days'); ?>)</span>
            </h2>
            <?php
            $top_twitch_chatters = [];
            try {
                $stmt = $pdo->query("
                    SELECT username, COUNT(*) as msg_count 
                    FROM twitch_chat_logs 
                    WHERE timestamp >= NOW() - INTERVAL 30 DAY
                    GROUP BY username 
                    ORDER BY msg_count DESC 
                    LIMIT 5
                ");
                $top_twitch_chatters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Suppress error
            }

            if (!empty($top_twitch_chatters)): ?>
                <div class="space-y-3">
                    <?php 
                    $rank = 1;
                    foreach ($top_twitch_chatters as $chatter): 
                        // Different colored badges for different rankings
                        $badge_class = 'bg-gray-800 text-gray-400';
                        if ($rank === 1) $badge_class = 'bg-yellow-500 text-black';
                        elseif ($rank === 2) $badge_class = 'bg-gray-400 text-black';
                        elseif ($rank === 3) $badge_class = 'bg-amber-600 text-white';
                    ?>
                        <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-b-0">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold <?php echo $badge_class; ?>">
                                    <?php echo $rank++; ?>
                                </span>
                                <a href="https://twitch.tv/<?php echo urlencode($chatter['username']); ?>" target="_blank" class="text-gray-300 hover:text-purple-400 transition-colors font-medium">
                                    <?php echo htmlspecialchars($chatter['username']); ?>
                                </a>
                            </div>
                            <span class="text-xs text-gray-500 font-semibold">
                                <?php echo number_format($chatter['msg_count'], 0, ',', ' ') . ' ' . __('messages_count'); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 italic mt-4 text-center"><?php echo __('no_messages_yet'); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($show_youtube): ?>
        <!-- Discord top active members -->
        <div class="bg-card shadow-xl rounded-lg border border-gray-800 p-6">
            <h2 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-2 flex items-center">
                <svg class="w-6 h-6 mr-2 text-indigo-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994.021-.041.001-.09-.041-.106a13.094 13.094 0 0 1-1.873-.894.077.077 0 0 1-.008-.128c.126-.093.252-.19.372-.287a.075.075 0 0 1 .077-.011c3.92 1.793 8.18 1.793 12.061 0a.073.073 0 0 1 .078.009c.12.099.246.195.373.289a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.894.077.077 0 0 1-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.156 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.156-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.156 2.418z"/></svg>
                <?php echo __('discord_top_users'); ?> <span class="text-xs text-gray-500 ml-2 mt-1 font-normal">(<?php echo __('all_time'); ?>)</span>
            </h2>
            <?php
            $top_discord_users = [];
            $discord_cache = get_cache_value($pdo, 'discord_leaderboard');
            if (!empty($discord_cache)) {
                $top_discord_users = json_decode($discord_cache, true);
            }
            
            // Retrieve Discord invite
            $discord_invite = '#';
            try {
                $stmt = $pdo->query("SELECT url FROM social_links WHERE title = 'Discord' LIMIT 1");
                $discord_invite = $stmt->fetchColumn() ?: '#';
            } catch (PDOException $e) {
                // Suppress error
            }

            if (!empty($top_discord_users)): ?>
                <div class="space-y-3">
                    <?php 
                    $rank = 1;
                    foreach ($top_discord_users as $user): 
                        $badge_class = 'bg-gray-800 text-gray-400';
                        if ($rank === 1) $badge_class = 'bg-yellow-500 text-black';
                        elseif ($rank === 2) $badge_class = 'bg-gray-400 text-black';
                        elseif ($rank === 3) $badge_class = 'bg-amber-600 text-white';
                    ?>
                        <div class="flex items-center justify-between py-2 border-b border-gray-800 last:border-b-0">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 flex items-center justify-center rounded-full text-xs font-bold <?php echo $badge_class; ?>">
                                    <?php echo $rank++; ?>
                                </span>
                                <a href="<?php echo htmlspecialchars($discord_invite); ?>" target="_blank" class="text-gray-300 hover:text-indigo-400 transition-colors font-medium">
                                    <?php echo htmlspecialchars($user['name']); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 italic mt-4 text-center"><?php echo __('no_discord_data'); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

</main>

<script>
function toggleServerPlayers(serverId) {
    const row = document.getElementById('players_row_' + serverId);
    if (!row) return;
    
    // Hide all other rows
    document.querySelectorAll('[id^="players_row_"]').forEach(r => {
        if (r.id !== 'players_row_' + serverId) {
            r.classList.add('hidden');
        }
    });
    
    row.classList.toggle('hidden');
}
</script>

<?php require_once 'footer.php'; ?>

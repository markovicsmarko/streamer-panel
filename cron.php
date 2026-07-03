<?php
// Enable errors for testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
require_once 'db.php';
// Database cache helper functions
require_once 'functions.php';

// Security check: from Web only runs with a valid key, always allowed from CLI
if (php_sapi_name() !== 'cli') {
    $key = isset($_GET['key']) ? $_GET['key'] : '';
    if (empty($key) || $key !== CRON_KEY) {
        header('HTTP/1.0 403 Forbidden');
        exit('Access Denied: Invalid or missing Cron Key.');
    }
}

// Start output buffer for admin logging
ob_start();
$cron_results = [];

if (php_sapi_name() !== 'cli') {
    echo "<!DOCTYPE html><html lang='en'><head><title>" . htmlspecialchars(ucfirst(TWITCH_USERNAME)) . " Tracker - Cron Status</title>";
    echo "<style>body { background-color: #0f172a; color: #cbd5e1; font-family: monospace; padding: 24px; line-height: 1.8; max-width: 800px; margin: 0 auto; } strong { color: #f8fafc; } hr { border: 0; border-top: 1px solid #334155; margin: 20px 0; } .success { color: #10b981; } .error { color: #ef4444; } .info { color: #64748b; }</style>";
    echo "</head><body>";
    echo "<h2>" . htmlspecialchars(ucfirst(TWITCH_USERNAME)) . " Tracker Cron Run - " . date('Y-m-d H:i:s') . "</h2>";
    echo "<hr>";
}

// Custom Autoloader for GameQ (supports both test folder and final location)
spl_autoload_register(function ($class) {
    $prefix = 'GameQ\\';
    $base_dir = file_exists(__DIR__ . '/GameQ/') ? __DIR__ . '/GameQ/' : __DIR__ . '/../GameQ/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// 1. Query all servers from the database
try {
    $stmt = $pdo->query("SELECT * FROM servers");
    $servers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error querying the database: " . $e->getMessage());
}

if (empty($servers)) {
    die("No servers in the database to query.");
}

$servers_by_id = [];
foreach ($servers as $s) {
    $servers_by_id[$s['id']] = $s;
}

// Prepare GameQ and separate the servers
$GameQ = new \GameQ\GameQ();
$GameQ->setOption('timeout', 3);

$updateOnline = $pdo->prepare("UPDATE servers SET name = ?, map = ?, status = 1, players = ?, max_players = ?, player_list = ?, last_update = NOW() WHERE id = ?");
$updateOffline = $pdo->prepare("UPDATE servers SET status = 0, player_list = NULL, last_update = NOW() WHERE id = ?");

$has_gameq_servers = false;

foreach ($servers as $server) {
    
    // --- DISCORD LOGIC ---
    if ($server['game'] === 'discord') {
        $link = $server['invite_link'];
        $code = '';
        
        // Extract invite code with regex
        if (preg_match('/(?:discord\.gg\/|discord\.com\/invite\/)([a-zA-Z0-9-]+)/i', $link, $matches)) {
            $code = $matches[1];
        }
        
        if (!empty($code)) {
            // API call (use stream context so PHP does not throw a fatal error)
            $context = stream_context_create(['http' => ['ignore_errors' => true]]);
            $api_url = "https://discord.com/api/v9/invites/{$code}?with_counts=true";
            $response = file_get_contents($api_url, false, $context);
            
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['guild']) && isset($data['approximate_presence_count'])) {
                    // Successful query
                    $real_name = $data['guild']['name'] ?? $server['name'];
                    $players = $data['approximate_presence_count'] ?? 0;
                    $max_players = $data['approximate_member_count'] ?? 0;
                    $guild_id = $data['guild']['id'] ?? DISCORD_GUILD_ID; // Fallback to config ID
                    
                    // Retrieve online members via the widget API
                    $widget_url = "https://discord.com/api/guilds/{$guild_id}/widget.json";
                    $widget_response = file_get_contents($widget_url, false, $context);
                    $player_list_json = null;
                    
                    if ($widget_response) {
                        $widget_data = json_decode($widget_response, true);
                        if (isset($widget_data['members'])) {
                            $online_members = [];
                            foreach ($widget_data['members'] as $m) {
                                $online_members[] = [
                                    'name' => $m['username'],
                                    'status' => $m['status'],
                                    'activity' => $m['game']['name'] ?? ''
                                ];
                            }
                            $player_list_json = json_encode($online_members, JSON_UNESCAPED_UNICODE);
                        }
                    }
                    
                    $updateOnline->execute([$real_name, 'Discord Community', $players, $max_players, $player_list_json, $server['id']]);
                    echo "Discord query: <strong>{$real_name}</strong> -> <span class='success'>{$players} / {$max_players} online</span>. Player list updated.<br>";
                    $cron_results[] = ['name' => "Discord: {$real_name}", 'status' => 'success', 'message' => "{$players} online"];
                    continue;
                }
            }
        }
        
        // If there was an error with Discord, set to offline
        $updateOffline->execute([$server['id']]);
        echo "Discord query <span class='error'>failed (Offline)</span>: <strong>" . htmlspecialchars($server['name']) . "</strong><br>";
        $cron_results[] = ['name' => "Discord: " . $server['name'], 'status' => 'error', 'message' => 'Query failed (Offline)'];
        continue;
      }

      // --- GAMEQ LOGIC ---
    $game_types = [
        'cs2'       => 'csgo',
        'dayz'      => 'dayz',
        'cod2'      => 'cod2',
        'cod4'      => 'cod4',
        'mw2'       => 'codmw2',
        'mw3'       => 'codmw3',
        'minecraft' => 'minecraft'
    ];

    $q_type = isset($game_types[$server['game']]) ? $game_types[$server['game']] : $server['game'];

    $GameQ->addServer([
        'id' => $server['id'],
        'type' => $q_type, 
        'host' => $server['ip'] . ':' . $server['port']
    ]);
    
    $has_gameq_servers = true;
}

// Only start GameQ processing if there is a game server to query
if ($has_gameq_servers) {
    $results = $GameQ->process();

    foreach ($results as $server_id => $data) {
        if ($data['gq_online'] == true) {
            $real_name = $data['gq_hostname'] ?? 'Unknown Server';
            if (empty($real_name) && isset($data['hostname'])) {
                $real_name = $data['hostname'];
            }

            $map = $data['gq_mapname'] ?? 'No data';
            $players = $data['gq_numplayers'] ?? 0;
            $max_players = $data['gq_maxplayers'] ?? 0;
            $server = $servers_by_id[$server_id] ?? null;

            $player_list = [];
            $rcon_success = false;

            // If it is a CoD server and RCON password is set,
            // we try to retrieve the full player and bot list using RCON
            if ($server && in_array($server['game'], ['cod2', 'cod4', 'mw2', 'mw3']) && !empty($server['rcon_password'])) {
                $rcon_response = send_cod_rcon($server['ip'], $server['port'], $server['rcon_password'], 'status');
                if ($rcon_response && strpos($rcon_response, 'Hiba') === false && strpos($rcon_response, 'üres') === false) {
                    $player_list = parse_cod_status_players($rcon_response);
                    $rcon_success = true;
                }
            }

            // If not CoD, or RCON failed, use the GameQ data
            if (!$rcon_success) {
                if (isset($data['players']) && is_array($data['players'])) {
                    foreach ($data['players'] as $p) {
                        if (is_array($p)) {
                            $name = $p['name'] ?? $p['gq_name'] ?? '';
                            $score = $p['score'] ?? $p['gq_score'] ?? 0;
                            $ping = $p['ping'] ?? $p['gq_ping'] ?? 0;
                            $time = $p['time'] ?? $p['gq_time'] ?? '';
                        } else {
                            $name = (string)$p;
                            $score = 0;
                            $ping = 0;
                            $time = '';
                        }
                        if (empty($name)) continue;
                        $player_list[] = [
                            'name' => $name,
                            'score' => (int)$score,
                            'ping' => (int)$ping,
                            'time' => $time
                        ];
                    }
                }
            }
            $player_list_json = !empty($player_list) ? json_encode($player_list, JSON_UNESCAPED_UNICODE) : null;

            $updateOnline->execute([$real_name, $map, $players, $max_players, $player_list_json, $server_id]);
            
            if ($server && in_array($server['game'], ['cod2', 'cod4', 'mw2', 'mw3'])) {
                if (!empty($server['rcon_password'])) {
                    $rcon_status = $rcon_success ? "<span class='success'>Success (RCON)</span>" : "<span class='error'>Failed (RCON error)</span>";
                } else {
                    $rcon_status = "<span class='info'>No password specified (GameQ)</span>";
                }
            } else {
                $rcon_status = "<span class='info'>Not required (GameQ)</span>";
            }
            
            $game_type = $server ? strtoupper($server['game']) : 'UNKNOWN';
            echo "Game server <span class='success'>online</span>: <strong>{$real_name}</strong> ({$game_type}) -> {$players} / {$max_players} players. Map: {$map}. RCON query: {$rcon_status}.<br>";
            $cron_results[] = ['name' => "Server: {$real_name}", 'status' => 'success', 'message' => "{$players} players"];
        } else {
            $updateOffline->execute([$server_id]);
            $server = $servers_by_id[$server_id] ?? null;
            $server_name = $server ? $server['name'] : 'Unknown';
            echo "Game server <span class='error'>offline</span>: <strong>{$server_name}</strong> -> Query failed.<br>";
            $cron_results[] = ['name' => "Server: {$server_name}", 'status' => 'error', 'message' => 'Offline'];
        }
    }
}

// --- YOUTUBE CACHE LOGIC (RATE LIMITED: EVERY 3 HOURS) ---
$last_yt_run = (int)get_cache_value($pdo, 'last_youtube_api_run');
if (time() - $last_yt_run > 10800) { // 3 hours
    $API_key    = YT_API_KEY;
    $channelID  = YT_CHANNEL_ID;
    $maxResults = 10;
    $search_url = 'https://www.googleapis.com/youtube/v3/search?order=date&part=snippet&type=video&channelId='.$channelID.'&maxResults='.$maxResults.'&key='.$API_key;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $search_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $search_json = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $yt_latest_success = false;
    if ($http_code == 200 && $search_json) {
        $searchList = json_decode($search_json);
        if (!empty($searchList->items)) {
            $videoIds = [];
            foreach ($searchList->items as $item) {
                if (isset($item->id->videoId)) {
                    $videoIds[] = $item->id->videoId;
                }
            }
            
            if (!empty($videoIds)) {
                $videos_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails,liveStreamingDetails&id=' . implode(',', $videoIds) . '&key=' . $API_key;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $videos_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $videos_json = curl_exec($ch);
                curl_close($ch);
                
                $videosList = json_decode($videos_json);
                if (!empty($videosList->items)) {
                    foreach ($videoIds as $vid_id) {
                        $videoData = null;
                        foreach ($videosList->items as $v) {
                            if ($v->id === $vid_id) {
                                $videoData = $v;
                                break;
                            }
                        }
                        
                        if ($videoData) {
                            if (isset($videoData->liveStreamingDetails)) continue;
                            if (isset($videoData->contentDetails->duration)) {
                                $duration = $videoData->contentDetails->duration;
                                $seconds = 0;
                                if (preg_match('/P(?:(\d+)D)?T?(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?/', $duration, $matches)) {
                                    $days    = (isset($matches[1]) && $matches[1] !== '') ? (int)$matches[1] : 0;
                                    $hours   = (isset($matches[2]) && $matches[2] !== '') ? (int)$matches[2] : 0;
                                    $minutes = (isset($matches[3]) && $matches[3] !== '') ? (int)$matches[3] : 0;
                                    $secs    = (isset($matches[4]) && $matches[4] !== '') ? (int)$matches[4] : 0;
                                    $seconds = ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $secs;
                                }
                                if ($seconds > 0 && $seconds <= 65) continue;
                            }
                            
                            set_cache_value($pdo, 'youtube_video', $vid_id);
                            echo "Latest YouTube video successfully saved to the database: " . $vid_id . "<br>";
                            $cron_results[] = ['name' => 'YouTube (Latest)', 'status' => 'success', 'message' => 'Updated'];
                            $yt_latest_success = true;
                            break;
                        }
                    }
                }
            }
        }
    }
    
    if (!$yt_latest_success) {
        echo "YouTube Latest video query error! HTTP code: " . $http_code . "<br>";
        $cron_results[] = ['name' => 'YouTube (Latest)', 'status' => 'error', 'message' => 'Query failed'];
    }

    // --- YOUTUBE MOST VIEWED CACHE LOGIC ---
    $url_most_viewed = 'https://www.googleapis.com/youtube/v3/search?order=viewCount&part=snippet&channelId='.$channelID.'&maxResults=1&type=video&key='.$API_key;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_most_viewed);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $yt_mv_json = curl_exec($ch);
    $http_code_mv = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $yt_mv_success = false;
    if ($http_code_mv == 200 && $yt_mv_json) {
        $videoList_mv = json_decode($yt_mv_json);
        if (!empty($videoList_mv->items)) {
            foreach ($videoList_mv->items as $item) {
                if (isset($item->id->videoId)) {
                    set_cache_value($pdo, 'youtube_most_viewed', $item->id->videoId);
                    echo "YouTube Most Viewed video successfully saved to the database: " . $item->id->videoId . "<br>";
                    $cron_results[] = ['name' => 'YouTube (Most Viewed)', 'status' => 'success', 'message' => 'Updated'];
                    $yt_mv_success = true;
                    break;
                }
            }
        }
    }
    
    if (!$yt_mv_success) {
        echo "YouTube Most Viewed video error! HTTP code: " . $http_code_mv . "<br>";
        $cron_results[] = ['name' => 'YouTube (Most Viewed)', 'status' => 'error', 'message' => 'Query failed'];
    }

    if ($yt_latest_success && $yt_mv_success) {
        set_cache_value($pdo, 'youtube_api_last_success', time());
    } else {
        set_cache_value($pdo, 'youtube_api_last_error', time());
    }
    set_cache_value($pdo, 'last_youtube_api_run', time());
} else {
    echo "YouTube API call skipped (limitation: runs every 3 hours).<br>";
    $cron_results[] = ['name' => 'YouTube API', 'status' => 'info', 'message' => 'Skipped (Quota protection)'];
}

// --- TWITCH MOST VIEWED CLIP LOGIC (RATE LIMITED: EVERY 3 HOURS) ---
$last_twitch_run = (int)get_cache_value($pdo, 'last_twitch_api_run');
if (time() - $last_twitch_run > 10800) { // 3 hours
    $twitch_client_id = TWITCH_CLIENT_ID;
    $twitch_client_secret = TWITCH_CLIENT_SECRET;
    $twitch_username = TWITCH_USERNAME;

    if (!empty($twitch_client_id) && !empty($twitch_client_secret) && strpos($twitch_client_id, 'IDE_') === false) {
        $twitch_success = false;
        // 1. Get token
        $token_url = "https://id.twitch.tv/oauth2/token?client_id={$twitch_client_id}&client_secret={$twitch_client_secret}&grant_type=client_credentials";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $token_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $token_resp = curl_exec($ch);
        curl_close($ch);
        
        $token_data = json_decode($token_resp, true);
        if (isset($token_data['access_token'])) {
            $access_token = $token_data['access_token'];
            
            // 2. Get Broadcaster ID
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.twitch.tv/helix/users?login={$twitch_username}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Client-ID: {$twitch_client_id}",
                "Authorization: Bearer {$access_token}"
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $user_resp = curl_exec($ch);
            curl_close($ch);
            
            $user_data = json_decode($user_resp, true);
            if (isset($user_data['data'][0]['id'])) {
                $broadcaster_id = $user_data['data'][0]['id'];
                
                // 3. Retrieve most viewed clip
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://api.twitch.tv/helix/clips?broadcaster_id={$broadcaster_id}&first=1");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    "Client-ID: {$twitch_client_id}",
                    "Authorization: Bearer {$access_token}"
                ]);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $clip_resp = curl_exec($ch);
                curl_close($ch);
                
                $clip_data = json_decode($clip_resp, true);
                if (isset($clip_data['data'][0]['id'])) {
                    $clip_id = $clip_data['data'][0]['id'];
                    set_cache_value($pdo, 'twitch_clip', $clip_id);
                    echo "Twitch Most Viewed clip successfully saved to the database: " . $clip_id . "<br>";
                    $cron_results[] = ['name' => 'Twitch (Clip)', 'status' => 'success', 'message' => 'Updated'];
                    $twitch_success = true;
                }
            }
        }
        if (!$twitch_success) {
            echo "Twitch Clip query error!<br>";
            $cron_results[] = ['name' => 'Twitch (Clip)', 'status' => 'error', 'message' => 'API error'];
        }
    } else {
        echo "Twitch API not configured in config.php, skipping.<br>";
        $cron_results[] = ['name' => 'Twitch (Clip)', 'status' => 'error', 'message' => 'Not configured'];
    }
    if ($twitch_success) {
        set_cache_value($pdo, 'twitch_api_last_success', time());
    } else {
        set_cache_value($pdo, 'twitch_api_last_error', time());
    }
    set_cache_value($pdo, 'last_twitch_api_run', time());
} else {
    echo "Twitch Clip API call skipped (limitation: runs every 3 hours).<br>";
    $cron_results[] = ['name' => 'Twitch (Clip)', 'status' => 'info', 'message' => 'Skipped (Quota protection)'];
}

// --- TWITCH CHAT BOT WATCHDOG & CLEANUP ---
try {
    // 1. Delete Twitch chat logs older than 30 days
    $pdo->exec("DELETE FROM twitch_chat_logs WHERE timestamp < NOW() - INTERVAL 30 DAY");
    echo "Old Twitch chat logs cleaned.<br>";
    $cron_results[] = ['name' => 'DB Cleanup', 'status' => 'success', 'message' => 'Old logs deleted'];
} catch (PDOException $e) {
    echo "Twitch logs cleanup failed: " . $e->getMessage() . "<br>";
    $cron_results[] = ['name' => 'DB Cleanup', 'status' => 'error', 'message' => 'Error occurred'];
}

// 2. Watchdog: check if the bot background process is running
$bot_last_ping = (int)get_cache_value($pdo, 'twitch_bot_last_ping');
if (time() - $bot_last_ping > 180) { // If it hasn't pinged for more than 3 minutes
    $script_path = __DIR__ . '/twitch_bot.php';
    
    // Check if exec is enabled on the server
    $exec_disabled = false;
    if (!function_exists('exec')) {
        $exec_disabled = true;
    } else {
        $disabled_funcs = explode(',', ini_get('disable_functions'));
        $disabled_funcs = array_map('trim', array_map('strtolower', $disabled_funcs));
        if (in_array('exec', $disabled_funcs)) {
            $exec_disabled = true;
        }
    }

    if ($exec_disabled) {
        echo "<span class='info'>Twitch bot not running.</span> Since the <code>exec()</code> function is disabled on your server, starting it in the background is not possible from the web interface.<br>";
        echo "<strong>Recommendation:</strong> To start and run the Twitch bot automatically, set up a cPanel Cron job (recommended e.g., every 5 minutes): <br>";
        echo "<code>*/5 * * * * php -q " . htmlspecialchars($script_path) . " >/dev/null 2>&1</code><br>";
        $cron_results[] = ['name' => 'Twitch Bot', 'status' => 'error', 'message' => 'Not running (exec disabled)'];
    } else {
        echo "Twitch bot not running, starting in the background...<br>";
        $cron_results[] = ['name' => 'Twitch Bot', 'status' => 'error', 'message' => 'Not running (Restarted)'];
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows background startup
            if (function_exists('popen')) {
                pclose(popen("start /B php -q " . escapeshellarg($script_path), "r"));
            }
        } else {
            // Linux background startup
            @exec("php -q " . escapeshellarg($script_path) . " > /dev/null 2>&1 &");
        }
    }
} else {
    echo "Twitch bot online (last ping: " . (time() - $bot_last_ping) . " seconds ago).<br>";
    $cron_results[] = ['name' => 'Twitch Bot', 'status' => 'success', 'message' => 'Running (Online)'];
}

// --- DISCORD LEADERBOARD CACHE (ENGAU.GE) ---
$discord_server_id = DISCORD_GUILD_ID;
$discord_api_url = "https://engau.ge/api/v1/servers/{$discord_server_id}/leaderboard?start=0&limit=10";

echo "Retrieving Discord (engau.ge) leaderboard...<br>";
$context = stream_context_create(['http' => ['timeout' => 5, 'ignore_errors' => true]]);
$discord_resp = @file_get_contents($discord_api_url, false, $context);

if ($discord_resp) {
    $discord_data = json_decode($discord_resp, true);
    if (is_array($discord_data)) {
        $filtered_users = [];
        $streamer_id = STREAMER_DISCORD_ID; // Streamer's Discord ID
        
        foreach ($discord_data as $user) {
            $user_id = $user['id'] ?? '';
            $username = $user['name'] ?? '';
            
            // Filter out the streamer by ID and name (for safety)
            if ($user_id === $streamer_id || strtolower($username) === strtolower(TWITCH_USERNAME)) {
                continue;
            }
            
            $filtered_users[] = [
                'name' => $username,
                'id' => $user_id
            ];
            
            // Csak a Top 5 kell
            if (count($filtered_users) >= 5) {
                break;
            }
        }
        
        set_cache_value($pdo, 'discord_leaderboard', json_encode($filtered_users, JSON_UNESCAPED_UNICODE));
        echo "Discord leaderboard successfully updated and cached.<br>";
        $cron_results[] = ['name' => 'Discord Leaderboard', 'status' => 'success', 'message' => 'Updated'];
    } else {
        echo "Error: Discord API response is not valid JSON.<br>";
        $cron_results[] = ['name' => 'Discord Leaderboard', 'status' => 'error', 'message' => 'API error (JSON)'];
    }
} else {
    echo "Error: Failed to reach Discord (engau.ge) API.<br>";
    $cron_results[] = ['name' => 'Discord Leaderboard', 'status' => 'error', 'message' => 'Unreachable API'];
}

// Save the exact cron execution time to the cache
set_cache_value($pdo, 'last_cron_run', time());

echo "<br><strong>The query and caching processes completed successfully!</strong>";

if (php_sapi_name() !== 'cli') {
    echo "</body></html>";
}

// Pufferelt kimenet mentése az adatbázisba az admin felület részére
$cron_output_log = ob_get_clean();
set_cache_value($pdo, 'last_cron_log', $cron_output_log);
set_cache_value($pdo, 'last_cron_results', json_encode($cron_results, JSON_UNESCAPED_UNICODE));

// Kiírjuk a kimenetet, hogy a közvetlen meghívás továbbra is látszódjon
echo $cron_output_log;
?>

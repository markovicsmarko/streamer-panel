<?php
require_once 'db.php';

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];
$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

// If the test folder is in the URL, we strip or handle it (but most often it will run from the root)
$openid_url = 'https://steamcommunity.com/openid/login';
$return_url = $protocol . $domainName . $_SERVER['PHP_SELF'];
$realm_url = $protocol . $domainName . $path . '/';

// 1. REDIRECT TO STEAM
if (empty($_GET['openid_mode'])) {
    $params = [
        'openid.ns' => 'http://specs.openid.net/auth/2.0',
        'openid.mode' => 'checkid_setup',
        'openid.return_to' => $return_url,
        'openid.realm' => $realm_url,
        'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
        'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select'
    ];
    header('Location: ' . $openid_url . '?' . http_build_query($params));
    exit;

// 2. IF THE USER CANCELS LOGIN
} elseif ($_GET['openid_mode'] == 'cancel') {
    die(__('steam_cancel'));

// 3. STEAM REDIRECTED TO YOUR SITE WITH DATA
} else {
    // Check Steam signature (security)
    $params = [
        'openid.assoc_handle' => $_GET['openid_assoc_handle'],
        'openid.signed' => $_GET['openid_signed'],
        'openid.sig' => $_GET['openid_sig'],
        'openid.ns' => 'http://specs.openid.net/auth/2.0',
        'openid.mode' => 'check_authentication',
    ];

    $signed = explode(',', $_GET['openid_signed']);
    foreach ($signed as $item) {
        $val = $_GET['openid_' . str_replace('.', '_', $item)];
        $params['openid.' . $item] = $val;
    }

    $ch = curl_init($openid_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    
    // SECURITY: Enable SSL verification
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    $result = curl_exec($ch);
    curl_close($ch);

    // If verification is successful
    if (preg_match("/is_valid\s*:\s*true/i", $result)) {
        preg_match("/^https?:\/\/steamcommunity\.com\/openid\/id\/(7[0-9]{15,25}+)$/", $_GET['openid_claimed_id'], $matches);
        $steam_id = $matches[1];

        // Retrieve player data using the Steam API (fetched over SSL for security)
        $api_url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key={$steam_api_key}&steamids={$steam_id}";
        
        $ch_api = curl_init($api_url);
        curl_setopt($ch_api, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_api, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch_api, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($ch_api);
        curl_close($ch_api);
        
        $data = json_decode($response, true);
        
        if (isset($data['response']['players'][0])) {
            $player = $data['response']['players'][0];
            $username = $player['personaname'];
            $avatar = $player['avatarfull'];

            // ADMIN ROLE PROTECTION: Check if user already exists and if they are banned
            $stmtCheck = $pdo->prepare("SELECT role, is_banned, perm_links, perm_servers, perm_rcon, perm_users, perm_setup FROM users WHERE steam_id = ?");
            $stmtCheck->execute([$steam_id]);
            $existing_user = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing_user && $existing_user['is_banned'] == 1) {
                die("Hiba: Ez a fiók ki lett tiltva a rendszerről!");
            }

            $existing_role = $existing_user ? $existing_user['role'] : null;

            // If the main admin logs in, they will definitely be sadmin (superadmin).
            // If they were already sadmin/admin in the database, keep that role.
            if ($steam_id === $admin_steam_id) {
                $role = 'sadmin';
            } elseif ($existing_role === 'sadmin' || $existing_role === 'admin') {
                $role = $existing_role;
            } else {
                $role = 'user';
            }

            // Save/Update in the database
            $stmt = $pdo->prepare("
                INSERT INTO users (steam_id, username, avatar, role) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE username = ?, avatar = ?, role = ?
            ");
            $stmt->execute([$steam_id, $username, $avatar, $role, $username, $avatar, $role]);

            // Save to Session
            $_SESSION['steam_id'] = $steam_id;
            $_SESSION['username'] = $username;
            $_SESSION['avatar'] = $avatar;
            $_SESSION['role'] = $role;
            
            // Also save permissions
            $_SESSION['perm_links'] = $existing_user ? (int)$existing_user['perm_links'] : ($role === 'sadmin' ? 1 : 0);
            $_SESSION['perm_servers'] = $existing_user ? (int)$existing_user['perm_servers'] : ($role === 'sadmin' ? 1 : 0);
            $_SESSION['perm_rcon'] = $existing_user ? (int)$existing_user['perm_rcon'] : ($role === 'sadmin' ? 1 : 0);
            $_SESSION['perm_users'] = $existing_user ? (int)$existing_user['perm_users'] : ($role === 'sadmin' ? 1 : 0);
            $_SESSION['perm_setup'] = $existing_user ? (int)$existing_user['perm_setup'] : ($role === 'sadmin' ? 1 : 0);

            // Done! Back to home page.
            header('Location: index.php');
            exit;
        } else {
            die(__('steam_api_error'));
        }
    } else {
        die(__('steam_auth_error'));
    }
}
?>

<?php
require_once 'db.php';
require_once 'functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['steam_id']) || !has_permission('perm_rcon')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Hozzáférés megtagadva! Nincs jogosultságod az RCON parancsok küldéséhez.'
    ]);
    exit;
}

// 2. CSRF token validation
$token = $_POST['csrf_token'] ?? '';
if (empty($token) || $token !== ($_SESSION['csrf_token'] ?? '')) {
    echo json_encode([
        'status' => 'error',
        'message' => 'CSRF biztonsági token hiba! Kérjük frissítsd az oldalt.'
    ]);
    exit;
}

// 3. Read parameters
$server_id = isset($_POST['server_id']) ? (int)$_POST['server_id'] : 0;
$command = isset($_POST['command']) ? trim($_POST['command']) : '';

if ($server_id <= 0 || empty($command)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Hiányzó paraméterek! Válassz szervert és adj meg parancsot.'
    ]);
    exit;
}

// 4. Retrieve server data and RCON password from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM servers WHERE id = ?");
    $stmt->execute([$server_id]);
    $server = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Adatbázis hiba: ' . $e->getMessage()
    ]);
    exit;
}

if (!$server) {
    echo json_encode([
        'status' => 'error',
        'message' => 'A megadott szerver nem található az adatbázisban!'
    ]);
    exit;
}

// Security: only allow for supported games
$supported_games = ['cod2', 'cod4', 'mw2', 'mw3', 'cs2', 'dayz'];
if (!in_array($server['game'], $supported_games)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Ez a játék típus nem támogatja az RCON parancsokat!'
    ]);
    exit;
}

// Check if RCON password is set
if (empty($server['rcon_password'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'A szerverhez nincs beállítva RCON jelszó az admin panelben!'
    ]);
    exit;
}

// 5. Send RCON command
if ($server['game'] === 'cs2') {
    $rcon = new SourceRcon($server['ip'], $server['port'], $server['rcon_password']);
    $response = $rcon->sendCommand($command);
} elseif ($server['game'] === 'dayz') {
    $rcon = new BattlEyeRcon($server['ip'], $server['port'], $server['rcon_password']);
    $response = $rcon->sendCommand($command);
} else {
    $response = send_cod_rcon($server['ip'], $server['port'], $server['rcon_password'], $command);
}

// UTF-8 conversion (CoD servers often send responses in Windows-1250/ISO-8859-2 encoding)
if (!mb_check_encoding($response, 'UTF-8')) {
    $response = mb_convert_encoding($response, 'UTF-8', 'ISO-8859-2, CP1250, auto');
}

// Format color codes to HTML for the console
if ($server['game'] === 'cs2') {
    $formatted_response = htmlspecialchars($response);
} else {
    $formatted_response = parseGameColors($response, $server['game']);
}

echo json_encode([
    'status' => 'success',
    'raw' => $response,
    'message' => nl2br($formatted_response),
    'timestamp' => date('H:i:s')
]);
exit;
?>

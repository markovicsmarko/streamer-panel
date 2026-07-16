<?php
// Security protection: disable direct browser access
if (basename($_SERVER['SCRIPT_FILENAME']) === 'functions.php') {
    header('HTTP/1.0 403 Forbidden');
    exit('Access Denied');
}

// --- COLOR CODE DECODER ---
function parseGameColors($text, $game) {
    // Security filtering before formatting
    $text = htmlspecialchars($text ?? '');

    // Call of Duty (^0-^9) formatting
    if (in_array($game, ['cod2', 'cod4', 'mw2', 'mw3'])) {
        $colors = [
            '0' => '#000000', '1' => '#ff3333', '2' => '#33ff33', '3' => '#ffff33',
            '4' => '#3333ff', '5' => '#33ffff', '6' => '#ff33ff', '7' => '#ffffff',
            '8' => '#aaaaaa', '9' => '#777777'
        ];
        $parts = explode('^', $text);
        $out = array_shift($parts); // The first part has no color code
        foreach ($parts as $part) {
            if (strlen($part) > 0 && isset($colors[$part[0]])) {
                $color = $colors[$part[0]];
                $out .= '<span style="color: ' . $color . '; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);">' . substr($part, 1) . '</span>';
            } else {
                $out .= '^' . $part;
            }
        }
        return $out;
    }

    // Minecraft (§0-§f and formatting)
    if ($game === 'minecraft') {
        // Unify basic Minecraft codes
        $text = str_replace('&', '§', $text);
        $colors = [
            '0' => '#000000', '1' => '#0000AA', '2' => '#00AA00', '3' => '#00AAAA',
            '4' => '#AA0000', '5' => '#AA00AA', '6' => '#FFAA00', '7' => '#AAAAAA',
            '8' => '#555555', '9' => '#5555FF', 'a' => '#55FF55', 'b' => '#55FFFF',
            'c' => '#FF5555', 'd' => '#FF55FF', 'e' => '#FFFF55', 'f' => '#FFFFFF'
        ];
        $formats = [
            'l' => 'font-weight: bold;',
            'm' => 'text-decoration: line-through;',
            'n' => 'text-decoration: underline;',
            'o' => 'font-style: italic;'
        ];

        $parts = explode('§', $text);
        $out = array_shift($parts);
        $current_styles = [];

        foreach ($parts as $part) {
            if (strlen($part) == 0) continue;
            $code = strtolower($part[0]);
            $rest = substr($part, 1);

            if ($code === 'r') {
                $current_styles = []; // Reset formatting
            } elseif (isset($colors[$code])) {
                $current_styles['color'] = 'color: ' . $colors[$code] . '; text-shadow: 1px 1px 2px rgba(0,0,0,0.8);';
            } elseif (isset($formats[$code])) {
                $current_styles[$code] = $formats[$code];
            }

            if (!empty($current_styles)) {
                $style_attr = implode(' ', $current_styles);
                $out .= '<span style="' . $style_attr . '">' . $rest . '</span>';
            } else {
                $out .= $rest;
            }
        }
        return $out;
    }

    // If none match, return original safe text
    return $text;
}

// --- DATABASE-BASED CACHE FUNCTIONS ---

/**
 * Retrieves the cached value from the database.
 *
 * @param PDO $pdo The database connection object
 * @param string $key The cache key (e.g. 'youtube_video')
 * @return string The saved value or an empty string if it does not exist
 */
function get_cache_value($pdo, $key) {
    try {
        $stmt = $pdo->prepare("SELECT value FROM site_cache WHERE key_name = ?");
        $stmt->execute([$key]);
        return $stmt->fetchColumn() ?: '';
    } catch (PDOException $e) {
        return '';
    }
}

/**
 * Saves or updates the cache value in the database.
 *
 * @param PDO $pdo The database connection object
 * @param string $key The cache key
 * @param string $value The value to save
 */
function set_cache_value($pdo, $key, $value) {
    try {
        $stmt = $pdo->prepare("INSERT INTO site_cache (key_name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = ?");
        $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        // Suppress error
    }
}

// --- DATABASE-BASED SYSTEM SETTINGS ---

/**
 * Retrieves the setting value from the site_settings table.
 *
 * @param PDO $pdo The database connection object
 * @param string $key The setting key (e.g. 'show_twitch')
 * @param string $default Default value if the key does not exist
 * @return string The setting value
 */
function get_setting_value($pdo, $key, $default = '') {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Sets the setting value in the site_settings table.
 *
 * @param PDO $pdo The database connection object
 * @param string $key The setting key
 * @param string $value The new setting value
 */
function set_setting_value($pdo, $key, $value) {
    try {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        // Suppress error
    }
}

/**
 * Checks if the logged in user has permission for the specified action.
 *
 * @param string $permission The permission name (e.g. 'perm_links', 'perm_servers', 'perm_rcon', 'perm_users', 'perm_setup')
 * @return bool True if they have permission, false otherwise
 */
function has_permission($permission) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    // The superadmin (sadmin) gets permission for everything
    if ($_SESSION['role'] === 'sadmin') {
        return true;
    }
    // The admin only accesses the configured permissions
    if ($_SESSION['role'] === 'admin') {
        return isset($_SESSION[$permission]) && $_SESSION[$permission] == 1;
    }
    return false;
}

/**
 * Quake 3 based Call of Duty game server RCON command sender
 */
function send_cod_rcon($ip, $port, $password, $command) {
    $socket = @fsockopen("udp://$ip", $port, $errno, $errstr, 2);
    if (!$socket) {
        return "Hiba a kapcsolódáskor: $errstr ($errno)";
    }

    $packet = "\xff\xff\xff\xffrcon \"" . $password . "\" " . $command . "\n";
    @fwrite($socket, $packet);
    @stream_set_timeout($socket, 2);
    
    $response = '';
    $info = ['timed_out' => false];
    
    do {
        $data = @fread($socket, 8192);
        if ($data === false || $data === '') {
            break;
        }
        
        if (substr($data, 0, 4) === "\xff\xff\xff\xff") {
            $header_len = 4;
            if (substr($data, 4, 6) === "print\n") {
                $header_len += 6;
            }
            $response .= substr($data, $header_len);
        } else {
            $response .= $data;
        }
        
        $info = @stream_get_meta_data($socket);
    } while (!$info['timed_out'] && !empty($data));

    @fclose($socket);

    if (empty($response)) {
        if ($info['timed_out']) {
            return "Hiba: A szerver nem válaszolt az időkorláton belül.";
        }
        return "A szerver válasza üres.";
    }

    return trim($response);
}

/**
 * Process RCON status command output to extract players (CoD)
 */
function parse_cod_status_players($status_text) {
    $players = [];
    if (preg_match_all('/^\s*(\d+)\s+(-?\d+)\s+(\S+)\s+(\S+)\s+(\S+)/m', $status_text, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $name = $match[5];
            $name = preg_replace('/\^[0-9]/', '', $name); // Remove color codes from the name
            $name = trim($name, '"');
            
            $ping = $match[3];
            if (!is_numeric($ping)) {
                $ping = 0;
            }
            
            $players[] = [
                'name'  => $name,
                'score' => (int)$match[2],
                'ping'  => (int)$ping,
                'time'  => ''
            ];
        }
    }
    return $players;
}

/**
 * Source Engine (TCP based) RCON client class for CS2 servers
 */
class SourceRcon {
    private $socket;
    private $ip;
    private $port;
    private $password;
    private $requestId = 1;

    const SERVERDATA_AUTH = 3;
    const SERVERDATA_AUTH_RESPONSE = 2;
    const SERVERDATA_EXECCOMMAND = 2;
    const SERVERDATA_RESPONSE_VALUE = 0;

    public function __construct($ip, $port, $password) {
        $this->ip = $ip;
        $this->port = $port;
        $this->password = $password;
    }

    public function sendCommand($command) {
        $this->socket = @fsockopen("tcp://{$this->ip}", $this->port, $errno, $errstr, 3);
        if (!$this->socket) {
            return "Hiba a kapcsolódáskor: $errstr ($errno)";
        }
        @stream_set_timeout($this->socket, 3);

        if (!$this->writePacket(self::SERVERDATA_AUTH, $this->password)) {
            @fclose($this->socket);
            return "Hiba: Sikertelen hitelesítési csomag küldése.";
        }

        $response = $this->readPacket();
        if ($response['type'] == self::SERVERDATA_RESPONSE_VALUE) {
            $response = $this->readPacket();
        }

        if ($response['id'] == -1 || $response['type'] != self::SERVERDATA_AUTH_RESPONSE) {
            @fclose($this->socket);
            return "Hiba: Hibás RCON jelszó!";
        }

        if (!$this->writePacket(self::SERVERDATA_EXECCOMMAND, $command)) {
            @fclose($this->socket);
            return "Hiba: Sikertelen parancs csomag küldése.";
        }

        $result = '';
        $packet = $this->readPacket();
        if ($packet['type'] == self::SERVERDATA_RESPONSE_VALUE) {
            $result .= $packet['body'];
        }

        @fclose($this->socket);
        return trim($result);
    }

    private function writePacket($type, $body) {
        $id = $this->requestId++;
        $packet = pack("V", $id) . pack("V", $type) . $body . "\x00\x00";
        $size = strlen($packet);
        $fullPacket = pack("V", $size) . $packet;

        return @fwrite($this->socket, $fullPacket, strlen($fullPacket));
    }

    private function readPacket() {
        $sizeData = @fread($this->socket, 4);
        if (strlen($sizeData) < 4) {
            return ['id' => -1, 'type' => -1, 'body' => ''];
        }
        $size = unpack("V1size", $sizeData)['size'];
        
        $packetData = '';
        $remaining = $size;
        while ($remaining > 0) {
            $chunk = @fread($this->socket, $remaining);
            if ($chunk === false || $chunk === '') {
                break;
            }
            $packetData .= $chunk;
            $remaining -= strlen($chunk);
        }

        if (strlen($packetData) < 8) {
            return ['id' => -1, 'type' => -1, 'body' => ''];
        }

        $id = unpack("V1id", substr($packetData, 0, 4))['id'];
        $type = unpack("V1type", substr($packetData, 4, 4))['type'];
        $body = substr($packetData, 8, $size - 10);

        return ['id' => $id, 'type' => $type, 'body' => $body];
    }
}

/**
 * BattlEye RCON Protocol Class for DayZ
 */
class BattlEyeRcon {
    private $ip;
    private $port;
    private $password;
    private $socket;
    private $sequence = 0;

    public function __construct($ip, $port, $password) {
        $this->ip = $ip;
        $port = (int)$port;
        // DayZ game port defaults to ending in 02 (e.g., 2302).
        // The corresponding BattlEye RCON port is typically Game Port + 3 (e.g., 2305).
        if ($port % 100 === 2) {
            $port += 3;
        }
        $this->port = $port;
        $this->password = $password;
    }

    private function connect() {
        $this->socket = @fsockopen("udp://{$this->ip}", $this->port, $errno, $errstr, 2);
        if (!$this->socket) {
            return false;
        }
        @stream_set_timeout($this->socket, 2);
        return true;
    }

    private function makePacket($type, $payload = '', $seq = null) {
        if ($type === 0x00) {
            // Login: Type (0x00) + Password
            $body = chr(0xFF) . chr($type) . $payload;
        } else {
            // Command: Type (0x01) + Sequence + Command
            $body = chr(0xFF) . chr($type) . chr($seq) . $payload;
        }
        
        $checksum = crc32($body);
        return "BE" . pack("V", $checksum) . $body;
    }

    public function sendCommand($command) {
        if (!$this->connect()) {
            return "Error connecting.";
        }

        // 1. Authenticate
        $loginPacket = $this->makePacket(0x00, $this->password);
        @fwrite($this->socket, $loginPacket);
        
        $loginTimeout = time() + 2;
        $authenticated = false;
        $authError = "Error: The server did not respond to the login request.";
        
        while (time() < $loginTimeout) {
            $response = @fread($this->socket, 4096);
            if (!$response) {
                break;
            }
            if (strlen($response) >= 9) {
                $type = ord($response[7]);
                $status = ord($response[8]);
                
                if ($type === 0x00) {
                    if ($status === 0x01) {
                        $authenticated = true;
                    } else {
                        $authError = "Error: Authentication failed! Invalid RCON password.";
                    }
                    break;
                } elseif ($type === 0x02) {
                    // ACK any server message received during login
                    $ackPacket = $this->makePacket(0x02, '', $status);
                    @fwrite($this->socket, $ackPacket);
                }
            }
        }
        
        if (!$authenticated) {
            @fclose($this->socket);
            return $authError;
        }

        // 2. Send Command
        $seq = $this->sequence++;
        $cmdPacket = $this->makePacket(0x01, $command, $seq);
        @fwrite($this->socket, $cmdPacket);

        $result = '';
        $timeout = time() + 2;
        
        while (time() < $timeout) {
            $respPacket = @fread($this->socket, 8192);
            if ($respPacket) {
                if (strlen($respPacket) >= 9) {
                    $respType = ord($respPacket[7]);
                    $respSeq = ord($respPacket[8]);
                    
                    if ($respType === 0x01 && $respSeq === $seq) {
                        // This is the command response packet!
                        // In BattlEye protocol, command response payload starts directly at index 9.
                        $result .= substr($respPacket, 9);
                        
                        // Check if there are more fragments
                        $read = [$this->socket];
                        $write = null;
                        $except = null;
                        if (@stream_select($read, $write, $except, 0, 100000) > 0) {
                            continue;
                        } else {
                            break;
                        }
                    } elseif ($respType === 0x02) {
                        // This is an asynchronous server message (chat log, admin login, etc.)
                        // We MUST acknowledge it (ACK) so the server stops re-sending it.
                        // The ACK packet type for a Server Message (0x02) is 0x02.
                        $ackPacket = $this->makePacket(0x02, '', $respSeq);
                        @fwrite($this->socket, $ackPacket);
                        
                        // Check if the command response is already in the queue
                        $read = [$this->socket];
                        $write = null;
                        $except = null;
                        if (@stream_select($read, $write, $except, 0, 100000) > 0) {
                            continue;
                        }
                    }
                }
            } else {
                break;
            }
        }

        @fclose($this->socket);
        return trim($result);
    }
}

/**
 * Parses BattlEye 'players' command output
 */
function parse_battleye_status_players($status_text) {
    $players = [];
    $lines = explode("\n", $status_text);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || 
            strpos($line, 'Players on server') !== false || 
            strpos($line, '---') !== false || 
            strpos($line, 'players total') !== false ||
            preg_match('/^#\s+name/i', $line) ||
            preg_match('/^#\s+id/i', $line)) {
            continue;
        }

        if (preg_match('/^(\d+)\s+(\S+)\s+(?:(-?\d+)\s+)?([a-fA-F0-9]+(?:\([^\)]+\))?)\s+(.+)$/', $line, $m)) {
            $name = trim($m[5]);
            $name = trim($name, '"');
            $ping = (int)$m[3];
            $players[] = [
                'name' => $name,
                'score' => 0,
                'ping' => $ping < 0 ? 0 : $ping,
                'time' => ''
            ];
        }
    }
    return $players;
}

<?php

// ============================================================
// api.php — Snake & Ladder Pro (SQLite edition)
// Tidak perlu MySQL, tidak perlu setup apapun.
// File database dibuat otomatis di folder yang sama.
// ============================================================

// --- KONFIGURASI ---
// Path file SQLite. Ganti ke direktori di luar public_html untuk keamanan.
// Contoh: define('DB_PATH', __DIR__ . '/../data/snakeladder.db');
define('DB_PATH',     __DIR__ . '/snakeladder.db');
define('POLL_TIMEOUT', 20);   // detik max long-polling
define('WAL_MODE',    true);  // Write-Ahead Logging — jauh lebih cepat untuk concurrent reads

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================================
// DATABASE — SQLite, auto-init schema
// ============================================================
function getDB(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    try {
        $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        sendError('SQLite open failed: ' . $e->getMessage(), 500);
    }

    // WAL mode: baca dan tulis bisa bersamaan tanpa saling blokir
    if (WAL_MODE) {
        $pdo->exec('PRAGMA journal_mode=WAL');
        $pdo->exec('PRAGMA synchronous=NORMAL');  // lebih cepat, masih aman
    }
    $pdo->exec('PRAGMA foreign_keys=ON');
    $pdo->exec('PRAGMA busy_timeout=5000');       // tunggu max 5 detik kalau DB terkunci

    // Auto-create tabel jika belum ada (tidak perlu jalankan schema.sql)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS rooms (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            room_code      TEXT    NOT NULL UNIQUE,
            max_players    INTEGER NOT NULL DEFAULT 2,
            current_turn   INTEGER NOT NULL DEFAULT 0,
            board_seed     INTEGER NOT NULL DEFAULT 0,
            last_roll      INTEGER NOT NULL DEFAULT 0,
            last_player    INTEGER NOT NULL DEFAULT 0,
            bonus_turn     INTEGER NOT NULL DEFAULT 0,
            finished_count INTEGER NOT NULL DEFAULT 0,
            status         TEXT    NOT NULL DEFAULT 'waiting',
            updated_at     TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%d %H:%M:%f','now'))
        );

        CREATE TABLE IF NOT EXISTS room_players (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            room_code    TEXT    NOT NULL,
            player_index INTEGER NOT NULL,
            player_name  TEXT    NOT NULL DEFAULT 'Player',
            position     INTEGER NOT NULL DEFAULT 1,
            score        INTEGER NOT NULL DEFAULT 0,
            finish_rank  INTEGER NOT NULL DEFAULT 0,
            is_finished  INTEGER NOT NULL DEFAULT 0,
            UNIQUE(room_code, player_index),
            FOREIGN KEY(room_code) REFERENCES rooms(room_code) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS game_logs (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            room_code    TEXT    NOT NULL,
            player_index INTEGER NOT NULL,
            dice_value   INTEGER NOT NULL,
            pos_before   INTEGER NOT NULL,
            pos_after    INTEGER NOT NULL,
            event_type   TEXT    NOT NULL DEFAULT 'normal',
            created_at   TEXT    NOT NULL DEFAULT (strftime('%Y-%m-%d %H:%M:%f','now')),
            FOREIGN KEY(room_code) REFERENCES rooms(room_code) ON DELETE CASCADE
        );

        CREATE INDEX IF NOT EXISTS idx_room_code ON rooms(room_code);
        CREATE INDEX IF NOT EXISTS idx_rp_room   ON room_players(room_code);
        CREATE INDEX IF NOT EXISTS idx_log_room  ON game_logs(room_code);
    ");

    return $pdo;
}

// Shortcut: timestamp SQLite presisi milidetik (untuk polling)
function now(): string
{
    return (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s.v');
}

// ============================================================
// HELPERS
// ============================================================
function sendJson($data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}
function sendError(string $msg, int $code = 400): void
{
    sendJson(['success' => false, 'error' => $msg], $code);
}
function sendSuccess($data = []): void
{
    sendJson(array_merge(['success' => true], $data));
}
function getInput(): array
{
    $d = json_decode(file_get_contents('php://input'), true);
    return is_array($d) ? $d : [];
}
function generateRoomCode(): string
{
    $db = getDB();
    do {
        $code = (string) random_int(1000, 9999);
        $s = $db->prepare('SELECT id FROM rooms WHERE room_code=? LIMIT 1');
        $s->execute([$code]);
    } while ($s->fetch());
    return $code;
}

// ============================================================
// getRoomData — format lengkap untuk dikirim ke client
// ============================================================
function getRoomData(string $roomCode): ?array
{
    $db = getDB();

    $s = $db->prepare('SELECT * FROM rooms WHERE room_code=? LIMIT 1');
    $s->execute([$roomCode]);
    $room = $s->fetch();
    if (!$room) return null;

    $s2 = $db->prepare('SELECT * FROM room_players WHERE room_code=? ORDER BY player_index ASC');
    $s2->execute([$roomCode]);

    $players = [];
    foreach ($s2->fetchAll() as $p) {
        $players[] = [
            'index'      => (int)  $p['player_index'],
            'name'       =>        $p['player_name'],
            'position'   => (int)  $p['position'],
            'score'      => (int)  $p['score'],
            'finishRank' => (int)  $p['finish_rank'],
            'isFinished' => (bool) $p['is_finished'],
        ];
    }

    return [
        'roomCode'      =>        $room['room_code'],
        'maxPlayers'    => (int)  $room['max_players'],
        'turn'          => (int)  $room['current_turn'],
        'seed'          => (int)  $room['board_seed'],
        'lastRoll'      => (int)  $room['last_roll'],
        'lastPlayer'    => (int)  $room['last_player'],
        'bonusTurn'     => (bool) $room['bonus_turn'],
        'finishedCount' => (int)  $room['finished_count'],
        'status'        =>        $room['status'],
        'updatedAt'     =>        $room['updated_at'],
        'players'       =>        $players,
    ];
}

// ============================================================
// getNextTurn — lewati pemain yang sudah finish
// ============================================================
function getNextTurn(string $roomCode, int $pIdx, bool $bonusTurn, PDO $db): int
{
    if ($bonusTurn) return $pIdx;

    $s = $db->prepare('SELECT player_index, is_finished FROM room_players WHERE room_code=? ORDER BY player_index ASC');
    $s->execute([$roomCode]);
    $all   = $s->fetchAll();
    $count = count($all);

    for ($i = 1; $i <= $count; $i++) {
        $candidate = ($pIdx + $i) % $count;
        $cp = array_values(array_filter($all, fn($p) => (int)$p['player_index'] === $candidate));
        if (!empty($cp) && !(bool)$cp[0]['is_finished']) return $candidate;
    }
    return $pIdx; // semua finish
}

// ============================================================
// ROUTER
// ============================================================
$action = $_GET['action'] ?? '';

switch ($action) {

    // --------------------------------------------------------
    // POST create
    // Body: { playerName, maxPlayers }
    // --------------------------------------------------------
    case 'create':
        $b          = getInput();
        $name       = substr(trim($b['playerName'] ?? 'Player 1'), 0, 50) ?: 'Player 1';
        $maxPlayers = max(2, min(10, (int)($b['maxPlayers'] ?? 2)));
        $seed       = random_int(100000, 999999);
        $roomCode   = generateRoomCode();
        $ts         = now();

        $db = getDB();
        $db->beginTransaction();
        $db->prepare('INSERT INTO rooms (room_code,max_players,board_seed,updated_at) VALUES (?,?,?,?)')
            ->execute([$roomCode, $maxPlayers, $seed, $ts]);
        $db->prepare('INSERT INTO room_players (room_code,player_index,player_name) VALUES (?,0,?)')
            ->execute([$roomCode, $name]);
        $db->commit();

        sendSuccess(['room' => getRoomData($roomCode)]);
        break;

    // --------------------------------------------------------
    // POST join
    // Body: { roomCode, playerName }
    // --------------------------------------------------------
    case 'join':
        $b        = getInput();
        $roomCode = trim($b['roomCode'] ?? '');
        $name     = substr(trim($b['playerName'] ?? 'Player'), 0, 50) ?: 'Player';
        if (!$roomCode) sendError('roomCode wajib diisi.');

        $db = getDB();
        $db->beginTransaction();

        // SQLite tidak punya FOR UPDATE — BEGIN IMMEDIATE mengunci DB untuk write
        // Tapi kita sudah ada busy_timeout=5000 jadi aman
        $s = $db->prepare('SELECT * FROM rooms WHERE room_code=? LIMIT 1');
        $s->execute([$roomCode]);
        $room = $s->fetch();
        if (!$room) {
            $db->rollBack();
            sendError('Room tidak ditemukan.', 404);
        }
        if ($room['status'] === 'finished') {
            $db->rollBack();
            sendError('Room sudah selesai.', 409);
        }

        $s2 = $db->prepare('SELECT COUNT(*) as cnt FROM room_players WHERE room_code=?');
        $s2->execute([$roomCode]);
        $cnt = (int)$s2->fetch()['cnt'];

        if ($cnt >= (int)$room['max_players']) {
            $db->rollBack();
            sendError('Room sudah penuh (' . $room['max_players'] . ' pemain).', 409);
        }

        $newIndex = $cnt;
        $ts       = now();
        $db->prepare('INSERT INTO room_players (room_code,player_index,player_name) VALUES (?,?,?)')
            ->execute([$roomCode, $newIndex, $name]);

        $newStatus = ($cnt + 1 >= (int)$room['max_players']) ? 'playing' : 'waiting';
        $db->prepare('UPDATE rooms SET status=?,updated_at=? WHERE room_code=?')
            ->execute([$newStatus, $ts, $roomCode]);

        $db->commit();
        sendSuccess(['room' => getRoomData($roomCode), 'assignedIndex' => $newIndex]);
        break;

    // --------------------------------------------------------
    // POST start — host paksa mulai sebelum slot penuh
    // Body: { roomCode, playerIndex }
    // --------------------------------------------------------
    case 'start':
        $b        = getInput();
        $roomCode = trim($b['roomCode'] ?? '');
        $pIdx     = (int)($b['playerIndex'] ?? -1);
        if (!$roomCode) sendError('roomCode wajib diisi.');
        if ($pIdx !== 0) sendError('Hanya host yang bisa memulai.', 403);

        $db = getDB();
        $s  = $db->prepare('SELECT * FROM rooms WHERE room_code=? LIMIT 1');
        $s->execute([$roomCode]);
        $room = $s->fetch();
        if (!$room)                        sendError('Room tidak ditemukan.', 404);
        if ($room['status'] !== 'waiting') sendError('Game sudah dimulai.', 409);

        $s2 = $db->prepare('SELECT COUNT(*) as cnt FROM room_players WHERE room_code=?');
        $s2->execute([$roomCode]);
        $cnt = (int)$s2->fetch()['cnt'];
        if ($cnt < 2) sendError('Minimal 2 pemain untuk mulai.', 409);

        $db->prepare("UPDATE rooms SET status='playing',max_players=?,updated_at=? WHERE room_code=?")
            ->execute([$cnt, now(), $roomCode]);

        sendSuccess(['room' => getRoomData($roomCode)]);
        break;

    // --------------------------------------------------------
    // POST roll
    // Body: { roomCode, playerIndex, diceValue }
    // Menentukan bonus turn dan giliran berikutnya.
    // --------------------------------------------------------
    case 'roll':
        $b        = getInput();
        $roomCode = trim($b['roomCode'] ?? '');
        $pIdx     = (int)($b['playerIndex'] ?? -1);
        $dice     = (int)($b['diceValue']   ?? 0);
        if (!$roomCode)             sendError('roomCode wajib diisi.');
        if ($pIdx < 0 || $pIdx > 9) sendError('playerIndex tidak valid.');
        if ($dice < 1 || $dice > 6) sendError('diceValue harus 1-6.');

        $db = getDB();
        $db->beginTransaction();

        $s = $db->prepare('SELECT * FROM rooms WHERE room_code=? LIMIT 1');
        $s->execute([$roomCode]);
        $room = $s->fetch();
        if (!$room) {
            $db->rollBack();
            sendError('Room tidak ditemukan.', 404);
        }
        if ((int)$room['current_turn'] !== $pIdx) {
            $db->rollBack();
            sendError('Bukan giliran Anda.', 403);
        }
        if ($room['status'] !== 'playing') {
            $db->rollBack();
            sendError('Game belum aktif.', 409);
        }

        // Bonus: angka 6 pertama → giliran tetap. Angka 6 lagi saat bonus → tidak bonus lagi.
        $prevBonus  = (bool)$room['bonus_turn'];
        $grantBonus = ($dice === 6 && !$prevBonus);

        $nextTurn = getNextTurn($roomCode, $pIdx, $grantBonus, $db);

        $db->prepare('UPDATE rooms SET last_roll=?,last_player=?,bonus_turn=?,current_turn=?,updated_at=? WHERE room_code=?')
            ->execute([$dice, $pIdx, $grantBonus ? 1 : 0, $nextTurn, now(), $roomCode]);

        $db->commit();
        sendSuccess(['room' => getRoomData($roomCode)]);
        break;

    // --------------------------------------------------------
    // POST move
    // Body: { roomCode, playerIndex, finalPos, diceValue, eventType }
    // Client kirim posisi final setelah animasi selesai.
    // --------------------------------------------------------
    case 'move':
        $b         = getInput();
        $roomCode  = trim($b['roomCode']  ?? '');
        $pIdx      = (int)($b['playerIndex'] ?? -1);
        $finalPos  = (int)($b['finalPos']    ?? 0);
        $dice      = (int)($b['diceValue']   ?? 0);
        $validEvt  = ['normal', 'snake', 'ladder', 'win', 'bonus'];
        $eventType = in_array($b['eventType'] ?? '', $validEvt) ? $b['eventType'] : 'normal';

        if (!$roomCode)              sendError('roomCode wajib diisi.');
        if ($pIdx < 0 || $pIdx > 9) sendError('playerIndex tidak valid.');
        if ($finalPos < 1 || $finalPos > 100) sendError('finalPos harus 1-100.');

        $db = getDB();
        $db->beginTransaction();

        $sr = $db->prepare('SELECT * FROM rooms WHERE room_code=? LIMIT 1');
        $sr->execute([$roomCode]);
        $room = $sr->fetch();
        if (!$room) {
            $db->rollBack();
            sendError('Room tidak ditemukan.', 404);
        }

        $sp = $db->prepare('SELECT * FROM room_players WHERE room_code=? AND player_index=? LIMIT 1');
        $sp->execute([$roomCode, $pIdx]);
        $player = $sp->fetch();
        if (!$player) {
            $db->rollBack();
            sendError('Pemain tidak ditemukan.', 404);
        }

        $posBefore     = (int)$player['position'];
        $isWin         = ($finalPos === 100 && !(bool)$player['is_finished']);
        $finishedCount = (int)$room['finished_count'];
        $allFinished   = false;
        $ts            = now();

        if ($isWin) {
            $finishedCount++;
            $rank     = $finishedCount;
            $maxP     = (int)$room['max_players'];
            $newScore = (int)$player['score'] + max(1, $maxP - $rank + 1);

            $db->prepare('UPDATE room_players SET position=100,finish_rank=?,score=?,is_finished=1 WHERE room_code=? AND player_index=?')
                ->execute([$rank, $newScore, $roomCode, $pIdx]);

            // Cek apakah semua pemain sudah finish
            $sAll = $db->prepare('SELECT COUNT(*) as total, SUM(is_finished) as done FROM room_players WHERE room_code=?');
            $sAll->execute([$roomCode]);
            $counts      = $sAll->fetch();
            $allFinished = ((int)$counts['done'] >= (int)$counts['total']);
            $newStatus   = $allFinished ? 'finished' : $room['status'];

            $db->prepare('UPDATE rooms SET finished_count=?,status=?,updated_at=? WHERE room_code=?')
                ->execute([$finishedCount, $newStatus, $ts, $roomCode]);
        } else {
            $db->prepare('UPDATE room_players SET position=? WHERE room_code=? AND player_index=?')
                ->execute([$finalPos, $roomCode, $pIdx]);
            $db->prepare('UPDATE rooms SET updated_at=? WHERE room_code=?')
                ->execute([$ts, $roomCode]);
        }

        // Log langkah
        if ($dice > 0) {
            $db->prepare('INSERT INTO game_logs (room_code,player_index,dice_value,pos_before,pos_after,event_type) VALUES (?,?,?,?,?,?)')
                ->execute([$roomCode, $pIdx, $dice, $posBefore, $finalPos, $isWin ? 'win' : $eventType]);
        }

        $db->commit();
        sendSuccess(['room' => getRoomData($roomCode), 'allFinished' => $allFinished]);
        break;

    // --------------------------------------------------------
    // POST restart
    // Body: { roomCode, playerIndex }
    // --------------------------------------------------------
    case 'restart':
        $b        = getInput();
        $roomCode = trim($b['roomCode']     ?? '');
        $pIdx     = (int)($b['playerIndex'] ?? -1);
        if (!$roomCode) sendError('roomCode wajib diisi.');
        if ($pIdx !== 0) sendError('Hanya host yang bisa restart.', 403);

        $db      = getDB();
        $newSeed = random_int(100000, 999999);
        $ts      = now();

        $db->beginTransaction();
        $db->prepare("UPDATE rooms SET board_seed=?,last_roll=0,current_turn=0,bonus_turn=0,finished_count=0,status='playing',updated_at=? WHERE room_code=?")
            ->execute([$newSeed, $ts, $roomCode]);
        $db->prepare('UPDATE room_players SET position=1,finish_rank=0,is_finished=0 WHERE room_code=?')
            ->execute([$roomCode]);
        $db->commit();

        sendSuccess(['room' => getRoomData($roomCode)]);
        break;

    // --------------------------------------------------------
    // GET poll?roomCode=1234&since=TIMESTAMP
    // Long-polling: tunggu sampai updated_at berubah.
    // SQLite + WAL memungkinkan reader tidak terblokir writer.
    // --------------------------------------------------------
    case 'poll':
        $roomCode = trim($_GET['roomCode'] ?? '');
        $since    = trim($_GET['since']    ?? '');
        if (!$roomCode) sendError('roomCode wajib diisi.');

        $db       = getDB();
        $deadline = time() + POLL_TIMEOUT;

        while (time() < $deadline) {
            $s = $db->prepare('SELECT updated_at FROM rooms WHERE room_code=? LIMIT 1');
            $s->execute([$roomCode]);
            $row = $s->fetch();
            if (!$row) sendError('Room tidak ditemukan.', 404);

            // Ada update baru?
            if ($since === '' || $row['updated_at'] > $since) {
                sendSuccess(['room' => getRoomData($roomCode)]);
            }

            usleep(800_000); // 0.8 detik — lebih responsif dari sleep(1)
        }

        // Timeout — kirim data terkini
        sendSuccess(['room' => getRoomData($roomCode), 'timeout' => true]);
        break;

    // --------------------------------------------------------
    // GET get?roomCode=1234
    // --------------------------------------------------------
    case 'get':
        $roomCode = trim($_GET['roomCode'] ?? '');
        if (!$roomCode) sendError('roomCode wajib diisi.');
        $data = getRoomData($roomCode);
        if (!$data) sendError('Room tidak ditemukan.', 404);
        sendSuccess(['room' => $data]);
        break;

    // --------------------------------------------------------
    // GET logs?roomCode=1234
    // --------------------------------------------------------
    case 'logs':
        $roomCode = trim($_GET['roomCode'] ?? '');
        if (!$roomCode) sendError('roomCode wajib diisi.');
        $db = getDB();
        $s  = $db->prepare('SELECT * FROM game_logs WHERE room_code=? ORDER BY id DESC LIMIT 100');
        $s->execute([$roomCode]);
        sendSuccess(['logs' => $s->fetchAll()]);
        break;

    // --------------------------------------------------------
    // GET cleanup — hapus room lama (opsional, bisa di-cron)
    // --------------------------------------------------------
    case 'cleanup':
        $db = getDB();
        // Hapus room yang tidak aktif lebih dari 24 jam
        $db->prepare("DELETE FROM rooms WHERE updated_at < datetime('now','-24 hours')")
            ->execute();
        $affected = $db->query('SELECT changes() as n')->fetch()['n'];
        sendSuccess(['deleted' => (int)$affected]);
        break;

    default:
        sendError('Action tidak dikenal: ' . htmlspecialchars($action), 404);
}

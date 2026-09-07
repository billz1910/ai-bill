<?php
// Konfigurasi Database
define('DB_FILE', __DIR__ . '/database.sqlite');

// Konfigurasi API Default
define('DEFAULT_API_KEY', 'sk-dca0173befe1a998-0us61o-c8855a33');
define('DEFAULT_MODEL', 'df/deepseek-v4-flash-vision-exp');
define('BASE_URL', 'https://9router-production-5fe9.up.railway.app/v1');

// Inisialisasi Database
function initDatabase() {
    $db = new SQLite3(DB_FILE);
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS chats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            chat_id INTEGER NOT NULL,
            role TEXT NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
        )
    ");
    
    $db->exec("
        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        )
    ");
    
    return $db;
}

function getDB() {
    static $db = null;
    if ($db === null) {
        $db = initDatabase();
    }
    return $db;
}

// Helper functions
function getChats() {
    $db = getDB();
    $result = $db->query("SELECT * FROM chats ORDER BY updated_at DESC");
    $chats = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $chats[] = $row;
    }
    return $chats;
}

function getMessages($chatId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM messages WHERE chat_id = :chat_id ORDER BY created_at ASC");
    $stmt->bindValue(':chat_id', $chatId, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $messages = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $messages[] = $row;
    }
    return $messages;
}

function createChat($title) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO chats (title) VALUES (:title)");
    $stmt->bindValue(':title', $title, SQLITE3_TEXT);
    $stmt->execute();
    return $db->lastInsertRowID();
}

function addMessage($chatId, $role, $content) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO messages (chat_id, role, content) VALUES (:chat_id, :role, :content)");
    $stmt->bindValue(':chat_id', $chatId, SQLITE3_INTEGER);
    $stmt->bindValue(':role', $role, SQLITE3_TEXT);
    $stmt->bindValue(':content', $content, SQLITE3_TEXT);
    $stmt->execute();
    
    // Update chat timestamp
    $stmt2 = $db->prepare("UPDATE chats SET updated_at = CURRENT_TIMESTAMP WHERE id = :id");
    $stmt2->bindValue(':id', $chatId, SQLITE3_INTEGER);
    $stmt2->execute();
}

function deleteChat($chatId) {
    $db = getDB();
    $stmt = $db->prepare("DELETE FROM chats WHERE id = :id");
    $stmt->bindValue(':id', $chatId, SQLITE3_INTEGER);
    $stmt->execute();
}

function getSetting($key, $default = null) {
    $db = getDB();
    $stmt = $db->prepare("SELECT value FROM settings WHERE key = :key");
    $stmt->bindValue(':key', $key, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return $row ? $row['value'] : $default;
}

function setSetting($key, $value) {
    $db = getDB();
    $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (:key, :value)");
    $stmt->bindValue(':key', $key, SQLITE3_TEXT);
    $stmt->bindValue(':value', $value, SQLITE3_TEXT);
    $stmt->execute();
}
?>

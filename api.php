<?php
$db_file = __DIR__ . '/undangan.db';

try {
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("CREATE TABLE IF NOT EXISTS rsvp (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama TEXT,
        ucapan TEXT,
        konfirmasi TEXT,
        jumlah TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Menangkap data sesuai dengan atribut name di HTML Elementor Anda
    $fields = $_POST['form_fields'] ?? [];
    $nama = $fields['guestname'] ?? '';
    $ucapan = $fields['messagestext'] ?? '';
    $konfirmasi = $fields['confirmattend'] ?? '';
    $jumlah = $fields['countpeople'] ?? '';

    if (!empty($nama) && !empty($ucapan)) {
        $stmt = $db->prepare("INSERT INTO rsvp (nama, ucapan, konfirmasi, jumlah) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $ucapan, $konfirmasi, $jumlah]);
        echo json_encode(['status' => 'success', 'message' => 'Terima kasih, ucapan Anda telah tersimpan.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nama dan Ucapan tidak boleh kosong.']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json');
    $query = $db->query("SELECT * FROM rsvp ORDER BY id DESC");
    echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

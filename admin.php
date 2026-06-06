<?php
// Ganti password ini dengan yang Anda inginkan
$password_admin = 'nita&fembi2026';

session_start();

// Proses Login
if (isset($_POST['password'])) {
    if ($_POST['password'] === $password_admin) {
        $_SESSION['admin_logged_in'] = true;
        // Redirect agar form tidak ter-submit ulang saat di-refresh
        header("Location: ?");
        exit;
    } else {
        $error = "Password salah!";
    }
}

// Proses Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ?");
    exit;
}

// Cek Sesi
if (!isset($_SESSION['admin_logged_in'])) {
?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Login Admin RSVP</title>
        <style>
            body {
                font-family: sans-serif;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                background: #f4f4f4;
            }

            .login-box {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }

            input {
                padding: 10px;
                margin: 10px 0;
                width: 100%;
                box-sizing: border-box;
            }

            button {
                padding: 10px 20px;
                background: #66513E;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
        </style>
    </head>

    <body>
        <div class="login-box">
            <h2>Login Admin</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST">
                <input type="password" name="password" placeholder="Masukkan Password" required>
                <button type="submit">Masuk</button>
            </form>
        </div>
    </body>

    </html>
<?php
    exit;
}

// --- JIKA SUDAH LOGIN ---
$db_file = __DIR__ . '/undangan.db';

try {
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buat tabel jika belum ada
    $db->exec("CREATE TABLE IF NOT EXISTS rsvp (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nama TEXT,
        ucapan TEXT,
        konfirmasi TEXT,
        jumlah TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// --- PROSES API UNTUK ELEMENTOR (POST & GET JSON) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_fields'])) {
    header('Content-Type: application/json');

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

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api'])) {
    header('Content-Type: application/json');
    $query = $db->query("SELECT * FROM rsvp ORDER BY id DESC");
    echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
    exit;
}
// --- END API ---

// Proses Hapus Data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM rsvp WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: ?msg=deleted");
    exit;
}

// Proses Update Data (Edit)
if (isset($_POST['update_data'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $konfirmasi = $_POST['konfirmasi'];
    $ucapan = $_POST['ucapan'];

    $stmt = $db->prepare("UPDATE rsvp SET nama = ?, konfirmasi = ?, ucapan = ? WHERE id = ?");
    $stmt->execute([$nama, $konfirmasi, $ucapan, $id]);
    header("Location: ?msg=updated");
    exit;
}

// Ambil Semua Data untuk Tabel
$query = $db->query("SELECT * FROM rsvp ORDER BY id DESC");
$data = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola RSVP & Ucapan</title>
    <style>
        body {
            font-family: sans-serif;
            background: #fdfaf7;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #66513E;
            color: white;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
        }

        .btn-edit {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 12px;
            margin-right: 5px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-logout {
            background: #555;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        /* Styling Form Edit */
        .edit-box {
            background: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .edit-box input,
        .edit-box select,
        .edit-box textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .edit-box textarea {
            height: 80px;
            resize: vertical;
        }

        .btn-save {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            margin-left: 5px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h2>Daftar RSVP & Ucapan</h2>
            <a href="?logout=true" class="btn-logout">Logout</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') echo "<p style='color:red;'>Data berhasil dihapus!</p>"; ?>
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated') echo "<p style='color:green;'>Data berhasil diperbarui!</p>"; ?>

        <?php
        if (isset($_GET['edit'])) {
            $id_edit = $_GET['edit'];
            $stmt = $db->prepare("SELECT * FROM rsvp WHERE id = ?");
            $stmt->execute([$id_edit]);
            $row_edit = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row_edit) {
        ?>
                <div class="edit-box">
                    <h3>Edit Data</h3>
                    <form method="POST">
                        <input type="hidden" name="id" value="<?= $row_edit['id'] ?>">

                        <label>Nama:</label>
                        <input type="text" name="nama" value="<?= htmlspecialchars($row_edit['nama']) ?>" required>

                        <label>Kehadiran:</label>
                        <select name="konfirmasi" required>
                            <option value="Hadir" <?= $row_edit['konfirmasi'] == 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                            <option value="Tidak Hadir" <?= $row_edit['konfirmasi'] == 'Tidak Hadir' ? 'selected' : '' ?>>Tidak Hadir</option>
                            <option value="Masih Ragu" <?= $row_edit['konfirmasi'] == 'Masih Ragu' ? 'selected' : '' ?>>Masih Ragu</option>
                        </select>

                        <label>Ucapan:</label>
                        <textarea name="ucapan" required><?= htmlspecialchars($row_edit['ucapan']) ?></textarea>

                        <button type="submit" name="update_data" class="btn-save">Simpan Perubahan</button>
                        <a href="?" class="btn-cancel">Batal</a>
                    </form>
                </div>
        <?php
            }
        }
        ?>

        <table>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Kehadiran</th>
                <th>Ucapan</th>
                <th>Waktu</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><strong><?= htmlspecialchars($row['nama']) ?></strong></td>
                    <td><?= htmlspecialchars($row['konfirmasi']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['ucapan'])) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="?edit=<?= $row['id'] ?>" class="btn-edit">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus ucapan dari <?= htmlspecialchars($row['nama']) ?>?');">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>

</html>
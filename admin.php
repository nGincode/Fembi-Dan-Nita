<?php
// Ganti password ini dengan yang Anda inginkan
$password_admin = 'nita&fembi2026';

session_start();

// Proses Login
if (isset($_POST['password'])) {
    if ($_POST['password'] === $password_admin) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Password salah!";
    }
}

// Proses Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
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
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Proses Hapus Data
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM rsvp WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin.php?msg=deleted");
    exit;
}

// Ambil Semua Data
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
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h2>Daftar RSVP & Ucapan</h2>
            <a href="?logout=true" class="btn-logout">Logout</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') echo "<p style='color:green;'>Data berhasil dihapus!</p>"; ?>

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
                        <a href="?delete=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus ucapan dari <?= htmlspecialchars($row['nama']) ?>?');">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

</body>

</html>
<?php
// admin/login.php
session_start();
require_once '../config/database.php';

// Jika sudah login, langsung arahkan ke index admin
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$error = "";

// Proses jika tombol login ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password =$_POST['password'];
    
    // Enkripsi password input ke format MD5 agar cocok dengan isi database
    $password_md5 = md5($password);

    // DIPERBARUI: Mengambil data dari tabel `admin`, bukan `users_admin`
    $stmt =$conn->prepare("SELECT id, username, password, nama_admin FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);$stmt->execute();
    $result =$stmt->get_result();

    if ($result->num_rows > 0) {
        $row =$result->fetch_assoc();
        
        // DIPERBARUI: Verifikasi menggunakan perbandingan string MD5 langsung
        if ($password_md5 ===$row['password']) {
            // Berhasil login
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['nama_admin'] =$row['nama_admin'];
            $_SESSION['username'] =$row['username']; // Wajib ada untuk sinkronisasi menu Pengaturan
            
            header("Location: index.php");
            exit();
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Buku Tamu</title>
    <link rel="stylesheet" href="../assets/css/style-tamu.css">
</head>
<body style="background-color: #F4F7FA; display: flex; justify-content: center; align-items: center; min-height: 100vh;">

    <div class="login-container">
        <h2>Login Admin</h2>

        <?php if ($error != ""): ?>
            <div class="alert-error"><?= $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Masukkan username" required autofocus>
            </div>
            
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Masukkan password" required>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; max-width: 100%; margin-top: 10px;">
                MASUK
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="../index.php" style="color: #64748B; text-decoration: none; font-size: 14px;">← Kembali ke Halaman Tamu</a>
        </div>
    </div>

</body>
</html>
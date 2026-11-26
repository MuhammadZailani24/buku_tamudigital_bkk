<?php
// admin/login.php
session_start();
require_once '../config/database.php';

// Jika sudah login, langsung lempar ke index
if (isset($_SESSION['admin_username'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = md5($_POST['password']); // Enkripsi MD5 sesuai default

    $query = "SELECT * FROM admin_users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role']     = $user['role'];
        $_SESSION['admin_wilker']   = $user['wilker'];
        
        header("Location: index.php");
        exit;
    } else {
        $error = 'Username atau Password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - BKK Banjarmasin</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #F1F5F9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 380px; text-align: center; }
        .login-box img { width: 90px; margin-bottom: 15px; }
        .login-box h2 { margin: 0 0 5px; color: #1E3A8A; font-size: 22px; }
        .login-box p { color: #64748B; font-size: 13px; margin-bottom: 25px; }
        .form-group { text-align: left; margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; color: #334155; font-weight: bold; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #CBD5E1; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #3B82F6; }
        .btn-login { background: #2563EB; color: white; width: 100%; padding: 12px; border: none; border-radius: 8px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-login:hover { background: #1D4ED8; }
        .alert { background: #FEE2E2; color: #B91C1C; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; border: 1px solid #FCA5A5; }
    </style>
</head>
<body>
    <div class="login-box">
        <img src="../assets/images/logo_bkk.webp" alt="Logo">
        <h2>LOGIN SISTEM</h2>
        <p>Buku Tamu Digital BKK Kelas I Banjarmasin</p>
        
        <?php if($error != '') echo "<div class='alert'>$error</div>"; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Masukkan username admin">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Masukkan password">
            </div>
            <button type="submit" class="btn-login">Masuk ke Dashboard</button>
        </form>
    </div>
</body>
</html>
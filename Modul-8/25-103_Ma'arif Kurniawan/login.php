<?php
require 'config.php';
guest_only();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $s = $db->prepare("SELECT id_user, password, nama, level FROM user WHERE username = ? LIMIT 1");
        $s->bind_param('s', $username);
        $s->execute();
        $u = $s->get_result()->fetch_assoc();

        if ($u && in_array((int)$u['level'], [1, 2], true) && verify_password($password, $u['password'])) {
            // Upgrade password md5 lama ke bcrypt (gagal pun login tetap jalan)
            if (!is_bcrypt($u['password'])) {
                try {
                    $h = password_hash($password, PASSWORD_DEFAULT);
                    $up = $db->prepare("UPDATE user SET password = ? WHERE id_user = ?");
                    $up->bind_param('si', $h, $u['id_user']);
                    $up->execute();
                } catch (Throwable $ex) {
                    // dilewati; jalankan migrasi_module8.sql untuk mengaktifkan
                }
            }

            session_regenerate_id(true); // cegah session fixation
            $_SESSION['user_id'] = (int)$u['id_user'];
            $_SESSION['nama']    = $u['nama'];
            $_SESSION['level']   = (int)$u['level'];
            header('Location: home.php');
            exit;
        }
        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Penjualan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main style="max-width:360px;margin-top:80px">
  <div class="card">
    <h1 style="margin-top:0">Login</h1>
    <?php if ($error): ?><div class="alert err"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus>
      <label>Password</label>
      <input type="password" name="password" required>
      <p><button type="submit">Login</button></p>
    </form>
  </div>
</main>
</body>
</html>

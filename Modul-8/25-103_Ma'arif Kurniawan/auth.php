<?php
// Hanya boleh diakses setelah login
function require_login(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

// Hanya boleh diakses level tertentu (1 = owner, 2 = kasir)
function require_level(array $levels): void {
    require_login();
    if (!in_array((int)$_SESSION['level'], $levels, true)) {
        flash('err', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        header('Location: home.php');
        exit;
    }
}

// Halaman login tidak boleh diakses jika sudah login
function guest_only(): void {
    if (!empty($_SESSION['user_id'])) {
        header('Location: home.php');
        exit;
    }
}

function is_bcrypt(string $hash): bool {
    return strncmp($hash, '$2y$', 4) === 0;
}

// Mendukung password bcrypt baru dan md5 lama (data dari Permintaan.sql)
function verify_password(string $plain, string $stored): bool {
    if (is_bcrypt($stored)) {
        return password_verify($plain, $stored);
    }
    return hash_equals(strtolower($stored), md5($plain));
}

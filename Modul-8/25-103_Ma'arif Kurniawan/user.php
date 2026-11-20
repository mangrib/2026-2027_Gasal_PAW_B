<?php
require 'config.php';
require_level([1]); // Data user hanya untuk owner

$levels = [1 => 'Owner', 2 => 'Kasir'];
$errors = [];
$mode   = $_GET['aksi'] ?? 'list';
$f      = ['id_user' => '', 'username' => '', 'nama' => '', 'alamat' => '', 'hp' => '', 'level' => '2'];

// ---------- HAPUS ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id === (int)$_SESSION['user_id']) {
        flash('err', 'Anda tidak dapat menghapus akun yang sedang login.');
    } else {
        $s = $db->prepare("DELETE FROM user WHERE id_user = ?");
        $s->bind_param('i', $id);
        $s->execute();
        flash($s->affected_rows > 0 ? 'ok' : 'err', $s->affected_rows > 0 ? 'User berhasil dihapus.' : 'Data user tidak ditemukan.');
    }
    header('Location: user.php'); exit;
}

// ---------- SIMPAN ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'simpan') {
    $f = [
        'id_user'  => (int)($_POST['id_user'] ?? 0),
        'username' => trim($_POST['username'] ?? ''),
        'nama'     => trim($_POST['nama'] ?? ''),
        'alamat'   => trim($_POST['alamat'] ?? ''),
        'hp'       => trim($_POST['hp'] ?? ''),
        'level'    => trim($_POST['level'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';

    if ($f['username'] === '' || strlen($f['username']) > 30) $errors[] = 'Username wajib diisi (maks. 30 karakter).';
    if ($f['nama'] === '')                                    $errors[] = 'Nama wajib diisi.';
    if (!isset($levels[(int)$f['level']]))                    $errors[] = 'Level tidak valid.';
    if ($f['id_user'] === 0 && $password === '')              $errors[] = 'Password wajib diisi untuk user baru.';
    if ($password !== '' && strlen($password) < 6)            $errors[] = 'Password minimal 6 karakter.';

    if (!$errors) {
        $lv   = (int)$f['level'];
        $hash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;
        try {
            if ($f['id_user'] > 0) {
                $sets   = 'username=?, nama=?, alamat=?, hp=?, level=?';
                $types  = 'ssssi';
                $params = [$f['username'], $f['nama'], $f['alamat'], $f['hp'], $lv];
                if ($hash !== null) {            // password hanya diubah jika diisi
                    $sets    .= ', password=?';
                    $types   .= 's';
                    $params[] = $hash;
                }
                $types   .= 'i';
                $params[] = $f['id_user'];
                $s = $db->prepare("UPDATE user SET $sets WHERE id_user=?");
                $s->bind_param($types, ...$params);
            } else {
                $s = $db->prepare("INSERT INTO user (username, password, nama, alamat, hp, level) VALUES (?,?,?,?,?,?)");
                $s->bind_param('sssssi', $f['username'], $hash, $f['nama'], $f['alamat'], $f['hp'], $lv);
            }
            $s->execute();
            flash('ok', 'Data user berhasil disimpan.');
            header('Location: user.php'); exit;
        } catch (mysqli_sql_exception $ex) {
            $errors[] = $ex->getCode() === 1062 ? 'Username sudah digunakan.' : 'Gagal menyimpan: ' . $ex->getMessage();
        }
    }
    $mode = $f['id_user'] > 0 ? 'edit' : 'tambah';
}

// ---------- Ambil data form ubah ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $mode === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $s  = $db->prepare("SELECT id_user, username, nama, alamat, hp, level FROM user WHERE id_user = ?");
    $s->bind_param('i', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) {
        flash('err', 'Data user tidak ditemukan.');
        header('Location: user.php'); exit;
    }
    $f = $row;
}

$list = null;
if ($mode === 'list') {
    $list = $db->query("SELECT id_user, username, nama, alamat, hp, level FROM user ORDER BY id_user");
}

page_header('Data User');
?>
<?php if ($errors): ?><div class="alert err"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

<?php if ($mode === 'list'): ?>
  <p><a class="btn" href="user.php?aksi=tambah">+ Tambah User</a></p>
  <table>
    <thead><tr><th>ID</th><th>Username</th><th>Nama</th><th>Alamat</th><th>HP</th><th>Level</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php while ($r = $list->fetch_assoc()): ?>
      <tr>
        <td><?= $r['id_user'] ?></td>
        <td><?= e($r['username']) ?></td>
        <td><?= e($r['nama']) ?></td>
        <td><?= e($r['alamat']) ?></td>
        <td><?= e($r['hp']) ?></td>
        <td><?= e(($levels[(int)$r['level']] ?? '-')) ?></td>
        <td>
          <a class="btn gray" href="user.php?aksi=edit&id=<?= $r['id_user'] ?>">Ubah</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Yakin hapus user ini?')">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id" value="<?= $r['id_user'] ?>">
            <button class="danger" type="submit">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

<?php else: ?>
  <?php $isEdit = $mode === 'edit'; ?>
  <div class="card">
    <h2 style="margin-top:0"><?= $isEdit ? 'Ubah' : 'Tambah' ?> User</h2>
    <form method="post" action="user.php">
      <input type="hidden" name="action" value="simpan">
      <input type="hidden" name="id_user" value="<?= (int)$f['id_user'] ?>">

      <label>Username</label>
      <input type="text" name="username" maxlength="30" value="<?= e($f['username']) ?>" required>

      <label>Password</label>
      <input type="password" name="password" placeholder="<?= $isEdit ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' ?>">

      <label>Nama</label>
      <input type="text" name="nama" maxlength="50" value="<?= e($f['nama']) ?>" required>

      <label>Alamat</label>
      <input type="text" name="alamat" maxlength="150" value="<?= e($f['alamat']) ?>">

      <label>HP</label>
      <input type="text" name="hp" maxlength="20" value="<?= e($f['hp']) ?>">

      <label>Level</label>
      <select name="level">
        <?php foreach ($levels as $val => $nm): ?>
          <option value="<?= $val ?>" <?= (string)$f['level'] === (string)$val ? 'selected' : '' ?>><?= e("$val - $nm") ?></option>
        <?php endforeach; ?>
      </select>

      <p><button type="submit">Simpan</button> <a class="btn gray" href="user.php">Batal</a></p>
    </form>
  </div>
<?php endif; ?>
<?php page_footer();

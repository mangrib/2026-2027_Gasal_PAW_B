<?php
require 'config.php';
require_level([1]); // Data master hanya untuk owner

$jk_label = ['L' => 'Laki-laki', 'P' => 'Perempuan'];
$errors   = [];
$mode     = $_GET['aksi'] ?? 'list';
$f        = ['id' => '', 'nama' => '', 'jenis_kelamin' => 'L', 'telp' => '', 'alamat' => ''];

// ---------- HAPUS ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus') {
    $id = trim($_POST['id'] ?? '');

    $s = $db->prepare("SELECT nama FROM pelanggan WHERE id = ?");
    $s->bind_param('s', $id);
    $s->execute();
    $p = $s->get_result()->fetch_assoc();

    if (!$p) {
        flash('err', 'Data pelanggan tidak ditemukan.');
    } else {
        $s = $db->prepare("SELECT COUNT(*) AS n FROM transaksi WHERE pelanggan_id = ?");
        $s->bind_param('s', $id);
        $s->execute();
        $n = (int)$s->get_result()->fetch_assoc()['n'];

        if ($n > 0) {
            flash('err', "Pelanggan \"{$p['nama']}\" tidak dapat dihapus karena memiliki $n transaksi.");
        } else {
            $s = $db->prepare("DELETE FROM pelanggan WHERE id = ?");
            $s->bind_param('s', $id);
            $s->execute();
            flash('ok', "Pelanggan \"{$p['nama']}\" berhasil dihapus.");
        }
    }
    header('Location: pelanggan.php'); exit;
}

// ---------- SIMPAN ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'simpan') {
    $editId = trim($_POST['edit_id'] ?? ''); // terisi saat mengubah data
    $f = [
        'id'            => $editId !== '' ? $editId : trim($_POST['id'] ?? ''),
        'nama'          => trim($_POST['nama'] ?? ''),
        'jenis_kelamin' => trim($_POST['jenis_kelamin'] ?? ''),
        'telp'          => trim($_POST['telp'] ?? ''),
        'alamat'        => trim($_POST['alamat'] ?? ''),
    ];

    if ($f['id'] === '' || strlen($f['id']) > 20)       $errors[] = 'Kode pelanggan wajib diisi (maks. 20 karakter).';
    if ($f['nama'] === '')                               $errors[] = 'Nama pelanggan wajib diisi.';
    if (!isset($jk_label[$f['jenis_kelamin']]))          $errors[] = 'Jenis kelamin tidak valid.';
    if (strlen($f['telp']) > 12)                         $errors[] = 'Telepon maksimal 12 karakter.';

    if (!$errors) {
        try {
            if ($editId !== '') {
                $s = $db->prepare("UPDATE pelanggan SET nama=?, jenis_kelamin=?, telp=?, alamat=? WHERE id=?");
                $s->bind_param('sssss', $f['nama'], $f['jenis_kelamin'], $f['telp'], $f['alamat'], $editId);
            } else {
                $s = $db->prepare("INSERT INTO pelanggan (id, nama, jenis_kelamin, telp, alamat) VALUES (?,?,?,?,?)");
                $s->bind_param('sssss', $f['id'], $f['nama'], $f['jenis_kelamin'], $f['telp'], $f['alamat']);
            }
            $s->execute();
            flash('ok', 'Data pelanggan berhasil disimpan.');
            header('Location: pelanggan.php'); exit;
        } catch (mysqli_sql_exception $ex) {
            $errors[] = $ex->getCode() === 1062 ? 'Kode pelanggan sudah digunakan.' : 'Gagal menyimpan: ' . $ex->getMessage();
        }
    }
    $mode = $editId !== '' ? 'edit' : 'tambah';
}

// ---------- Ambil data form ubah ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $mode === 'edit') {
    $id = trim($_GET['id'] ?? '');
    $s  = $db->prepare("SELECT * FROM pelanggan WHERE id = ?");
    $s->bind_param('s', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) {
        flash('err', 'Data pelanggan tidak ditemukan.');
        header('Location: pelanggan.php'); exit;
    }
    $f = $row;
}

$list = null;
if ($mode === 'list') {
    $list = $db->query(
        "SELECT p.*, (SELECT COUNT(*) FROM transaksi t WHERE t.pelanggan_id = p.id) AS jml_transaksi
           FROM pelanggan p ORDER BY p.nama"
    );
}

page_header('Data Pelanggan');
?>
<?php if ($errors): ?><div class="alert err"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

<?php if ($mode === 'list'): ?>
  <p><a class="btn" href="pelanggan.php?aksi=tambah">+ Tambah Pelanggan</a></p>
  <table>
    <thead><tr><th>Kode</th><th>Nama</th><th>JK</th><th>Telepon</th><th>Alamat</th><th class="num">Transaksi</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php while ($r = $list->fetch_assoc()): ?>
      <tr>
        <td><?= e($r['id']) ?></td>
        <td><?= e($r['nama']) ?></td>
        <td><?= e($jk_label[$r['jenis_kelamin']] ?? '') ?></td>
        <td><?= e($r['telp']) ?></td>
        <td><?= e($r['alamat']) ?></td>
        <td class="num"><?= $r['jml_transaksi'] ?></td>
        <td>
          <a class="btn gray" href="pelanggan.php?aksi=edit&id=<?= e(urlencode($r['id'])) ?>">Ubah</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Yakin hapus pelanggan ini?')">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
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
    <h2 style="margin-top:0"><?= $isEdit ? 'Ubah' : 'Tambah' ?> Pelanggan</h2>
    <form method="post" action="pelanggan.php">
      <input type="hidden" name="action" value="simpan">
      <input type="hidden" name="edit_id" value="<?= $isEdit ? e($f['id']) : '' ?>">

      <label>Kode Pelanggan</label>
      <input type="text" name="id" maxlength="20" value="<?= e($f['id']) ?>" <?= $isEdit ? 'readonly' : 'required' ?>>

      <label>Nama</label>
      <input type="text" name="nama" maxlength="100" value="<?= e($f['nama']) ?>" required>

      <label>Jenis Kelamin</label>
      <select name="jenis_kelamin">
        <?php foreach ($jk_label as $val => $nm): ?>
          <option value="<?= $val ?>" <?= $f['jenis_kelamin'] === $val ? 'selected' : '' ?>><?= e($nm) ?></option>
        <?php endforeach; ?>
      </select>

      <label>Telepon</label>
      <input type="text" name="telp" maxlength="12" value="<?= e($f['telp']) ?>">

      <label>Alamat</label>
      <textarea name="alamat" rows="3"><?= e($f['alamat']) ?></textarea>

      <p><button type="submit">Simpan</button> <a class="btn gray" href="pelanggan.php">Batal</a></p>
    </form>
  </div>
<?php endif; ?>
<?php page_footer();

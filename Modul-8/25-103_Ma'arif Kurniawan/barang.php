<?php
require 'config.php';
require_level([1]); // Data master hanya untuk owner

$errors = [];
$mode   = $_GET['aksi'] ?? 'list';
$f      = ['id' => '', 'kode_barang' => '', 'nama_barang' => '', 'harga' => '', 'stok' => '0', 'supplier_id' => ''];

// ---------- HAPUS ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);

    $s = $db->prepare("SELECT nama_barang FROM barang WHERE id = ?");
    $s->bind_param('i', $id);
    $s->execute();
    $brg = $s->get_result()->fetch_assoc();

    if (!$brg) {
        flash('err', 'Data barang tidak ditemukan.');
    } else {
        $s = $db->prepare("SELECT COUNT(DISTINCT transaksi_id) AS n FROM transaksi_detail WHERE barang_id = ?");
        $s->bind_param('i', $id);
        $s->execute();
        $n = (int)$s->get_result()->fetch_assoc()['n'];

        if ($n > 0) {
            flash('err', "Barang \"{$brg['nama_barang']}\" tidak dapat dihapus karena masih digunakan pada $n transaksi.");
        } else {
            $s = $db->prepare("DELETE FROM barang WHERE id = ?");
            $s->bind_param('i', $id);
            $s->execute();
            flash('ok', "Barang \"{$brg['nama_barang']}\" berhasil dihapus.");
        }
    }
    header('Location: barang.php'); exit;
}

// ---------- SIMPAN (tambah / ubah) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'simpan') {
    $f = [
        'id'          => (int)($_POST['id'] ?? 0),
        'kode_barang' => strtoupper(trim($_POST['kode_barang'] ?? '')),
        'nama_barang' => trim($_POST['nama_barang'] ?? ''),
        'harga'       => trim($_POST['harga'] ?? ''),
        'stok'        => trim($_POST['stok'] ?? ''),
        'supplier_id' => (int)($_POST['supplier_id'] ?? 0),
    ];

    if ($f['kode_barang'] === '' || strlen($f['kode_barang']) > 10) $errors[] = 'Kode barang wajib diisi (maks. 10 karakter).';
    if ($f['nama_barang'] === '')                                   $errors[] = 'Nama barang wajib diisi.';
    if (!ctype_digit($f['harga']))                                  $errors[] = 'Harga harus berupa angka bulat.';
    if (!ctype_digit($f['stok']))                                   $errors[] = 'Stok harus berupa angka bulat.';
    if ($f['supplier_id'] <= 0)                                     $errors[] = 'Supplier wajib dipilih.';

    if (!$errors) {
        $harga = (int)$f['harga'];
        $stok  = (int)$f['stok'];
        try {
            if ($f['id'] > 0) {
                $s = $db->prepare("UPDATE barang SET kode_barang=?, nama_barang=?, harga=?, stok=?, supplier_id=? WHERE id=?");
                $s->bind_param('ssiiii', $f['kode_barang'], $f['nama_barang'], $harga, $stok, $f['supplier_id'], $f['id']);
            } else {
                $s = $db->prepare("INSERT INTO barang (kode_barang, nama_barang, harga, stok, supplier_id) VALUES (?,?,?,?,?)");
                $s->bind_param('ssiii', $f['kode_barang'], $f['nama_barang'], $harga, $stok, $f['supplier_id']);
            }
            $s->execute();
            flash('ok', 'Data barang berhasil disimpan.');
            header('Location: barang.php'); exit;
        } catch (mysqli_sql_exception $ex) {
            $errors[] = $ex->getCode() === 1062 ? 'Kode barang sudah digunakan.' : 'Gagal menyimpan: ' . $ex->getMessage();
        }
    }
    $mode = $f['id'] > 0 ? 'edit' : 'tambah';
}

// ---------- Ambil data form ubah ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $mode === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $s  = $db->prepare("SELECT * FROM barang WHERE id = ?");
    $s->bind_param('i', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) {
        flash('err', 'Data barang tidak ditemukan.');
        header('Location: barang.php'); exit;
    }
    $f = $row;
}

$suppliers = $db->query("SELECT id, nama FROM supplier ORDER BY nama")->fetch_all(MYSQLI_ASSOC);

$list = null;
if ($mode === 'list') {
    $list = $db->query(
        "SELECT b.*, s.nama AS supplier,
                (SELECT COUNT(DISTINCT transaksi_id) FROM transaksi_detail d WHERE d.barang_id = b.id) AS dipakai
           FROM barang b LEFT JOIN supplier s ON s.id = b.supplier_id
          ORDER BY b.id"
    );
}

page_header('Data Barang');
?>
<?php if ($errors): ?><div class="alert err"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

<?php if ($mode === 'list'): ?>
  <p><a class="btn" href="barang.php?aksi=tambah">+ Tambah Barang</a></p>
  <table>
    <thead><tr><th>ID</th><th>Kode</th><th>Nama Barang</th><th class="num">Harga</th><th class="num">Stok</th><th>Supplier</th><th class="num">Dipakai di</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php while ($b = $list->fetch_assoc()): ?>
      <tr>
        <td><?= $b['id'] ?></td>
        <td><?= e($b['kode_barang']) ?></td>
        <td><?= e($b['nama_barang']) ?></td>
        <td class="num"><?= rupiah($b['harga']) ?></td>
        <td class="num"><?= $b['stok'] ?></td>
        <td><?= e($b['supplier']) ?></td>
        <td class="num"><?= $b['dipakai'] ?> transaksi</td>
        <td>
          <a class="btn gray" href="barang.php?aksi=edit&id=<?= $b['id'] ?>">Ubah</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Yakin hapus barang ini?')">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id" value="<?= $b['id'] ?>">
            <button class="danger" type="submit">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

<?php else: ?>
  <div class="card">
    <h2 style="margin-top:0"><?= $mode === 'edit' ? 'Ubah' : 'Tambah' ?> Barang</h2>
    <form method="post" action="barang.php">
      <input type="hidden" name="action" value="simpan">
      <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">

      <label>Kode Barang</label>
      <input type="text" name="kode_barang" maxlength="10" value="<?= e($f['kode_barang']) ?>" required>

      <label>Nama Barang</label>
      <input type="text" name="nama_barang" maxlength="100" value="<?= e($f['nama_barang']) ?>" required>

      <label>Harga</label>
      <input type="text" name="harga" inputmode="numeric" value="<?= e($f['harga']) ?>" required>

      <label>Stok</label>
      <input type="text" name="stok" inputmode="numeric" value="<?= e($f['stok']) ?>" required>

      <label>Supplier</label>
      <select name="supplier_id" required>
        <option value="">-- pilih supplier --</option>
        <?php foreach ($suppliers as $sp): ?>
          <option value="<?= $sp['id'] ?>" <?= (int)$f['supplier_id'] === (int)$sp['id'] ? 'selected' : '' ?>><?= e($sp['nama']) ?></option>
        <?php endforeach; ?>
      </select>

      <p><button type="submit">Simpan</button> <a class="btn gray" href="barang.php">Batal</a></p>
    </form>
  </div>
<?php endif; ?>
<?php page_footer();

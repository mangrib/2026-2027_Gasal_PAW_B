<?php
require 'config.php';
require_level([1]); // Data master hanya untuk owner

$errors = [];
$mode   = $_GET['aksi'] ?? 'list';
$f      = ['id' => '', 'nama' => '', 'telp' => '', 'alamat' => ''];

// ---------- HAPUS ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);

    $s = $db->prepare("SELECT nama FROM supplier WHERE id = ?");
    $s->bind_param('i', $id);
    $s->execute();
    $sp = $s->get_result()->fetch_assoc();

    if (!$sp) {
        flash('err', 'Data supplier tidak ditemukan.');
    } else {
        $s = $db->prepare("SELECT COUNT(*) AS n FROM barang WHERE supplier_id = ?");
        $s->bind_param('i', $id);
        $s->execute();
        $n = (int)$s->get_result()->fetch_assoc()['n'];

        if ($n > 0) {
            flash('err', "Supplier \"{$sp['nama']}\" tidak dapat dihapus karena masih memiliki $n barang.");
        } else {
            $s = $db->prepare("DELETE FROM supplier WHERE id = ?");
            $s->bind_param('i', $id);
            $s->execute();
            flash('ok', "Supplier \"{$sp['nama']}\" berhasil dihapus.");
        }
    }
    header('Location: supplier.php'); exit;
}

// ---------- SIMPAN ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'simpan') {
    $f = [
        'id'     => (int)($_POST['id'] ?? 0),
        'nama'   => trim($_POST['nama'] ?? ''),
        'telp'   => trim($_POST['telp'] ?? ''),
        'alamat' => trim($_POST['alamat'] ?? ''),
    ];

    if ($f['nama'] === '')          $errors[] = 'Nama supplier wajib diisi.';
    if (strlen($f['telp']) > 12)    $errors[] = 'Telepon maksimal 12 karakter.';

    if (!$errors) {
        try {
            if ($f['id'] > 0) {
                $s = $db->prepare("UPDATE supplier SET nama=?, telp=?, alamat=? WHERE id=?");
                $s->bind_param('sssi', $f['nama'], $f['telp'], $f['alamat'], $f['id']);
            } else {
                $s = $db->prepare("INSERT INTO supplier (nama, telp, alamat) VALUES (?,?,?)");
                $s->bind_param('sss', $f['nama'], $f['telp'], $f['alamat']);
            }
            $s->execute();
            flash('ok', 'Data supplier berhasil disimpan.');
            header('Location: supplier.php'); exit;
        } catch (mysqli_sql_exception $ex) {
            $errors[] = 'Gagal menyimpan: ' . $ex->getMessage();
        }
    }
    $mode = $f['id'] > 0 ? 'edit' : 'tambah';
}

// ---------- Ambil data form ubah ----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $mode === 'edit') {
    $id = (int)($_GET['id'] ?? 0);
    $s  = $db->prepare("SELECT * FROM supplier WHERE id = ?");
    $s->bind_param('i', $id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    if (!$row) {
        flash('err', 'Data supplier tidak ditemukan.');
        header('Location: supplier.php'); exit;
    }
    $f = $row;
}

$list = null;
if ($mode === 'list') {
    $list = $db->query(
        "SELECT s.*, (SELECT COUNT(*) FROM barang b WHERE b.supplier_id = s.id) AS jml_barang
           FROM supplier s ORDER BY s.id"
    );
}

page_header('Data Supplier');
?>
<?php if ($errors): ?><div class="alert err"><?= e(implode(' ', $errors)) ?></div><?php endif; ?>

<?php if ($mode === 'list'): ?>
  <p><a class="btn" href="supplier.php?aksi=tambah">+ Tambah Supplier</a></p>
  <table>
    <thead><tr><th>ID</th><th>Nama</th><th>Telepon</th><th>Alamat</th><th class="num">Jumlah Barang</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php while ($r = $list->fetch_assoc()): ?>
      <tr>
        <td><?= $r['id'] ?></td>
        <td><?= e($r['nama']) ?></td>
        <td><?= e($r['telp']) ?></td>
        <td><?= e($r['alamat']) ?></td>
        <td class="num"><?= $r['jml_barang'] ?></td>
        <td>
          <a class="btn gray" href="supplier.php?aksi=edit&id=<?= $r['id'] ?>">Ubah</a>
          <form method="post" style="display:inline" onsubmit="return confirm('Yakin hapus supplier ini?')">
            <input type="hidden" name="action" value="hapus">
            <input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="danger" type="submit">Hapus</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>

<?php else: ?>
  <div class="card">
    <h2 style="margin-top:0"><?= $mode === 'edit' ? 'Ubah' : 'Tambah' ?> Supplier</h2>
    <form method="post" action="supplier.php">
      <input type="hidden" name="action" value="simpan">
      <input type="hidden" name="id" value="<?= (int)$f['id'] ?>">

      <label>Nama Supplier</label>
      <input type="text" name="nama" maxlength="100" value="<?= e($f['nama']) ?>" required>

      <label>Telepon</label>
      <input type="text" name="telp" maxlength="12" value="<?= e($f['telp']) ?>">

      <label>Alamat</label>
      <textarea name="alamat" rows="3"><?= e($f['alamat']) ?></textarea>

      <p><button type="submit">Simpan</button> <a class="btn gray" href="supplier.php">Batal</a></p>
    </form>
  </div>
<?php endif; ?>
<?php page_footer();

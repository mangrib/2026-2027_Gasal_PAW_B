<?php
require 'config.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare(
    "SELECT t.*, p.nama AS pelanggan FROM transaksi t
       LEFT JOIN pelanggan p ON p.id = t.pelanggan_id WHERE t.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$master = $stmt->get_result()->fetch_assoc();
if (!$master) {
    flash('err', 'Transaksi tidak ditemukan.');
    header('Location: index.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah_detail') {
        $barang_id = (int)($_POST['barang_id'] ?? 0);
        $qty       = (int)($_POST['qty'] ?? 0);

        if ($barang_id <= 0 || $qty < 1) {
            flash('err', 'Pilih barang dan isi qty minimal 1.');
        } else {
            $db->begin_transaction();
            try {
                $s = $db->prepare("SELECT harga FROM barang WHERE id = ?");
                $s->bind_param('i', $barang_id);
                $s->execute();
                $b = $s->get_result()->fetch_assoc();
                if (!$b) throw new RuntimeException('Barang tidak ditemukan.');

                $s = $db->prepare("SELECT 1 FROM transaksi_detail WHERE transaksi_id = ? AND barang_id = ?");
                $s->bind_param('ii', $id, $barang_id);
                $s->execute();
                if ($s->get_result()->num_rows > 0) {
                    throw new RuntimeException('Barang tersebut sudah ada di transaksi ini.');
                }

                $s = $db->prepare("INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES (?, ?, ?, ?)");
                $s->bind_param('iiii', $id, $barang_id, $b['harga'], $qty);
                $s->execute();

                hitung_ulang_total($db, $id);
                $db->commit();
                flash('ok', 'Detail tersimpan, total transaksi diperbarui.');
            } catch (Throwable $ex) {
                $db->rollback();
                flash('err', $ex->getMessage());
            }
        }
        header('Location: detail.php?id=' . $id); exit;
    }

    if ($action === 'hapus_detail') {
        $barang_id = (int)($_POST['barang_id'] ?? 0);
        $db->begin_transaction();
        $s = $db->prepare("DELETE FROM transaksi_detail WHERE transaksi_id = ? AND barang_id = ?");
        $s->bind_param('ii', $id, $barang_id);
        $s->execute();
        hitung_ulang_total($db, $id);
        $db->commit();
        flash('ok', 'Detail dihapus, total transaksi diperbarui.');
        header('Location: detail.php?id=' . $id); exit;
    }
}

$stmt = $db->prepare(
    "SELECT d.barang_id, b.kode_barang, b.nama_barang, d.harga, d.qty, (d.harga * d.qty) AS subtotal
       FROM transaksi_detail d JOIN barang b ON b.id = d.barang_id
      WHERE d.transaksi_id = ? ORDER BY b.nama_barang");
$stmt->bind_param('i', $id);
$stmt->execute();
$details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $db->prepare(
    "SELECT id, kode_barang, nama_barang, harga, stok FROM barang
      WHERE id NOT IN (SELECT barang_id FROM transaksi_detail WHERE transaksi_id = ?)
      ORDER BY nama_barang");
$stmt->bind_param('i', $id);
$stmt->execute();
$tersedia = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$prev = $db->query("SELECT MAX(id) AS x FROM transaksi WHERE id < $id")->fetch_assoc()['x'];
$next = $db->query("SELECT MIN(id) AS x FROM transaksi WHERE id > $id")->fetch_assoc()['x'];
$semua = $db->query("SELECT id, waktu_transaksi FROM transaksi ORDER BY id");

page_header('Master-Detail Transaksi #' . $id);
?>
<div class="nav-master">
  <?php if ($prev): ?><a class="btn gray" href="detail.php?id=<?= $prev ?>">&laquo; Sebelumnya</a><?php endif; ?>
  <form method="get">
    <select name="id" onchange="this.form.submit()">
      <?php while ($r = $semua->fetch_assoc()): ?>
        <option value="<?= $r['id'] ?>" <?= $r['id'] == $id ? 'selected' : '' ?>>Transaksi #<?= $r['id'] ?> (<?= e($r['waktu_transaksi']) ?>)</option>
      <?php endwhile; ?>
    </select>
    <noscript><button>Load</button></noscript>
  </form>
  <?php if ($next): ?><a class="btn gray" href="detail.php?id=<?= $next ?>">Berikutnya &raquo;</a><?php endif; ?>
  <a class="btn gray" href="index.php">Daftar Transaksi</a>
</div>

<div class="card">
  <h2 style="margin-top:0">Master: Transaksi</h2>
  <table>
    <tr><th style="width:160px">ID Transaksi</th><td><?= $master['id'] ?></td></tr>
    <tr><th>Tanggal</th><td><?= e($master['waktu_transaksi']) ?></td></tr>
    <tr><th>Pelanggan</th><td><?= e($master['pelanggan_id'] . ' - ' . $master['pelanggan']) ?></td></tr>
    <tr><th>Keterangan</th><td><?= e($master['keterangan']) ?></td></tr>
    <tr><th>Total</th><td><b><?= rupiah($master['total']) ?></b></td></tr>
  </table>
</div>

<h2>Detail: Barang dalam Transaksi</h2>
<table>
  <thead><tr><th>Kode</th><th>Nama Barang</th><th class="num">Harga</th><th class="num">Qty</th><th class="num">Subtotal</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php foreach ($details as $d): ?>
    <tr>
      <td><?= e($d['kode_barang']) ?></td>
      <td><?= e($d['nama_barang']) ?></td>
      <td class="num"><?= rupiah($d['harga']) ?></td>
      <td class="num"><?= $d['qty'] ?></td>
      <td class="num"><?= rupiah($d['subtotal']) ?></td>
      <td>
        <form method="post" onsubmit="return confirm('Hapus barang ini dari transaksi?')">
          <input type="hidden" name="action" value="hapus_detail">
          <input type="hidden" name="barang_id" value="<?= $d['barang_id'] ?>">
          <button class="danger" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$details): ?><tr><td colspan="6" class="muted">Belum ada detail.</td></tr><?php endif; ?>
  </tbody>
  <tfoot><tr><td colspan="4" class="num">Total</td><td class="num"><?= rupiah($master['total']) ?></td><td></td></tr></tfoot>
</table>

<div class="card" style="margin-top:20px">
  <h2 style="margin-top:0">Tambah Detail</h2>
  <?php if (!$tersedia): ?>
    <p class="muted">Semua barang sudah dimasukkan ke transaksi ini.</p>
  <?php else: ?>
  <form method="post" class="row">
    <input type="hidden" name="action" value="tambah_detail">
    <div><label>Barang</label>
      <select name="barang_id" required>
        <option value="">-- pilih barang --</option>
        <?php foreach ($tersedia as $b): ?>
          <option value="<?= $b['id'] ?>"><?= e($b['kode_barang'] . ' - ' . $b['nama_barang'] . ' (' . rupiah($b['harga']) . ', stok ' . $b['stok'] . ')') ?></option>
        <?php endforeach; ?>
      </select></div>
    <div><label>Qty</label><input type="number" name="qty" min="1" value="1" required style="min-width:90px"></div>
    <div><button type="submit">Simpan Detail</button></div>
  </form>
  <?php endif; ?>
</div>
<?php page_footer();

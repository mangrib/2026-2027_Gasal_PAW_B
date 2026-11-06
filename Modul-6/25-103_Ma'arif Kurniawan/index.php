<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'tambah') {
    $waktu = $_POST['waktu_transaksi'] ?? '';
    $ket   = trim($_POST['keterangan'] ?? '');
    $pel   = $_POST['pelanggan_id'] ?? '';
    if ($waktu === '' || $pel === '') {
        flash('err', 'Tanggal dan pelanggan wajib diisi.');
        header('Location: index.php'); exit;
    }
    $stmt = $db->prepare("INSERT INTO transaksi (waktu_transaksi, keterangan, total, pelanggan_id) VALUES (?, ?, 0, ?)");
    $stmt->bind_param('sss', $waktu, $ket, $pel);
    $stmt->execute();
    flash('ok', 'Transaksi dibuat. Silakan isi detail transaksi.');
    header('Location: detail.php?id=' . $db->insert_id); exit;
}

$pelanggan = $db->query("SELECT id, nama FROM pelanggan ORDER BY nama");
$list = $db->query(
    "SELECT t.id, t.waktu_transaksi, t.keterangan, t.total, p.nama AS pelanggan,
            (SELECT COUNT(*) FROM transaksi_detail d WHERE d.transaksi_id = t.id) AS jml_item
       FROM transaksi t
       LEFT JOIN pelanggan p ON p.id = t.pelanggan_id
      ORDER BY t.id DESC"
);

page_header('Data Transaksi (Master)');
?>
<div class="card">
  <h2 style="margin-top:0">Tambah Transaksi</h2>
  <form method="post" class="row">
    <input type="hidden" name="action" value="tambah">
    <div><label>Tanggal</label><input type="date" name="waktu_transaksi" value="<?= date('Y-m-d') ?>" required></div>
    <div><label>Pelanggan</label>
      <select name="pelanggan_id" required>
        <option value="">-- pilih --</option>
        <?php while ($p = $pelanggan->fetch_assoc()): ?>
          <option value="<?= e($p['id']) ?>"><?= e($p['id'] . ' - ' . $p['nama']) ?></option>
        <?php endwhile; ?>
      </select></div>
    <div><label>Keterangan</label><input type="text" name="keterangan"></div>
    <div><button type="submit">Simpan &amp; Isi Detail</button></div>
  </form>
</div>

<table>
  <thead><tr><th>ID</th><th>Tanggal</th><th>Pelanggan</th><th>Keterangan</th><th class="num">Item</th><th class="num">Total</th><th>Aksi</th></tr></thead>
  <tbody>
  <?php while ($t = $list->fetch_assoc()): ?>
    <tr>
      <td><?= $t['id'] ?></td>
      <td><?= e($t['waktu_transaksi']) ?></td>
      <td><?= e($t['pelanggan']) ?></td>
      <td><?= e($t['keterangan']) ?></td>
      <td class="num"><?= $t['jml_item'] ?></td>
      <td class="num"><?= rupiah($t['total']) ?></td>
      <td><a class="btn" href="detail.php?id=<?= $t['id'] ?>">Lihat / Isi Detail</a></td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php page_footer();

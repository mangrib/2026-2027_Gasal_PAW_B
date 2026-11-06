<?php
require 'config.php';

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

$list = $db->query(
    "SELECT b.*, s.nama AS supplier,
            (SELECT COUNT(DISTINCT transaksi_id) FROM transaksi_detail d WHERE d.barang_id = b.id) AS dipakai
       FROM barang b LEFT JOIN supplier s ON s.id = b.supplier_id
      ORDER BY b.id"
);

page_header('Data Barang');
?>
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
        <form method="post" onsubmit="return confirm('Yakin hapus barang ini?')">
          <input type="hidden" name="action" value="hapus">
          <input type="hidden" name="id" value="<?= $b['id'] ?>">
          <button class="danger" type="submit">Hapus</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
  </tbody>
</table>
<?php page_footer();

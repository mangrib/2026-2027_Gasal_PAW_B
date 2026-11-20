<?php
require 'config.php';
require_login();

$tgl_mulai   = $_GET['tanggal_mulai']   ?? '';
$tgl_selesai = $_GET['tanggal_selesai'] ?? '';

if (!valid_tgl($tgl_mulai) || !valid_tgl($tgl_selesai)) {
    die('Rentang tanggal tidak valid.');
}
if ($tgl_mulai > $tgl_selesai) {
    [$tgl_mulai, $tgl_selesai] = [$tgl_selesai, $tgl_mulai];
}

$rekap   = ambil_rekap_harian($db, $tgl_mulai, $tgl_selesai);
$ringkas = ambil_ringkasan($db, $tgl_mulai, $tgl_selesai);

$nama_file = "laporan_penjualan_{$tgl_mulai}_sd_{$tgl_selesai}.xls";
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nama_file . '"');
header('Pragma: no-cache');
header('Expires: 0');
?>
<html>
<head><meta charset="utf-8"></head>
<body>
<table border="1">
  <tr><th colspan="3">Rekap Laporan Penjualan <?= e($tgl_mulai) ?> s/d <?= e($tgl_selesai) ?></th></tr>
  <tr><th>No</th><th>Total Pendapatan</th><th>Tanggal Transaksi</th></tr>
  <?php $no = 1; foreach ($rekap as $r): ?>
  <tr>
    <td><?= $no++ ?></td>
    <td><?= (int)$r['total_harian'] ?></td>
    <td><?= e($r['tgl']) ?></td>
  </tr>
  <?php endforeach; ?>
  <tr><td colspan="3"></td></tr>
  <tr><th colspan="2">Jumlah Pelanggan Unik</th><td><?= (int)$ringkas['jml_pelanggan'] ?></td></tr>
  <tr><th colspan="2">Total Pendapatan Keseluruhan</th><td><?= (int)$ringkas['omzet'] ?></td></tr>
</table>
</body>
</html>

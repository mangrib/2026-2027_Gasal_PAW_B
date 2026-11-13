<?php
require 'config.php';

$tgl_mulai    = $_GET['tanggal_mulai']   ?? '';
$tgl_selesai  = $_GET['tanggal_selesai'] ?? '';
$sudah_filter = valid_tgl($tgl_mulai) && valid_tgl($tgl_selesai);

$rekap   = [];
$labels  = [];
$data    = [];
$ringkas = ['jml_pelanggan' => 0, 'omzet' => 0];

if ($sudah_filter) {
    if ($tgl_mulai > $tgl_selesai) {
        [$tgl_mulai, $tgl_selesai] = [$tgl_selesai, $tgl_mulai];
    }
    $rekap   = ambil_rekap_harian($db, $tgl_mulai, $tgl_selesai);
    $ringkas = ambil_ringkasan($db, $tgl_mulai, $tgl_selesai);
    $labels  = array_column($rekap, 'tgl');
    $data    = array_map('intval', array_column($rekap, 'total_harian'));
}

page_header('Laporan Penjualan');
?>
<style>
  .chart-box { height: 320px; position: relative; }
  @media print {
    .no-print { display: none !important; }
    nav { display: none !important; }
    main { max-width: 100%; margin: 0; padding: 0; }
    body { background: #fff; }
    .card, table { box-shadow: none; }
    .card { border: 1px solid #ccc; }
    tr { page-break-inside: avoid; }
  }
</style>

<div class="card no-print">
  <form method="get" class="row">
    <div><label>Tanggal Mulai</label>
      <input type="date" name="tanggal_mulai" value="<?= e($tgl_mulai) ?>" required></div>
    <div><label>Tanggal Selesai</label>
      <input type="date" name="tanggal_selesai" value="<?= e($tgl_selesai) ?>" required></div>
    <div><button type="submit">Tampilkan</button></div>
  </form>
</div>

<?php if (!$sudah_filter): ?>
  <p class="muted">Silakan pilih rentang tanggal lalu klik Tampilkan.</p>
<?php else: ?>

  <div class="no-print" style="margin-bottom:16px; display:flex; gap:8px;">
    <button type="button" onclick="window.print()">Cetak</button>
    <a class="btn" style="background:#1d7a3c"
       href="export_excel.php?tanggal_mulai=<?= urlencode($tgl_mulai) ?>&amp;tanggal_selesai=<?= urlencode($tgl_selesai) ?>">Excel</a>
  </div>

  <h2>Periode <?= e($tgl_mulai) ?> s/d <?= e($tgl_selesai) ?></h2>

  <?php if ($labels): ?>
  <div class="card">
    <div class="chart-box"><canvas id="grafik"></canvas></div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    new Chart(document.getElementById('grafik'), {
      type: 'bar',
      data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
          label: 'Total Pendapatan (Rp)',
          data: <?= json_encode($data) ?>,
          backgroundColor: '#1f6feb'
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
      }
    });
  </script>
  <?php endif; ?>

  <h2>Rekapitulasi Penjualan Harian</h2>
  <table>
    <thead><tr><th class="num">No</th><th class="num">Total Pendapatan</th><th>Tanggal Transaksi</th></tr></thead>
    <tbody>
    <?php $no = 1; foreach ($rekap as $r): ?>
      <tr>
        <td class="num"><?= $no++ ?></td>
        <td class="num"><?= rupiah($r['total_harian']) ?></td>
        <td><?= e($r['tgl']) ?></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$rekap): ?>
      <tr><td colspan="3" class="muted">Tidak ada transaksi pada rentang tanggal ini.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>

  <div class="row" style="margin-top:20px">
    <div class="card" style="flex:1">
      <div class="muted">Jumlah Pelanggan Unik</div>
      <h2 style="margin:6px 0"><?= (int)$ringkas['jml_pelanggan'] ?> Orang</h2>
    </div>
    <div class="card" style="flex:1">
      <div class="muted">Total Pendapatan Keseluruhan</div>
      <h2 style="margin:6px 0"><?= rupiah($ringkas['omzet']) ?></h2>
    </div>
  </div>

<?php endif; ?>
<?php page_footer();

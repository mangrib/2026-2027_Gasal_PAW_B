<?php
session_start();
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$db = new mysqli('localhost', 'root', '', 'penjualan');
$db->set_charset('utf8mb4');

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n) { return 'Rp ' . number_format((int)$n, 0, ',', '.'); }

function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        [$type, $msg] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="alert ' . e($type) . '">' . e($msg) . '</div>';
    }
}

function hitung_ulang_total(mysqli $db, int $transaksi_id): void {
    $stmt = $db->prepare(
        "UPDATE transaksi
            SET total = (SELECT COALESCE(SUM(harga * qty), 0)
                           FROM transaksi_detail WHERE transaksi_id = ?)
          WHERE id = ?"
    );
    $stmt->bind_param('ii', $transaksi_id, $transaksi_id);
    $stmt->execute();
}

/* ---------- Helper laporan ---------- */

function valid_tgl(string $s): bool {
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d !== false && $d->format('Y-m-d') === $s;
}

function ambil_rekap_harian(mysqli $db, string $mulai, string $selesai): array {
    $stmt = $db->prepare(
        "SELECT DATE(waktu_transaksi) AS tgl, SUM(total) AS total_harian
           FROM transaksi
          WHERE waktu_transaksi BETWEEN ? AND ?
          GROUP BY DATE(waktu_transaksi)
          ORDER BY tgl ASC"
    );
    $stmt->bind_param('ss', $mulai, $selesai);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function ambil_ringkasan(mysqli $db, string $mulai, string $selesai): array {
    $stmt = $db->prepare(
        "SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan,
                COALESCE(SUM(total), 0) AS omzet
           FROM transaksi
          WHERE waktu_transaksi BETWEEN ? AND ?"
    );
    $stmt->bind_param('ss', $mulai, $selesai);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/* ---------- Layout ---------- */

function page_header($title) {
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1">'
       . '<title>' . e($title) . '</title><link rel="stylesheet" href="style.css"></head><body>'
       . '<nav><b>Penjualan</b> <a href="index.php">Transaksi</a> <a href="barang.php">Barang</a> <a href="laporan.php">Laporan</a></nav>'
       . '<main><h1>' . e($title) . '</h1>';
    show_flash();
}

function page_footer() { echo '</main></body></html>'; }

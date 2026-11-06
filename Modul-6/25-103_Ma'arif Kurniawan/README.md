# Praktikum PAW Modul 6 – Master Detil (Penjualan)

## Cara menjalankan (XAMPP / Laragon)
1. Import `database.sql` lewat phpMyAdmin (tab SQL / Import) → terbentuk database `penjualan`.
2. Salin folder ini ke `htdocs/penjualan`.
3. Sesuaikan user/password MySQL di `config.php` (default: root, tanpa password).
4. Buka `http://localhost/penjualan/`.

## Pemetaan ke soal
| No | Soal | Lokasi |
|----|------|--------|
| 1 | Database + relasi + data (5 master, 10 transaksi) | `database.sql` |
| 2 | Master-detail transaksi, dropdown tanpa barang yang sudah dipilih | `detail.php` (query `NOT IN`, plus cek di server & PRIMARY KEY gabungan) |
| 3 | Hapus barang dengan cek pemakaian di `transaksi_detail` | `barang.php` (BRG007 belum terpakai → bisa dihapus; BRG001–006 → ditolak) |
| 4 | Simpan detail otomatis update `transaksi.total` | `hitung_ulang_total()` di `config.php`, dipanggil di `detail.php` |

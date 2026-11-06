DROP DATABASE IF EXISTS penjualan;
CREATE DATABASE penjualan CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE penjualan;

CREATE TABLE supplier (
  id     INT NOT NULL AUTO_INCREMENT,
  nama   VARCHAR(100) NOT NULL,
  telp   VARCHAR(12),
  alamat TEXT,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE barang (
  id          INT NOT NULL AUTO_INCREMENT,
  kode_barang VARCHAR(10) NOT NULL,
  nama_barang VARCHAR(100) NOT NULL,
  harga       INT NOT NULL,
  stok        INT NOT NULL DEFAULT 0,
  supplier_id INT NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_kode_barang (kode_barang),
  CONSTRAINT fk_barang_supplier FOREIGN KEY (supplier_id)
    REFERENCES supplier (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pelanggan (
  id            VARCHAR(20) NOT NULL,
  nama          VARCHAR(100) NOT NULL,
  jenis_kelamin ENUM('L','P') NOT NULL,
  telp          VARCHAR(12),
  alamat        TEXT,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE transaksi (
  id              INT NOT NULL AUTO_INCREMENT,
  waktu_transaksi DATE NOT NULL,
  keterangan      TEXT,
  total           INT NOT NULL DEFAULT 0,
  pelanggan_id    VARCHAR(20) NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_transaksi_pelanggan FOREIGN KEY (pelanggan_id)
    REFERENCES pelanggan (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE transaksi_detail (
  transaksi_id INT NOT NULL,
  barang_id    INT NOT NULL,
  harga        INT NOT NULL,
  qty          INT NOT NULL,
  PRIMARY KEY (transaksi_id, barang_id),
  CONSTRAINT fk_td_transaksi FOREIGN KEY (transaksi_id)
    REFERENCES transaksi (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_td_barang FOREIGN KEY (barang_id)
    REFERENCES barang (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pembayaran (
  id           INT NOT NULL AUTO_INCREMENT,
  waktu_bayar  DATETIME NOT NULL,
  total        INT NOT NULL,
  metode       ENUM('TUNAI','TRANSFER','EDC') NOT NULL,
  transaksi_id INT NOT NULL,
  PRIMARY KEY (id),
  CONSTRAINT fk_pembayaran_transaksi FOREIGN KEY (transaksi_id)
    REFERENCES transaksi (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user (
  id_user  TINYINT NOT NULL AUTO_INCREMENT,
  username VARCHAR(30) NOT NULL,
  password VARCHAR(35) NOT NULL,
  nama     VARCHAR(50),
  alamat   VARCHAR(150),
  hp       VARCHAR(20),
  level    TINYINT(1) NOT NULL DEFAULT 2,
  PRIMARY KEY (id_user),
  UNIQUE KEY uk_username (username)
) ENGINE=InnoDB;

INSERT INTO supplier (id, nama, telp, alamat) VALUES
(1,'PT Sumber Pangan','031555001','Jl. Raya Surabaya No. 10, Surabaya'),
(2,'CV Makmur Jaya','031555002','Jl. Ahmad Yani No. 22, Sidoarjo'),
(3,'UD Tani Subur','031555003','Jl. Pasar Baru No. 5, Bangkalan'),
(4,'PT Nusantara Kopi','031555004','Jl. Diponegoro No. 8, Malang'),
(5,'CV Berkah Abadi','031555005','Jl. Veteran No. 15, Gresik');

INSERT INTO barang (id, kode_barang, nama_barang, harga, stok, supplier_id) VALUES
(1,'BRG001','Beras Premium 5kg',62000,50,1),
(2,'BRG002','Minyak Goreng 2L',38000,40,2),
(3,'BRG003','Gula Pasir 1kg',17000,60,1),
(4,'BRG004','Telur Ayam 1kg',29000,30,3),
(5,'BRG005','Kopi Bubuk 200g',22000,45,4),
(6,'BRG006','Teh Celup isi 25',9000,80,5),
(7,'BRG007','Garam Dapur 500g',3000,100,5);

INSERT INTO pelanggan (id, nama, jenis_kelamin, telp, alamat) VALUES
('PLG001','Kadita','L','081230000001','Jl. Mawar No. 1, Bangkalan'),
('PLG002','Miya','P','081230000002','Jl. Melati No. 2, Bangkalan'),
('PLG003','zetian','L','081230000003','Jl. Kenanga No. 3, Sampang'),
('PLG004','Zhuxin','P','081230000004','Jl. Anggrek No. 4, Pamekasan'),
('PLG005','wanwan','L','081230000005','Jl. Dahlia No. 5, Sumenep');

INSERT INTO user (username, password, nama, alamat, hp, level) VALUES
('admin',  '0192023a7bbd73250516f069df18b500','Administrator','Bangkalan','081111111111',1),
('kasir1', 'de28f8f7998f23ab4194b51a6029416f','Kasir Satu','Bangkalan','081111111112',2),
('kasir2', '2aaeb59d86791fb6cb096aaa72893405','Kasir Dua','Sampang','081111111113',2),
('gudang', 'cbb7449d78314665f9e7c7dd0a18a68a','Petugas Gudang','Bangkalan','081111111114',3),
('owner',  '5be057accb25758101fa5eadbbd79503','Pemilik Toko','Surabaya','081111111115',1);

INSERT INTO transaksi (id, waktu_transaksi, keterangan, total, pelanggan_id) VALUES
(1,'2026-10-01','Belanja bulanan',141000,'PLG001'),
(2,'2026-10-03','Kebutuhan dapur',65000,'PLG002'),
(3,'2026-10-05','Pembelian telur',58000,'PLG003'),
(4,'2026-10-08','Stok warung kopi',78000,'PLG004'),
(5,'2026-10-10','Belanja mingguan',167000,'PLG005'),
(6,'2026-10-13','Pembelian teh',45000,'PLG001'),
(7,'2026-10-16','Kopi dan gula',73000,'PLG002'),
(8,'2026-10-20','Minyak goreng grosir',114000,'PLG003'),
(9,'2026-10-24','Beras dan teh',204000,'PLG004'),
(10,'2026-10-28','Belanja akhir bulan',112000,'PLG005');

INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES
(1,1,62000,2),(1,3,17000,1),
(2,2,38000,1),(2,6,9000,3),
(3,4,29000,2),
(4,5,22000,2),(4,3,17000,2),
(5,1,62000,1),(5,2,38000,2),(5,4,29000,1),
(6,6,9000,5),
(7,3,17000,3),(7,5,22000,1),
(8,2,38000,3),
(9,1,62000,3),(9,6,9000,2),
(10,4,29000,1),(10,5,22000,3),(10,3,17000,1);

INSERT INTO pembayaran (waktu_bayar, total, metode, transaksi_id) VALUES
('2026-10-01 10:15:00',141000,'TUNAI',1),
('2026-10-03 14:30:00',65000,'TRANSFER',2),
('2026-10-05 09:05:00',58000,'TUNAI',3),
('2026-10-08 16:45:00',78000,'EDC',4),
('2026-10-10 11:20:00',167000,'TRANSFER',5);
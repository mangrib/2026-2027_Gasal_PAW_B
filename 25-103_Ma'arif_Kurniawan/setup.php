<?php
$conn = mysqli_connect("localhost", "root", "");
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$query = "CREATE DATABASE IF NOT EXISTS modul5";
if (mysqli_query($conn, $query)) {
  echo "Database created successfully<br>";
} else {
  echo "Error creating database: " . mysqli_error($conn);
}

$conn = mysqli_connect("localhost", "root", "", "modul5");

$query = "CREATE TABLE IF NOT EXISTS supplier (
  id INT(11) AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(255) NOT NULL,
  telp VARCHAR(20) NOT NULL,
  alamat VARCHAR(255) NOT NULL
)";
if (mysqli_query($conn, $query)) {
  echo "Table supplier berhasil dibuat!<br>";
} else {
  echo "Error: " . mysqli_error($conn);
}

$query  = "INSERT INTO supplier (nama, telp, alamat)
           VALUES ('PT. Maju Bersama', '08123456', 'Surabaya');";
$query .= "INSERT INTO supplier (nama, telp, alamat)
           VALUES ('PT. Senang Sekali', '081515563', 'Bangkalan');";
$query .= "INSERT INTO supplier (nama, telp, alamat)
           VALUES ('PT. Segar Segar', '0845454663', 'Surabaya');";

if (mysqli_multi_query($conn, $query)) {
  echo "Data baru berhasil ditambahkan!";
} else {
  echo "Error: " . mysqli_error($conn);
}
?>

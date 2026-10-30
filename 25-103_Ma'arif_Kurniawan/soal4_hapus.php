<?php
include "koneksi.php";

$id = (int) $_GET["id"];

if (isset($_GET["konfirmasi"])) {
  $query = "DELETE FROM supplier WHERE id=$id";

  if (mysqli_query($conn, $query)) {
    header("Location: soal1_tampil.php");
    exit;
  } else {
    echo "Error: " . mysqli_error($conn);
    exit;
  }
}
?>
<!DOCTYPE html>
<html>
<head><title>Hapus Supplier</title></head>
<body>
<script>
  if (confirm("Anda yakin akan menghapus supplier ini?")) {
    window.location.href = "soal4_hapus.php?id=<?= $id ?>&konfirmasi=1";
  } else {
    window.location.href = "soal1_tampil.php";
  }
</script>
</body>
</html>

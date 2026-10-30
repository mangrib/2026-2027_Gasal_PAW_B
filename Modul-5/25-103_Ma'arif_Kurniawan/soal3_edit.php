<?php
include "koneksi.php";

$id = (int) $_GET["id"];

if (isset($_POST["update"])) {
  $nama   = mysqli_real_escape_string($conn, $_POST["nama"]);
  $telp   = mysqli_real_escape_string($conn, $_POST["telp"]);
  $alamat = mysqli_real_escape_string($conn, $_POST["alamat"]);

  $query = "UPDATE supplier SET nama='$nama', telp='$telp', alamat='$alamat' WHERE id=$id";

  if (mysqli_query($conn, $query)) {
    header("Location: soal1_tampil.php");
    exit;
  } else {
    echo "Error: " . mysqli_error($conn);
  }
}

$result = mysqli_query($conn, "SELECT id, nama, telp, alamat FROM supplier WHERE id=$id");
$row    = mysqli_fetch_assoc($result);
if (!$row) {
  die("Data tidak ditemukan. <a href='soal1_tampil.php'>Kembali</a>");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Data Master Supplier</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
  <h2 style="color:#337ab7">Edit Data Master Supplier</h2>
  <hr>
  <form method="post" class="form-horizontal">
    <div class="form-group">
      <label class="col-sm-4 control-label">Nama</label>
      <div class="col-sm-5">
        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($row['nama']) ?>" required>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">Telp</label>
      <div class="col-sm-3">
        <input type="text" name="telp" class="form-control" value="<?= htmlspecialchars($row['telp']) ?>" required>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-4 control-label">Alamat</label>
      <div class="col-sm-3">
        <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($row['alamat']) ?>" required>
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-offset-4 col-sm-5">
        <button type="submit" name="update" class="btn btn-success">Update</button>
        <a href="soal1_tampil.php" class="btn btn-danger">Batal</a>
      </div>
    </div>
  </form>
</div>
</body>
</html>

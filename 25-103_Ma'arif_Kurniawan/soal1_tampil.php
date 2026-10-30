<?php include "koneksi.php"; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Data Master Supplier</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
  <div class="page-header clearfix" style="border:0">
    <a href="soal2_tambah.php" class="btn btn-success btn-sm pull-right" style="margin-top:20px">Tambah Data</a>
    <h3 style="color:#337ab7">Data Master Supplier</h3>
    <hr>
  </div>

  <table class="table table-bordered table-hover">
    <thead>
      <tr class="info">
        <th>No</th>
        <th>Nama</th>
        <th>Telp</th>
        <th>Alamat</th>
        <th>Tindakan</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $query  = "SELECT id, nama, telp, alamat FROM supplier";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
      $no = 1;
      while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($row["nama"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["telp"]) . "</td>";
        echo "<td>" . htmlspecialchars($row["alamat"]) . "</td>";
        echo "<td>
                <a href='soal3_edit.php?id=" . $row["id"] . "' class='btn btn-warning btn-sm' style='background:#e8590c;border-color:#e8590c'>Edit</a>
                <a href='soal4_hapus.php?id=" . $row["id"] . "' class='btn btn-danger btn-sm'>Hapus</a>
              </td>";
        echo "</tr>";
      }
    } else {
      echo "<tr><td colspan='5' class='text-center'>0 results</td></tr>";
    }
    ?>
    </tbody>
  </table>
</div>
</body>
</html>

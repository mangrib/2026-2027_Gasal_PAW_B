<?php
require 'config.php';
require_login();
page_header('Home');
?>
<div class="card">
  <h2 style="margin-top:0">Selamat datang, <?= e($_SESSION['nama']) ?></h2>
  <p>Anda login sebagai <b><?= (int)$_SESSION['level'] === 1 ? 'Owner' : 'Kasir' ?></b>.</p>
</div>
<?php page_footer();

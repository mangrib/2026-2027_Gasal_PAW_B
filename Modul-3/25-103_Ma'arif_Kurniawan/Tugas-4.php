<?php
$height = array("Andy"=>"176", "Barry"=>"165", "Charlie"=>"170");

$height["David"]  = "180";
$height["Ethan"]  = "172";
$height["Frank"]  = "168";
$height["George"] = "175";
$height["Harry"]  = "182";

$hasil = array();
foreach ($height as $nama => $tinggi) {
    $hasil[] = '"' . $nama . '"=>"' . $tinggi . '"';
}
echo 'height = (' . implode(', ', $hasil) . ')<br><br>';

foreach ($height as $nama => $tinggi) {
    echo $nama . " is " . $tinggi . " cm tall.<br>";
}

echo "<hr>";

$weight = array("Andy"=>"70", "Barry"=>"65", "Charlie"=>"75");

echo 'weight = ("Andy"=>"70", "Barry"=>"65", "Charlie"=>"75")<br><br>';

$kunci = array_keys($weight);
$jumlah = count($kunci);

for ($i = 0; $i < $jumlah; $i++) {
    $nama = $kunci[$i];
    echo $nama . " is " . $weight[$nama] . " kg.<br>";
}
?>

<?php
function tampilHeight($data) {
    $hasil = array();
    foreach ($data as $nama => $tinggi) {
        $hasil[] = '"' . $nama . '"=>"' . $tinggi . '"';
    }
    return 'height = (' . implode(', ', $hasil) . ')';
}

$height = array("Andy"=>"176", "Barry"=>"165", "Charlie"=>"170");

$height["David"]  = "180";
$height["Ethan"]  = "172";
$height["Frank"]  = "168";
$height["George"] = "175";
$height["Harry"]  = "182";

echo tampilHeight($height) . "<br>";
$kunciTerakhir = array_key_last($height);
echo "Nilai dengan indeks terakhir: " . $height[$kunciTerakhir] . "<br><br>";

unset($height["Barry"]);

echo tampilHeight($height) . "<br>";
$kunciTerakhir = array_key_last($height);
echo "Nilai dengan indeks terakhir setelah dihapus: " . $height[$kunciTerakhir];

echo "<hr>";

$weight = array("Andy"=>"70", "Barry"=>"65", "Charlie"=>"75");

echo 'weight = ("Andy"=>"70", "Barry"=>"65", "Charlie"=>"75")<br>';

$nilai = array_values($weight);
echo "Data kedua: " . $nilai[1];
?>

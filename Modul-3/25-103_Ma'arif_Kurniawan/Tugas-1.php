<?php
$fruits = array("Avocado", "Blueberry", "Cherry");

array_push($fruits, "Durian", "Elderberry", "Fig", "Grape", "Honeydew");

echo 'fruits = ("' . implode('", "', $fruits) . '")<br>';

$indeksTertinggi = array_key_last($fruits);
echo "Nilai dengan indeks tertinggi: " . $fruits[$indeksTertinggi];

echo "<hr>";

$fruits = array("Avocado", "Blueberry", "Cherry");

array_push($fruits, "Durian", "Elderberry", "Fig", "Grape", "Honeydew");

echo "Data " . $fruits[1] . " dihapus.<br>";
unset($fruits[1]);

echo 'fruits = ("' . implode('", "', $fruits) . '")<br>';

$indeksTertinggi = array_key_last($fruits);
echo "Nilai dengan indeks tertinggi: " . $fruits[$indeksTertinggi];
?>

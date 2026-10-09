<?php
function tampilAsosiatif($data) {
    foreach ($data as $kunci => $nilai) {
        echo $kunci . "=> " . $nilai . ", ";
    }
    echo "<br>";
}

$a = array("A");
array_push($a, "B");
echo 'Array awal: ("A")<br>';
echo "Hasil array_push: " . implode(" ", $a) . "<br><br>";

$b1 = array("A", "B");
$b2 = array("C");
$b = array_merge($b1, $b2);
echo 'Array awal: ("A", "B") digabung dengan ("C")<br>';
echo "Hasil array_merge: " . implode(" ", $b) . "<br><br>";

$c = array("x" => 1, "y" => 2);
echo 'Array awal: ("x" => 1, "y" => 2)<br>';
echo "Hasil array_values: " . implode(" ", array_values($c)) . "<br><br>";

$d = array("A", "B", "C");
echo 'Mencari "B" pada array: ("A", "B", "C")<br>';
echo "Hasil array_search: " . array_search("B", $d) . "<br><br>";

$e = array(0, 1, false, 2, "", 3, "array");
echo 'Array awal: (0, 1, false, 2, "", 3, "array")<br>';
echo "Hasil array_filter: " . implode(" ", array_filter($e)) . "<br><br>";

$f = array(3, 1, 2);
echo "Array awal: (3, 1, 2)<br>";
sort($f);
echo "Hasil sort: " . implode(" ", $f) . "<br>";
rsort($f);
echo "Hasil rsort: " . implode(" ", $f) . "<br><br>";

$g = array("Peter" => 35, "Ben" => 37, "Joe" => 43);
echo 'Array awal: ("Peter"=>35, "Ben"=>37, "Joe"=>43)<br>';

$h = $g; asort($h);
echo "Hasil asort: ";  tampilAsosiatif($h);

$h = $g; ksort($h);
echo "Hasil ksort: ";  tampilAsosiatif($h);

$h = $g; arsort($h);
echo "Hasil arsort: "; tampilAsosiatif($h);

$h = $g; krsort($h);
echo "Hasil krsort: "; tampilAsosiatif($h);
?>

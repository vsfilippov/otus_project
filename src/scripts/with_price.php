<?php

$file = 'SWAT.csv';
$url = 'http://ad.propellerads.com/conversion.php?visitor_id=%s&price=%f&trx=retry';

$csv = array_map('str_getcsv', file($file));
$count = 0;
foreach ($csv as $value) {
    $result = file_get_contents(sprintf($url, ...$value));
    file_put_contents('result.txt', $result . ' ' . $value[0] . " \n", FILE_APPEND);
    $count++;
    echo $count . " \n";
}

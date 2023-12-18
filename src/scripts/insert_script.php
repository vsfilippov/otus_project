<?php

$csvFile = 'iratio.csv';
$tableName = 'mnt_iratio';

$file = fopen($csvFile, 'r');
if (!$file) {
    die("Не удалось открыть файл $csvFile");
}

$headers = fgetcsv($file);

$insertQueries = [];
while (($data = fgetcsv($file)) !== false) {
    $values = array_map(function($value) {
        return "'" . addslashes($value) . "'";
    }, $data);
    $insertQueries[] = "INSERT INTO $tableName (" . implode(', ', $headers) . ") VALUES (" . implode(', ', $values) . ");";
}
fclose($file);

foreach ($insertQueries as $query) {
    echo $query . "\n";
}

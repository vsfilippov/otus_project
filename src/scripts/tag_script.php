<?php

function hasViewportMeta($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'authority: appsgag.com',
        'accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
        'accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
        'cache-control: max-age=0',
        'cookie: _gid=GA1.2.1255702736.1690283710; _ga_ZEQG8HJ5SF=GS1.1.1690283709.1.1.1690283872.0.0.0; _ga=GA1.2.1729852180.1690283709; XSRF-TOKEN=eyJpdiI6IkM1TjN2bEJ2bVJWVmJTOFJ0K1owQ2c9PSIsInZhbHVlIjoiQVlDanl6YW5GcXVueVZPM296cUhRVWc2a25vdGlOQmR3MTVrbGc4TW9aSHdGTk93Tyt4SFF5bkNVRjZWaUhTdE04VnUxK2ttZVRpZVcxTGtueFhHZ0RoaWFRWko2YXk0RVFSVjZacG5kQWVwZE1JNDVvUStBNThTZ3RsOERHTUMiLCJtYWMiOiIyMDY1ZjM3ODc4NWViYTliN2NmYjUzYjBiNjNlNzM2ZGY5ZDJhZTgxYTEyOTA0ZmZlOWQ1Njc4NTJjODViMTUyIiwidGFnIjoiIn0%3D; laravel_session=eyJpdiI6IlU0NWwyUmI4Q2I1SGRDQVMwWHlBbHc9PSIsInZhbHVlIjoiRG0xTHhKNGQ1eE5Zd3RsamdSVWJrQllvdXA1YmZ3VnFKS3dJaXB2NUIvdHlZeXRISkZLSnZqQmwwRUZ1d2orM1lBRDRMYWxjR2RJVHQyWlQyaGpYcDQvK3gzcTRXemp0THVYaHpxYnkxRXJLK3MrYkJneUtNSnBoOGRZRTdCbk0iLCJtYWMiOiJiMTg2MDAzNjk5M2E1MjEwYTk0NjBhY2U4ZmVhNmY3YmM4NzAyNzk4YjgwMGY2YzlmZDBiZmYwMjA4NDJjNmYxIiwidGFnIjoiIn0%3D',
        'referer: https://www.google.com/',
        'sec-ch-ua: "Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"',
        'sec-ch-ua-mobile: ?0',
        'sec-ch-ua-platform: "macOS"',
        'sec-fetch-dest: document',
        'sec-fetch-mode: navigate',
        'sec-fetch-site: cross-site',
        'sec-fetch-user: ?1',
        'upgrade-insecure-requests: 1',
        'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
    ));

    $html = curl_exec($ch);

    if (curl_errno($ch)) {
        $error_message = 'cURL Error: ' . curl_error($ch);
        curl_close($ch);
        return array('error' => $error_message);
    }

    curl_close($ch);

    $doc = new DOMDocument();
    if (!empty($html)) {
        @$doc->loadHTML($html);
        $metaTags = $doc->getElementsByTagName('meta');
        $viewportMetaFound = false;
        foreach ($metaTags as $metaTag) {
            if (strtolower($metaTag->getAttribute('name')) === 'viewport') {
                $viewportMetaFound = true;
                break;
            }
        }

        return array('meta_tag' => $viewportMetaFound ? 'meta_tag yes' : 'meta_tag no');
    }

    return array('error' => 'Empty HTML content');
}

$csvFile = 'meta_tag.csv';
$outputFile = 'output.csv';

if (($handle = fopen($csvFile, 'r')) !== false) {
    if (($outputHandle = fopen($outputFile, 'w')) !== false) {
        fputcsv($outputHandle, ['zone_id', 'RF', 'DRF', 'meta_tag', 'error_message']);

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $zone_id = $data[0];
            $rf = $data[1];
            $drf = isset($data[2]) ? $data[2] : '';

            $rf_result = hasViewportMeta($rf);
            $drf_result = !empty($drf) ? hasViewportMeta($drf) : '';

            fputcsv($outputHandle, [$zone_id, $rf, $drf, $rf_result['meta_tag'], isset($drf_result['meta_tag']) ? $drf_result['meta_tag'] : '', isset($rf_result['error']) ? $rf_result['error'] : '', isset($drf_result['error']) ? $drf_result['error'] : '']);
        }

        fclose($outputHandle);
    } else {
        echo "Error opening the output file.";
    }

    fclose($handle);
} else {
    echo "Error opening the CSV file.";
}

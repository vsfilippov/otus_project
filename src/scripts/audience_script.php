%<?php
//export oaids to this files !
//$oaidsByDirection['onclick'] = file('./oaids_onclick.csv');
$oaidsByDirection['push'] = file('oaids_push.csv');
//$oaidsByDirection['interstitial'] = file('./oaids_interstitial.csv');

//change it
$audienceId = 128357;

//echo 'Onclick OAIDS count: ' . count($oaidsByDirection['onclick']) . PHP_EOL;
echo 'Push OAIDS count: ' . count($oaidsByDirection['push']) . PHP_EOL;
//echo 'Intersitial OAIDS count: ' . count($oaidsByDirection['interstitial']) . PHP_EOL;
echo 'Audience ID: ' . $audienceId . PHP_EOL;

//Uncomment it on first run for check before real start
//die;

foreach ($oaidsByDirection as $direction => $oaids) {
    foreach ($oaids as $key => $oaid) {
        $oaid = trim($oaid);
        addOaidToAuience($audienceId, $oaid);
        echo $direction . ': pass ' . $key . ' from ' . count($oaids) . PHP_EOL;
    }
}

function addOaidToAuience($audienceId, $oaid)
{
    $url = "http://1userdata31.rtty.in:2394/add_audience?user_id=$oaid&audience=$audienceId&ttl=7776000";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $res = curl_exec($ch);
}

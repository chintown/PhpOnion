<?php

// http://developer.apple.com/library/ios/#documentation/NetworkingInternet/Conceptual/StoreKitGuide/VerifyingStoreReceipts/VerifyingStoreReceipts.html

//$cwd = dirname(__FILE__).'/';
//require $cwd . '/../config/path.php';

function is_iap_valid($receipt_in_base64){
    $receipt = json_encode(array("receipt-data" => $receipt_in_base64));
    $response_json = getResponseHeader(APPLE_RECEIPT_URI, $receipt);
    $response = json_decode($response_json['content'], true);
    return $response['status'] === 0 || $response['status'] === 21007;
}
function getResponseHeader($url, $data){
    $ch = curl_init();
    $timeout = 300;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $handles = curl_exec($ch);
    $header = curl_getinfo($ch);
    curl_close($ch);
    $header['content'] = $handles;
    return $header;
}
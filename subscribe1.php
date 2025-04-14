<?php
session_start();

// Data for transmission
$ip = $_SERVER['REMOTE_ADDR'];
$name = $_POST['name'];
$phone = $_POST['phone'];
$campaign_id = 560228;
$country_code = 'MX';

$order = array(
    'campaign_id' => $campaign_id,
    'ip' => $ip,
    'name' => $name,
    'phone' => $phone,
    'country_code' => $country_code,
);

$orderSerialized = "";
foreach ($_POST as $key => $value) {
    if ($value != '') {
        $order[$key] = $value;
        $orderSerialized .= "'" . $key . "':'" . $value . "',";
    }
}

$orderSerialized = "{" . rtrim($orderSerialized, ',') . "},
";
$ordersFile = fopen("orders.txt", "a");
fwrite($ordersFile, $orderSerialized);
fclose($ordersFile);

$order['is_smart_form'] = true;
// Define IP address
if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
$ip =  $_SERVER['HTTP_CF_CONNECTING_IP'];
}  elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
$ip =  $_SERVER['HTTP_X_REAL_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
$ip =  $_SERVER['REMOTE_ADDR'];
}

$ips = explode(",", $ip);
$order['ip'] = trim($ips[0]);

$parsed_referer = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY);
parse_str($parsed_referer, $referer_query);

$ch = curl_init();
$submit_url = 'https://tracker.rocketprofit.com/conversion/new';
$_SESSION['submit_url'] = $submit_url;

curl_setopt($ch, CURLOPT_URL, $submit_url );
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt($ch, CURLOPT_POST,          1 );
curl_setopt($ch, CURLOPT_POSTFIELDS,    http_build_query(array_merge($referer_query, $order)) );
curl_setopt($ch, CURLOPT_HTTPHEADER,      array('Content-Type: application/x-www-form-urlencoded'));

$result = curl_exec ($ch);
$error = false;

if ($result === false) {
    $error = "cURL Error: " . curl_error($ch);
} else {
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($httpCode === 400) {
    $error = "Order data is invalid! Order is not accepted!";
} else if ($httpCode === 423) {
    header('Location: ' . $_SERVER['HTTP_REFERER'] . '?is_pending_order_check_failed=true');
    exit();
} else if ($httpCode === 401) {
    $error = "Order is not accepted! No campaign_id.";
} else if ($httpCode === 403) {
    $error = "Order is not accepted! Restricted GEO. Please submit your order from another GEO.";
} else if ($httpCode !== 200) {
    $error = "Order is not accepted! Invalid or incomplete data. Please contact support. HTTP Code: " . $httpCode;
} else {
      foreach (json_decode($result, true) as $key => $value) {
    if ($key === "id") {
      $_SESSION['conversion_id'] = $value;
    }
   }
}}
curl_close ($ch);
if ($error) {header('Location: ' . $_SERVER['HTTP_REFERER'] . '?is_blacklist_error=true&error_message=' . urlencode($error));
exit();
}
?>
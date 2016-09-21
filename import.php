#!/usr/sbin/php
<?php
require_once('poloniex.php');

$api_key = "Y0E1JVKD-PF0RCBVE-I4OO8K28-YJIHIQJ7";
$api_secret = "c31279732b0a375c6645e05a8f7233f3c883198a8195e8f002418258a50152fd4342f8b2db2f1ac771d31fcad154ab61499debf9b9fba69e6e690ce678da1002";

$conn = new PDO('mysql:host=localhost;dbname=polo;charset=utf8mb4', 'root', '');
$conn->exec("SET CHARACTER SET utf8");

$p = new poloniex($api_key, $api_secret);

$allary = $p->get_ticker();
$date = date('Y-m-d H:i:s');
$lastID = array();
foreach ($allary as $key => $all) {
//    print $key . "\n";

    $str = sprintf("INSERT INTO hist VALUES(%f,'%s',%10.8f,%10.8f,%10.8f,%10.8f,%10.8f,%10.8f,%10.8f,%10.8f,%10.8f, '%s');", 0, $key, $all['last'], $all['lowestAsk'], $all['highestBid'], $all['percentChange'], $all['baseVolume'], $all['quoteVolume'], $all['isFrozen'], $all['high24hr'], $all['low24hr'], $date);
    print $str . "\n";
    $st = $conn->prepare($str);
    $st->execute();
}
$str = "SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'polo' AND TABLE_NAME = 'hist'";
$st = $conn->prepare($str);
$st->execute();
$lastID = $st->fetch();
//    $newid = mysql_insert_id();
file_put_contents("/home/jw/src/macd4/trans/_lastID", $lastID[0] . "\n");

#!/usr/sbin/php

<?php
$v = 0;
if (isset($argv[1])) {
$v =1 ;    
}
$conn = new PDO('mysql:host=localhost;dbname=polo;charset=utf8mb4', 'root', '');
$conn->exec("SET CHARACTER SET utf8");

$c = file_get_contents(".alarms");

$pairs = json_decode($c, TRUE);

$be = 1.1;


foreach ($pairs as $pairc => $val) {

    $currary = explode(':', $pairc);
    $currname = $currary[0];
    $currticker = $currary[1];

//    var_export($currary);exit;
    $str = "SELECT * FROM (SELECT * FROM hist where name = '${currticker}'  ORDER BY id DESC LIMIT 1) sub ORDER BY id ASC";
    $st = $conn->prepare($str);
    $st->execute();
    $currentary = $st->fetchall();
    $current = $currentary[0];

    $pct = percent($val, $current['last']);
//    $pct = percent($val, $current['highestBid']);
//    $pct = -100;
    $stat = "${current['name']} : bouhght at : ${val}, currently: ${current['last']} $pct sell at ${current['highestBid']}";

    switch (true) {
        case $pct < -6:   // ????
            $say = "CRASH CRASH CRASH ${currname}  $pct%";
            if ($v > 0) {
                system("echo '${say}' | festival --tts");
            }
            print r($stat) . "\n";
            break;

        case $pct < 0:   // ????
            $say = "DOWN DOWN DOWN ${currname}  $pct%";
            if ($v > 0) {
                system("echo '${say}' | festival --tts");
            }
            print r($stat) . "\n";
            break;

        case $pct > 10:
            $say = "SELL SELL SELL ${currname}  $pct%";
            system("echo '${say}' | festival --tts");
            print g($stat) . "\n";
            break;
        
        case $pct > 0:
            $say = "UP UP UP ${currname}  $pct%";
            system("echo '${say}' | festival --tts");
            print g($stat) . "\n";
            break;

        default:
            $say = "FLAT ${currname}  $pct%";
            system("echo '${say}' | festival --tts");
            print $stat . "\n";
            break;
    }
}

function percent($cost, $value) {
    $r = (($value - $cost) / $cost) * 100;

    $ret = sprintf("%6.2f", $r);
    return ($ret);



//        return number_format((1 - $cost / $value) * 100, 2);
}

// *****************************************************************************
function r($str) {
    $cstr = "";
    if (PHP_SAPI === 'cli') {
        $cstr = "\033[31m${str}\033[0m";
    } else {
        $cstr = "<span style='color:red'>${str}</span>";
    }
    return($cstr);
}

// *****************************************************************************
function g($str) {
    $cstr = "";
    if (PHP_SAPI === 'cli') {
        $cstr = "\033[32m${str}\033[0m";
    } else {

        $cstr = "<span style='color:green'>${str}</span>";
    }
    return($cstr);
}

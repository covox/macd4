<?php


$c = array(
        'bidcredit'=>1,
        'askcredit'=>0);

$s = array(
        'BTC'=>1,
        'shares'=>0);


file_put_contents(".credits", json_encode($c));
file_put_contents(".btcshares", json_encode($s));

//$f = json_decode(file_get_contents(".credits"),TRUE);
//
//$fa['askc'] = $f['askc'];
//
//var_export ($f);
//$c = json_decode(file_get_contents(".btcshares"),TRUE);
//var_export ($c);
//
print "-------------\n";
for ($i = 30; $i < 4000; $i++) {
    $cmd = "php ./macd4.php -f23 -s14 -S9 -pBTC_XMR -c1 -x1 -U0 -D6 -z1 -a4 -b2 -F${i} -ml";
//    $cmd = "php ./macd4.php -f23 -s14 -S9 -pBTC_DOGE -c1 -x1 -U1.25 -D6 -ml -z1 -a0 -b0 -F${i}";
    print "$i -";
    system($cmd);
}

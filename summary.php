<?php

require_once('poloniex.php');
// mine
$api_key = "Y0E1JVKD-PF0RCBVE-I4OO8K28-YJIHIQJ7";
$api_secret = "c31279732b0a375c6645e05a8f7233f3c883198a8195e8f002418258a50152fd4342f8b2db2f1ac771d31fcad154ab61499debf9b9fba69e6e690ce678da1002";

//victoria
//$api_key = "9N0YMXPR-GWTMPELB-N532V3PW-O9CUKE9J";
//$api_secret = "af1875e7f56dc7ec7ff400913528c2e84fb8609cf5eda6ad93694be8335fcfb9a127bee9a5c2fb57b9ea5303edc103f00e98319ca4a72c135f1edd53bf7d750f";

$p = new poloniex($api_key, $api_secret);

// get btc value
$btcy = $p->get_ticker("USDT_BTC");
$btcv = $btcy['last'];

// get shares owned
$cbaly = $p->get_complete_balances();

$btc_account = 0;
$cbalyv = array();
foreach ($cbaly as $k => $c) {
    if (($c['available'] + $c['onOrders']) != 0) {
        if ($k != "BTC") {
            $cbalyv[$k] = $c;
        } else {
            $btc_account = $c['available'] + $c['onOrders'];
        }
    }
}

$trades = array();


foreach ($cbalyv as $k => $val) {
    $trades[$k]['shares'] = 0;
    $trades[$k]['cost'] = 0;
    $trades[$k]['fee'] = 0;
    $trades[$k]['avgprice'] = 0;
    $cp = "BTC_" . $k;
    
    // DASH is broken
    
    if ($hist = $p->get_my_trade_history($cp)) {
        foreach ($hist as $h) {
            if ($h['type'] == "buy") {
                $trades[$k]['shares'] += $h['amount'];
                $trades[$k]['cost'] += $h['total'];
                $trades[$k]['fee'] += $h['fee'];
            }
            if ($h['type'] == "sell") {
                $trades[$k]['shares'] -= $h['amount'];
                $trades[$k]['cost'] -= $h['total'];
                $trades[$k]['fee'] += $h['fee'];
            }
            if ($trades[$k]['shares'] > 0) {
                $trades[$k]['avgprice'] = $trades[$k]['cost'] / $trades[$k]['shares'];
            } else {
                $trades[$k]['avgprice'] = 0;
            }
        }
    } else {
        if ($cp == "BTC_DASH") {
                // it's a but we we ad manually
                $trades[$k]['shares']  += 13.05040745;
                $trades[$k]['cost'] += ($trades[$k]['shares'] * 0.01915649) + $trades[$k]['fee'];
                $trades[$k]['fee'] += 0.01957561 ;
//                $trades[$k]['rate'] += 0.01915649;

                $trades[$k]['avgprice'] = $trades[$k]['cost']/$trades[$k]['shares'];
                
                //var_dump($trades[$k]);
        }
        print "ERROR: Failed getting trade history on ${cp}\n";
    }
}

//print_r($trades);
////
//exit;
$totalBTCval = 0;
$totalCurrentPercent = 0;
$t_pct = 0;
$t_cost = 0;
$t_value = 0;

//      "     BCN      10000       0.0035  0.000000350     0.00350000      1.00";

print( "    ----      ----          ---------       ----              -----     -----   ----             \n");
print( "    PAIR      SHRS          PUR $$          CUR $$            TOTCOST      TOTVALUE    P/L         \n");
print( "                            (avg)                             (w/fee)                        \n");
print( "    ----      ----          ---------       ----              -----      -----  ----            \n");

//usort($trades, "cmpLoss");


foreach ($trades as $k => $val) {
    $currentValue = $p->get_ticker("BTC_" . $k);
    //print("current proce [BTC_${k}]: ".$currentValue['last']);
    $pct = 0;
//    $value = $val['shares'] * $currentValue['last'];
    $value = $cbaly[$k]['btcValue'];
    if (($val['shares']) || ($val['avgprice']) > 0) {
        $pct = percent($val['cost'],$value);
    } else {
        $pct=0;
    }

    $rs = sprintf("%8s     %6.0f       %9.9f      [%9.9f]     %8.8f     %9.8f     %5.2f %%\n", $k, $val['shares'], $val['avgprice'], $currentValue['last'], $val['cost'], $value, $pct
    );

    print($rs);

    $t_cost += $val['cost'];    
    $t_value += $value;
}
print "\n";

$btc_value = file_get_contents("https://api.bitcoinaverage.com/ticker/USD/last");

print "PCT: \t" . percent($t_cost,$t_value). " %\n";
print "COST: \t".number_format($t_cost,2)." \t($".number_format($t_cost * $btc_value,2).")\n";
print "VALUE: \t".number_format($t_value,2)."\t($".number_format($t_value * $btc_value,2).")\n";
print "\n";

$org = 1.001;

print "BTC: \t".number_format($btc_account,4)." \t($".number_format($btc_account * $btc_value,2).")   \n";
print "TOTAL: \t".(number_format($btc_account+$t_value,4))." \t($".number_format(($btc_account+$t_value) * $btc_value,2).") (". percent($org,$btc_account+$t_value) ." %)\n";
//
//foreach ($cbalyv as $k => $c) {
//    $shares = $c['available'] + $c['btcValue'] + $c['onOrders'];
//    $pctchange = $c['btcValue'] / $c['totbtcinv'];
//    $rs = sprintf("%8s %8.1f %1.8f %6.8f %6.8f  %2.3f \n", $k, $shares, $c['purprice'], $c['totbtcinv'], $c['btcValue'], (($c['btcValue'] / $c['totbtcinv']) * 100 ) - 100);
//    print($rs);
//}
//print("------------------------------------------------\n");
//print "TOTAL: ${totalBTCval}   $".$totalBTCval * $btcv."    AVG % ".$totalCurrentPercent/count($pairy)." ($".($totalBTCval * $btcv)*(($totalCurrentPercent/count($pairy))/100).") \n";
//print "Current BTC valus: ${btcv}\n";
//print("------------------------------------------------\n");



function percentx($cost, $value) {
    $count1 = $cost / $value;
    $count2 = $count1 * 100;
    $count3 = 100 - $count2;
    $count = number_format($count3, 2);
    return $count;
}
function percenty($cost, $value) {
    return number_format((($cost - $value)/$cost)*100,2);
}
function percentz($cost, $value) {
    return number_format(100*($cost-$value)/$cost,10);
}


function percenta($cost, $value) {
    $a=$cost;
    $b=$value;
    $c = ($a > $b) ? ($a-$b)/$a*-100 : ($b-$a)/$b*100;  
    return number_format($c,2);
    
}



function percent($cost, $value) {
    return number_format((1 - $cost / $value) * 100, 2); 
}
  function cmpLoss($a, $b) {
//    return strcmp($a["baseVolume"], $b["baseVolume"]);
        return $a["cost"] < $b["cost"];
    }        

    function unique_multidim_array($array, $key) {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
?>

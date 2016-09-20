<?php

require_once('poloniex.php');
// mine
$api_key = "Y0E1JVKD-PF0RCBVE-I4OO8K28-YJIHIQJ7";
$api_secret = "c31279732b0a375c6645e05a8f7233f3c883198a8195e8f002418258a50152fd4342f8b2db2f1ac771d31fcad154ab61499debf9b9fba69e6e690ce678da1002";

//victoria
//$api_key = "9N0YMXPR-GWTMPELB-N532V3PW-O9CUKE9J";
//$api_secret = "af1875e7f56dc7ec7ff400913528c2e84fb8609cf5eda6ad93694be8335fcfb9a127bee9a5c2fb57b9ea5303edc103f00e98319ca4a72c135f1edd53bf7d750f";

$p = new poloniex($api_key, $api_secret);
$cbaly = $p->get_complete_balances();
$pair = "GRC";
print_r($cbaly[$pair]['onOrders']);
?>

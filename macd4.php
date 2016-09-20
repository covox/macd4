<?php
set_time_limit(180);

if (PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
    
} else {
    print "<pre>";
}
require_once('poloniex.php');
require_once 'phplot/phplot.php';
// mine
$api_key = "Y0E1JVKD-PF0RCBVE-I4OO8K28-YJIHIQJ7";
$api_secret = "c31279732b0a375c6645e05a8f7233f3c883198a8195e8f002418258a50152fd4342f8b2db2f1ac771d31fcad154ab61499debf9b9fba69e6e690ce678da1002";

//victoria
//$api_key = "9N0YMXPR-GWTMPELB-N532V3PW-O9CUKE9J";
//$api_secret = "af1875e7f56dc7ec7ff400913528c2e84fb8609cf5eda6ad93694be8335fcfb9a127bee9a5c2fb57b9ea5303edc103f00e98319ca4a72c135f1edd53bf7d750f";

$p = new \poloniex($api_key, $api_secret);

require("Macd4Class.php");
$m4 = new \Macd4Class();

$lvars = array(
    'pair' => array()
    , 'volume' => array()
    , 'shares' => 0
    , 'lmacd' => 0
    , 'lsignal' => 0
    , 'lhist' => 0
    , 'action' => ""
    , 'asks' => 0
    , 'bids' => 0
    , 'lastPrice' => 0
    , 'askcredit' => 0
    , 'bidcredit' => 1
    , 'lastUsedBidPrice' => 0
    , 'lastUsedAskPrice' => 0
    , 'proposedBidPrice' => 0
    , 'proposedAskPrice' => 0
    , 'lastbidbtc' => 0
    , 'lastDatax' => array()
    , 'lastData' => array()
    , 'lastDataBid' => array()
    , 'lastDataAsk' => array()
    , 'buyPoints' => array()
    , 'sellPoints' => array()
    , 'times' => array()
    , 'data' => array()
    , 'totavg' => 0
    , 'totcurrs' => 0
    , 'k' => 0
    , 'dir' => ""
    , 'trxnum' => ""
);

$cvars = array(
    'conn' => $m4->get_dbconn()
    , 'priceScaleFactor' => 10000000
    , 'volPctLimit' => 1 //  10%
    , 'makerFee' => 0.9985 // 0.15% fee of bid orders
    , 'takerFee' => 0.9985 // e use the make free for all  and not the actual 0.25 fee on ask
    , 'samples' =>  4320 // technically only need $fastPeriod numbner of records to make a decision, but we need more to see the hostory... 4320 = 3 days of data
    , 'ar' => array()
);
$totcashout = 0;
$ar = $m4->getARopts($cvars);

$cvars['logfile'] = $m4->getLogfile($cvars['ar']['pairq']);
$lvars['BTC'] = $cvars['ar']['BTCinv'];
$dataset = "hist";

if ((PHP_SAPI === 'cli') || empty($_SERVER['REMOTE_ADDR'])) {
    
} else {
    $cvars['ar']['fastPeriod'] = (isset($_GET['fastPeriod']) ? $_GET['fastPeriod'] : $cvars['ar']['fastPeriod']);
    $cvars['ar']['slowPeriod'] = (isset($_GET['slowPeriod']) ? $_GET['slowPeriod'] : $cvars['ar']['slowPeriod']);
    $cvars['ar']['signalPeriod'] = (isset($_GET['signalPeriod']) ? $_GET['signalPeriod'] : $cvars['ar']['signalPeriod']);
    $cvars['ar']['mode'] = (isset($_GET['mode']) ? $_GET['mode'] : $cvars['ar']['mode']);
    $cvars['ar']['BTCinv'] = (isset($_GET['BTCinv']) ? $_GET['BTCinv'] : $cvars['ar']['BTCinv']);
    $cvars['ar']['xsteps'] = (isset($_GET['xsteps']) ? $_GET['xsteps'] : $cvars['ar']['xsteps']);
    $cvars['ar']['minpctup'] = (isset($_GET['minpctup']) ? $_GET['minpctup'] / 100 : $cvars['ar']['minpctup']);
    $cvars['ar']['maxpctdn'] = (isset($_GET['maxpctdn']) ? $_GET['maxpctdn'] / 100 : $cvars['ar']['maxpctdn']);
    $cvars['ar']['dataset'] = (isset($_GET['data'][0]) ? $_GET['data'] : $cvars['ar']['dataset']);
    $cvars['ar']['method'] = (isset($_GET['method']) ? $_GET['method'] : $cvars['ar']['method']);
    $cvars['ar']['debug'] = (isset($_GET['debug'][0]) && ($_GET['debug'][0] == 1) ? 1 : 0);
    $cvars['ar']['pair'] = (isset($_GET['pair']) ? $_GET['pair'] : "BCT_XRP");

    // these are only web options
    $cvars['ar']['expand'] = (isset($_GET['expand']) ? $_GET['expand'] : 0);
    $dataset = $cvars['ar']['dataset'];
    $pairList = $m4->getMenuHtml(1000, $cvars, $_GET['pair']);
    $fList = $m4->getFlistHtml($cvars);
    $cvars['ar']['pairq'] = (isset($_GET['pair']) ? $_GET['pair'] : $cvars['ar']['pairq']);
    if ($_GET['func'] != "") {
        $_GET['pair'] = $_GET['func'];
        $cvars['ar']['pairq'] = $_GET['func'];
    }
    ?>    
    <head>
    <html>
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script src="js/macd4.js"></script>
        <link rel="stylesheet" type="text/css" href="css/macd4.css">
    </head>
    <body style="background-color:cornsilk">
        <ul>
            <li> Backtests against 770622 records polled every minute from Friday Sept 9 at 7:03 PM to Sunday Sept 5 11 at 5:13 PM
            <li> Shares purchased are limited to the volume at the time of the purchase
            <li> Transactions are adjusted with the 0.25% Taker Fee (even though Mker Fee is only 0.15)
        </ul>        
        <div id="show"></div>
        <table border="0px">
            <tr>
                <td><span class="b0" id="cats"> </span> </td>
                <td><span class="b0" id="cats">XRP</span> </td>
                <td><span class="b0" id="cats">Test 2</span> </td>
                <td><span class="b0" id="cats">Test 3</span></td>
            </tr>
            <tr>
                <td><span class="b0" id="cats"></span> </td>
                <td><span class="b1" id="set11">long EMA</span> </td>
                <td><span class="b2" id="set12">Hid triggers</span> </td>
                <td><span class="b3" id="set13">Temp</span></td>
            </tr>
            <tr>
                <td><span class="b0" id="cats"></span> </td>
                <td><span class="b1" id="set21">short EMA</span> </td>
                <td><span class="b2" id="set22">Obv triggers</span> </td>
                <td><span class="b3" id="set23">ADP</span></td>
            </tr>
            <tr>
                <td><!-- span class="b0" id="cats"></span--> </td>
                <td><!-- span class="b1" id="set31"></span--> </td>
                <td><!-- span class="b2" id="set32">Set 2</span--> </td>
                <td><!-- span class="b3" id="set33">Set 3</span--></td>
            </tr>
        </table>

        <form name="form" action="" method="get">
            <table>
                <tr><td>pair:</td><td><?php print $pairList; ?>   (order desc by avg base volume over sampel period) </td></tr>
                <tr><td>OR</td><td> (any select below overrides pair name)</td></tr>
                <tr><td>function:</td><td><?php print $fList; ?>   (builtin functions !! Poloniex ONLY!)) </td></tr>
                </tr>
            </table>
            <table>
                <tr><td>fastPeriod:</td><td>    <input  id="fastPeriod"  type="text" name="fastPeriod"   value="<?php echo $cvars['ar']['fastPeriod']; ?>"   >   EMA of previous X data points (fast period EMA) </td></tr>
                <tr><td>slowPeriod:</td><td>    <input  id="slowPeriod"  type="text" name="slowPeriod"   value="<?php echo $cvars['ar']['slowPeriod']; ?>"   >   EMA of previous X data points (slow period EMA)  </td></tr>
                <tr><td>signalPeriod:</td><td>  <input  id="signalPeriod"  type="text" name="signalPeriod" value="<?php echo $cvars['ar']['signalPeriod']; ?>" >   EMA of the X previous fast/slow deltas (signal period EMA)  </td></tr>
                <!--tr><td>pair:</td><td><input           type="text" name="pair"         value="<?php echo $cvars['ar']['pairq']; ?>"         >  ex: BTC_AMP  </td></tr-->

                <tr><td>BTC:</td><td>           <input  id="BTC"  type="text" name="BTC"          value="<?php echo $cvars['ar']['BTCinv']; ?>"       >  (initial amount of BTC investing)  </td></tr>
                <tr><td>steps:</td><td>         <input  id="xsteps"  type="text" name="xsteps"       value="<?php echo $cvars['ar']['xsteps']; ?>"       >  (use data points every [steps] minutes)  </td></tr>
                <tr><td>min % up:</td><td>      <input  id="minpctup"  type="text" name="minpctup"     value="<?php echo $cvars['ar']['minpctup'] * 100; ?>"       >  (min % up before selling.  0.25 to cover takeFee)  </td></tr>
                <tr><td>max % dn:</td><td>      <input  id="maxpctdn"  type="text" name="maxpctdn"     value="<?php echo $cvars['ar']['maxpctdn'] * 100; ?>"       >  (max % down before dumping)  </td></tr>
                <tr><td>method:</td><td>        <input  id="method"  type="text" name="method"       value="<?php echo $cvars['ar']['method']; ?>"       >  (4 = eval ever n-points, 3 = only look for crossovers)  </td></tr>
                <tr><td>debug:</td><td>         <input  id="debug"  type="checkbox" name="debug[]"  value="1" <?php print ($cvars['ar']['debug'] != 0 ? 'checked' : ''); ?>     /> (show individual trnsactions)<br /></td></tr>
                <tr><td>dataset:</td><td style='border:1px solid grey'>
                        <input   class="datasel"        type="radio" id="ds1" name="data" value="hist" <?php print ($cvars['ar']['dataset'] == 'hist' ? 'checked' : ''); ?>     /> Old Poloniex data<br />
                        <input   class="datasel"        type="radio"  id="ds2" name="data" value="histremote" <?php print ($cvars['ar']['dataset'] == 'histremote' ? 'checked' : ''); ?>     /> Latest Poloniex data<br />
                        <input   class="datasel"        type="radio"  id="ds3" name="data" value="histrandom" <?php print ($cvars['ar']['dataset'] == 'histrandom' ? 'checked' : ''); ?>     /> Random data<br />
                        <input   class="datasel"        type="radio"  id="ds4" name="data" value="testdata1" <?php print ($cvars['ar']['dataset'] == 'testdata1' ? 'checked' : ''); ?>     /> test data (set 1: FNGN, ADP)<br />
                        <input   class="datasel"        type="radio"  id="ds5" name="data" value="testdata2" <?php print ($cvars['ar']['dataset'] == 'testdata2' ? 'checked' : ''); ?>     /> test data (set 2: Sin/Cos/tan curves, brownian, fractal)<br />
                        <input   class="datasel"        type="radio"  id="ds6" name="data" value="testdata3" <?php print ($cvars['ar']['dataset'] == 'testdata3' ? 'checked' : ''); ?>     /> test data (set 3: temp from 1890)<br />
                    </td></tr>
                <tr><td>expand data view:</td><td><input    id="expand"         type="text" name="expand" value="<?php echo $cvars['ar']['expand']; ?>"     /> (dataset in same size window)<br /></td></tr>
            </table>
            <input type="submit" name="submit">
        </form><br>
    </body>
    </html>

    <?php
}

$up = $m4->getQstr($cvars['ar']['pairq'], $cvars);
$allpairs = $m4->doQuery(($up ? $up : "select distinct name from ${dataset} where name like '" . $cvars['ar']['pairq'] . "'"), $cvars);

//******************************************************************************
// ths is teh main loop that does everythihg
//******************************************************************************

//run through all pairs
foreach ($allpairs as $lvars['pair']) {
    $lvars = $m4->clearVars($lvars, $cvars);

    // FIXME - this seem to be gettign called every 5 seconds.. sometimes
    
    // only need the last days activity
    $tdata = $m4->getSampleSize($lvars, $cvars); //select all data from db and store in array.. everthign is derived from this
    
    
    
    if ($cvars['ar']['expand'] != 0) {
        $tdata = array_slice($tdata, 0, $cvars['ar']['expand']);
    }
    $cvars['ar']['scale'] = 100; //defaultyfor cryupto
    if ($cvars['ar']['dataset'] == "testdata1") { // we are using 'real' data, so don't scale it
        $cvars['ar']['scale'] = 1;
    }

    $lvars['lastDatax'] = $m4->skewData($lvars, $cvars, "last", "lowestAsk", "highestBid", $tdata, $scale = true);

    $macdary = trader_macd($lvars['lastDatax'], $cvars['ar']['fastPeriod'], $cvars['ar']['slowPeriod'], $cvars['ar']['signalPeriod']); //generate MACD and histograms
    $arraydiff = count($tdata) - count($macdary[0]); // get fiss for realign
    // get the actual 
    $lvars['volume'] = $m4->get_data($lvars, $cvars, "baseVolume", $tdata);    // get volume recs
    $lvars['lastDataBid'] = $m4->get_data($lvars, $cvars, "highestBid", $tdata);
    $lvars['lastDataAsk'] = $m4->get_data($lvars, $cvars, "lowestAsk", $tdata);
    $lvars['lastData'] = $m4->get_data($lvars, $cvars, "last", $tdata);
    $lvars['times'] = $m4->get_data($lvars, $cvars, "time", $tdata);

    $lvars['macd'] = array_values($macdary[0]);

    $lvars['lastDatax'] = $m4->chopary($lvars['lastDatax'], $arraydiff);
    $lvars['lastDataBid'] = $m4->chopary($lvars['lastDataBid'], $arraydiff);
    $lvars['lastDataAsk'] = $m4->chopary($lvars['lastDataAsk'], $arraydiff);
    $lvars['lastData'] = $m4->chopary($lvars['lastData'], $arraydiff);
    $lvars['times'] = $m4->chopary($lvars['times'], $arraydiff);
    $lvars['volume'] = $m4->chopary($lvars['volume'], $arraydiff);

    $lvars['data'] = $m4->chopary($tdata, $arraydiff);

    $m4->logIt("RUNNING -> ./macd4.php -f" . $cvars['ar']['fastPeriod'] . " -s" . $cvars['ar']['slowPeriod'] . " -S" . $cvars['ar']['signalPeriod'] . " -p" . ($lvars['pair']['name']) . " -m" . $cvars['ar']['mode'] . " -c" . $cvars['ar']['BTCinv'] . " -x" . $cvars['ar']['xsteps'] . " -U" . $cvars['ar']['minpctup'] * 100 . " -D" . $cvars['ar']['maxpctdn'] * 100 . " -z" . $cvars['ar']['dataset'] . "\n", $cvars);
    $btc_value = $m4->getCurrentBTCval();
    for ($lvars['k'] = 1; $lvars['k'] < count($lvars['macd']); $lvars['k'] ++) {
        $lvars['buyPoints'][$lvars['k']] = 0;
        $lvars['sellPoints'][$lvars['k']] = 0; //$lvars['macd'][$lvars['k']];;//0;
        $lvars['buyPointsVal'][$lvars['k']] = 0;
        $lvars['sellPointsVal'][$lvars['k']] = 0; //$lvars['macd'][$lvars['k']];;//0;

        switch ($cvars['ar']['method']) {
            case 1:   // ????
                $lvars['action'] = $m4->setAction_v1($lvars, $cvars);
                $m4->processByLessSimpleHist_v1($lvars, $cvars);
                break;
            case 2:  //this process analyses tickes on a minute by mninute bases
                $lvars['action'] = $m4->setAction_v2($lvars, $cvars);
                $m4->processByLessSimpleHist_v2($lvars, $cvars);
                break;
            case 3:  //his process only looks at MACD crossopvers
                $lvars['action'] = $m4->setAction_v3($lvars, $cvars);
                $m4->processByLessSimpleHist_v3($lvars, $cvars);
                break;
            case 4:   // 
                $lvars['action'] = $m4->setAction_v4($lvars, $cvars);
                $m4->processByLessSimpleHist_v4($lvars, $cvars);
                break;
//                case 5:
//                    $lvars['action'] = $m4->setAction_v5($lvars, $cvars);
//                    $m4->processByLessSimpleHist_v5($lvars, $cvars);
//                    break;
//                case 6:
//                    $lvars['action'] = $m4->setAction_v6($lvars, $cvars);
//                    $m4->processByLessSimpleHist_v6($lvars, $cvars);
//                    break;
            default:
//                  code to be executed if n is different from all labels;
        }
    }
    // cash out remaining BTC
    $m4->logIt("CASHING OUT AT LAST BUY PRICE: ", $cvars);

    $shval = $lvars['shares'] * $lvars['lastUsedBidPrice'];
    $cashout = $lvars['BTC'] + $shval;
    $lvars['sharesHolding'] = $lvars['shares'];

    $annualUnits = $m4->getDaysDiff($lvars['times'][0], $lvars['times'][count($lvars['times']) - 1]);
    $annualPct = ($cashout - $cvars['ar']['BTCinv']) * 100 * $annualUnits;

    $rs = sprintf("> %12s %32.16f %5s bids %5s asks %5.2f%% (annual) \n", $lvars['pair']['name'], $cashout, $lvars['bids'], $lvars['asks'], sprintf("%8.2f", $annualPct));
    print($rs);
    if (PHP_SAPI != 'cli') {
        print "<h2>";
        print "<div style='background-color:grey;'>\n";
        $m4->makeGraphs1($lvars, $cvars, $macdary);
        $m4->makeGraphs2($lvars, $cvars, $macdary);
        print "</h2>";
        print "</div>\n";
    }
    $totcashout += $cashout;
    $totc = count($allpairs);
    $lvars['BTC'] = $cvars['ar']['BTCinv'];
}
if ((isset($totc) && ($totc > 1))) {

    $annualInt = (($totcashout / $totc ) - 1) * 100 * 180; //(365/2)
    $str = "=================================================================================\nAVG: $totcashout / $totc  (" . ($totcashout / $totc) . ")  Annual interest: " . $annualInt . " %\n=================================================================================\n";
    print($str);
    $m4->logIt($str, $cvars);
}
  
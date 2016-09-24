<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Macd4Class
 *
 * @author jw
 */
class Macd4ClassTest {

    function getMenuHtml($list, $cvars, $selected) {


        $str = "select name ,avg(baseVolume)as av from " . $cvars['ar']['dataset'] . " where name like '%' group by name order by name asc";

//        $str = "select name ,avg(baseVolume)as av from hist group by name order by av desc";
        $conn = $cvars['conn'];
        $st = $conn->prepare($str);
        $st->execute();
        $orderedPairs = $st->fetchAll();
        $on = "";
        $str = "<select name='pair'>\n";
        foreach ($orderedPairs as $op) {
            if ($selected == $op['name']) {
                $on = "selected";
            } else {
                $on = "";
            }
            $str .= "<option  ${on} id='" . $op['name'] . "' value='" . $op['name'] . "'>${op['name']} (" . $this->nf($op['av'], 0) . ")</option>\n";
        }
        $str .= "</select>\n";
        return($str);
    }

    public function getScaleFactor($key, $data) {
        $retval = array();

        if ($key == "last") {
            foreach ($data as $bar) {
                $retval[] = $priceScaleFactor * $bar[$key];
            }
        } else {
            foreach ($data as $bar) {
                $retval[] = $bar[$key];
            }
        }

        $xfactor = 1 / min($retval);

        foreach ($retval as $r) {
            $r = r * $xfactor;
        }



        return $retval;
    }

    function getFlistHtml($cvars) {
        $selected = $_GET['func'];

        $fs = array(
            "" => "unselected",
            "f_delta" => "TOP 10 BASED ON LARGEST BID/ASK DIFFERENCE",
            "f_topbv" => "TOP 10 BASED ON LARGEST BASE VOLUME",
            "f_top2bv" => "TOP 10 BASED ON 2nd LARGEST BASE VOLUME",
            "f_top3qv" => "TOP 10 BASED ON 3nd LARGEST BASE VOLUME",
            "f_tophilo" => "TOP 10 BASED ON LARGEST HI/LO DELTA",
            "f_tophiloXpcXbv" => "TOP 10 BASED ON LARGEST HI/LO DELTA, BASE VOLUME AND PERCENT INCREASE",
            "f_bothilo" => "TOP 10 BASED ON SMALLEST HI/LO DELTA, BASE VOLUME AND PERCENT INCREASE"
        );
        $str = "<select name='func'>\n";

        foreach ($fs as $n => $desc) {
            if ($selected == $n) {
                $on = "selected";
            } else {
                $on = "";
            }
            $str .= "<option ${on} id='$n' value='" . $n . "'>${n} (${desc})</option>\n";
        }
        $str .= "</select>\n";
        return($str);
    }

    function getDaysDiff($from, $to) {
//        $now = time(); // or your date as well
        $_from = strtotime($from);
        $_to = strtotime($to);

        $datediff = $_to - $_from;

        $hours = floor($datediff / (60 * 60 ));

        $units = 8760 / $hours; // hours in a year 

        return($units);
    }

    function getSampleSize(&$lvars, &$cvars) {
        $alldata = $this->get_trxdata($lvars, $cvars);
        $size = $cvars['ar']['xsteps'];
        if ($size == 1) {
            return($alldata);
        }
        $newdata = array();

        // need to count back from the last to makde sure the last step size land on the stap entry

        for ($i = 0; $i <= count($alldata); $i++) {
            if (isset($alldata[$i])) {
                array_push($newdata, $alldata[$i]);
            }
            $i += $size;
        }
        return($newdata);
    }

    // **************************************************************************
    public function _getPercentDelta(&$lvars, &$cvars) {
        /*

          ex:   cv = 0.002964   pv = 0.000312   ...


          $cv = $lvars['lastData'][$lvars['k']];
          $pv = $lvars['lastData'][$lvars['k'] - 1];
         */

        return($this->percent($cv, $pv));
    }

    // **************************************************************************
    public function _getTime(&$lvars) {
        return(date("m-d-Y H:i:s", strtotime($lvars['times'][$lvars['k']])));
    }

    // **************************************************************************
    public function _newShareVal_LIVE(&$lvars, &$cvars) {
        $pps = $lvars['lastData'][$lvars['k']];
        $shCanBuy = $lvars['volume'][$lvars['k']] * $cvars['volPctLimit'];
        $shCanAfford = $lvars['BTC'] / $pps;
        $amtToBuy = $shCanAfford;
        $pair = $lvars['pair'];
        $trxnum = 0;

        $order = array(
            'command' => 'sell',
            'currencyPair' => $pair,
            'rate' => $pps,
            'amount' => $amtToBuy,
            'fillOrKill' => 1
//                            'immediateOrCancel' =>  $args['immediateOrCancel'],
//                            'postOnly' => $args['postOnly']
        );
        $trxnum = $p->xsel($order);

        $balances = $p->get_complete_balances();
        $lvars['BTC'] = $balances['BTC']['available'];
        $lvars['shares'] = $balances[$pair]['available'];
        $lvars['trxnum'] = $trxnum;

        $r = $this->_getStatStr($lvars);
        $this->logIt($r, $cvars, 1);
        return($sary);
    }

    // **************************************************************************
    public function _newBTCval_LIVE(&$lvars, &$cvars) {

        $pps = $lvars['lastData'][$lvars['k']];
        $shares = $lvars['shares'];
        $amtToRecover = ($shares * $pps);
        $pair = $lvars['pair'];
        $trxnum = 0;


        $order = array(
            'command' => 'sell',
            'currencyPair' => $pair,
            'rate' => $pps,
            'amount' => $shares,
            'fillOrKill' => 1
//                            'immediateOrCancel' =>  $args['immediateOrCancel'],
//                            'postOnly' => $args['postOnly']
        );
        $trxnum = $p->xsel($order);

        $balances = $p->get_complete_balances();
        $lvars['BTC'] = $balances['BTC']['available'];
        $lvars['shares'] = $balances[$pair]['available'];
        $lvars['trxnum'] = $trxnum;

        $r = $this->_getStatStr($lvars);
        $this->logIt($r, $cvars, 1);

        return($sary);
    }

    // **************************************************************************
    public function _getStatStr(&$lvars) {
        //$r = "[" . ($this->nf($lvars['volume'][$lvars['k']], 0)) . "]  + " . $lvars['action'] . " " . $this->nf($lvars['shares'], 0) . " shares for " . $this->nf($lvars['BTC'], 6) . " BTC   (pps: " . $this->nf(($lvars['lastUsedBidPrice']), 8) . ") - ${lvars['timesig']}\n";

        $vol = $lvars['volume'][$lvars['k']];
        //var_dump($lvars['volume']);exit;
        $action = $lvars['action'];
        $shares = $lvars['shares'];
        $ppsbid = $lvars['lastUsedBidPrice'];
        $ppsask = $lvars['lastUsedAskPrice'];

        $btcbid = $shares * $ppsbid;
        $btcask = $shares * $ppsask;
        //$btc = $lvars['BTC'];
        $time = $lvars['timesig'];
        $bal = $lvars['BTC'];

        if ($action == "bid") {
            $r = $this->r(sprintf(" + %s  %5.0f  shares for %12.6f BTC   (pps: %12.8f) - %17s\n", $action, $shares, $btcbid, $ppsbid, $time));
        } else {
            $r = $this->g(sprintf(" - %s  %5.0f  shares for %12.6f BTC   (pps: %12.8f) - %17s\n", $action, $shares, $btcask, $ppsask, $time));
        }

        return($r);
    }

    // **************************************************************************
    // **************************************************************************
    // **************************************************************************
    // **************************************************************************
    public function _newShareVal(&$lvars, &$cvars) {

        //this is where we are live
        //if this is the LAST calc then is it the one current when in live mode, so we set teh status to live
        //print $lvars['k']." >= ".(count($lvars['macd']))."\n";
        if ($lvars['k'] + $cvars['ar']['frompoint'] >= (count($lvars['macd'])) - 1) {
            $cvars['action'] = "live";
        } else {
            $cvars['action'] = "test"; // anything otehr ten teh last is for testing only
        }

        //$pps = $lvars['lastData'][$lvars['k']];  // uses LAST value
        $pps = $lvars['lastDataBid'][$lvars['k']];   // uses LOWESTASK
        $shCanBuy = $lvars['volume'][$lvars['k']] * $cvars['volPctLimit'];
        $shCanAfford = $lvars['BTC'] / $pps;

        $amtToBuy = ($shCanAfford > $shCanBuy ? $shCanBuy : $shCanAfford);
        //$amtToBuy = $shCanAfford;

        $amtBTCcost = ($amtToBuy * $pps);


//        $this->recspec($lvars['pctok']);
        $this->recspec("BOUGHT");

        $btcChange = $lvars['BTC'] - $amtBTCcost;
        $newShAmt = $amtToBuy * $cvars['makerFee'];


        if ($cvars['action'] == "live") { // if live (i.e. last recod) do this 
//            if ($cvars['ar']['mode'] == "l") {
//                //print $this->g("BUY (b" . $lvars['bidcredit'] . "/a" . $lvars['askcredit'] . ")\n");
//                print $this->r(sprintf("[%8s] PLACED BOUGHT ORDER: [%4s] shares: %15s @ %15s =  %15s BTC (%20s)\n", $lvars['pair']['name'], $lvars['k'], $amtToBuy, $pps, $amtBTCcost, date('m/d/Y h:i:s a', time()))); // tyhis is where we make the call to poloniex IF we are live and if thsi is the last transaction
//                $lvars['bidcredit'] --; // update counters
//                $lvars['askcredit'] ++;
//                file_put_contents(".credits", json_encode(array('bidcredit' => $lvars['bidcredit'], 'askcredit' => $lvars['askcredit'])));
//
//
//                //JWFIX thse need to be overwritten by teh API calls
//                $btcChange = $lvars['BTC'] - $amtBTCcost;
//                $newShAmt = $amtToBuy * $cvars['makerFee'];
//                $sary = array('shares' => $newShAmt, 'BTC' => $btcChange);
//
//                file_put_contents(".btcshares", json_encode($sary));
//
//                $sary = array('shares' => $newShAmt, 'BTC' => $btcChange);
////var_export($sary);
//                return($sary);
//            }
        }
        //if ($cvars['ar']['mode'] == "t") { // otehrsise, if test do this
            $lvars['bidcredit'] --; // update counters
            $lvars['askcredit'] ++;
            $lvars['buyPoints'][$lvars['k']] = $lvars['macd'][$lvars['k']];
            $lvars['buyPointsVal'][$lvars['k']] = $lvars['lastData'][$lvars['k']];
            $sary = array('shares' => $newShAmt, 'BTC' => $btcChange);
            return($sary);
        //}
    }

    // **************************************************************************
    public function _newBTCval(&$lvars, &$cvars) {

        if ($lvars['k'] >= count($lvars['macd']) - 1) {
            $cvars['action'] = "live";
        } else {
            $cvars['action'] = "test";
        }

        $pps = $lvars['lastDataAsk'][$lvars['k']]; // uses HIGHESTBID
        $shares = $lvars['shares'];

        $this->recspec("SOLD");

        $amtToRecover = ($shares * $pps);
        $btcBal = $lvars['BTC'] + ($amtToRecover * $cvars['takerFee']);
        $shareBal = 0;

        if ($cvars['action'] == "live") {
//            if ($cvars['ar']['mode'] == "l") {
//                print $this->g(sprintf("[%8s] PLACED SOLD   ORDER: [%4s] shares: %15s @ %15s =  %15s BTC (%20s)\n", $lvars['pair']['name'], $lvars['k'], $shares, $pps, $amtToRecover, date('m/d/Y h:i:s a', time()))); // tyhis is where we make the call to poloniex IF we are live and if thsi is the last transaction
//                $lvars['askcredit'] --;
//                $lvars['bidcredit'] ++;
//                file_put_contents(".credits", json_encode(array('bidcredit' => $lvars['bidcredit'], 'askcredit' => $lvars['askcredit'])));
//                // JWFX need to be overridde,
//                //echo ("320-btcBAL:" . $lvars['BTC'] . " + $amtToRecover * " . $cvars['takerFee'] . "\n");
//
//                $sary = array('shares' => $shareBal, 'BTC' => $btcBal);
//                file_put_contents(".btcshares", json_encode($sary));
////var_export($sary);
//
//                return($sary);
//            }
        }

        //if ($cvars['ar']['mode'] == "t") {

            $lvars['askcredit'] --;
            $lvars['bidcredit'] ++;
            $lvars['sellPoints'][$lvars['k']] = $lvars['macd'][$lvars['k']];
            $lvars['sellPointsVal'][$lvars['k']] = $lvars['lastData'][$lvars['k']];

            $sary = array('shares' => $shareBal, 'BTC' => $btcBal);
            return($sary);
        //}
    }

    // **************************************************************************
    // **************************************************************************
    // **************************************************************************
    // **************************************************************************
    public function clearVars(&$lvars, &$cvars) {

        $lvars['shares'] = 0;
        $lvars['lmacd'] = 0;
        $lvars['lsignal'] = 0;
        $lvars['lhist'] = 0;
        $lvars['action'] = "";
        //$lvars['pair'] = "";
        $lvars['asks'] = 0;
        $lvars['bids'] = 0;
        $lvars['lastPrice'] = 0;
        $lvars['askcredit'] = 0;
        $lvars['bidcredit'] = 1;
        $lvars['BTC'] = $cvars['ar']['BTCinv'];
        $lvars['lastUsedBidPrice'] = 0;
        $lvars['lastUsedAskPrice'] = 0;
        $lvars['lastbidbtc'] = 0;
        $lvars['lastDatax'] = array();
        $lvars['lastData'] = array();
        $lvars['lastDataAsk'] = array();
        $lvars['lastDataBid'] = array();
        $lvars['times'] = array();
        $lvars['data'] = array();
        $lvars['totavg'] = 0;
        $lvars['totcurrs'] = 0;
        $lvars['k'] = 0;
        $lvars['dir'] = "";



        // store
        if (file_exists("macd4.ini")) {
            $iniary = parse_ini_file("macd4.ini");
                        
            
            $cvars['ar']['upticks'] = $iniary['upticks'];
            $cvars['ar']['dnticks'] = $iniary['dnticks'];
            
            $cvars['ar']['fastPeriod'] = $iniary['fastPeriod'];
            $cvars['ar']['slowPeriod'] = $iniary['slowPeriod'];
            $cvars['ar']['signalPeriod'] = $iniary['signalPeriod'];
            
            
            $cvars['ar']['minpctup'] = $iniary['minpctup'];
            $cvars['ar']['maxpctdn'] = $iniary['minpctdn'];
        }
        // if live mode we need to remember the last state

//        if ($cvars['ar']['mode'] == "l") {
//            $loadCredits = json_decode(file_get_contents(".credits"), TRUE);
//            $lvars['askcredit'] = $loadCredits['askcredit'];
//            $lvars['bidcredit'] = $loadCredits['bidcredit'];
//
//            $loadBtcShares = json_decode(file_get_contents(".btcshares"), TRUE);
//            $lvars['BTC'] = $loadBtcShares['BTC'];
//            $lvars['shares'] = $loadBtcShares['shares'];
//        }


        return($lvars);
    }

    // **************************************************************************
    public function processByLessSimpleHist_v1(&$lvars, &$cvars) {
        // original one that works
        if ($lvars['action'] == "bid") {
            // there must be a 'credit' to bid as there can only be N asks for N bids... for now.  starts with a bid do bidcredit is initialized as 1
            if ($lvars['bidcredit'] > 0) {
                // only bid if the decrease in price has dropped a certain percentage.. but cover the commissions as well
                //$_percent = $this->_getPercentDelta($lvars, $cvars);
                $lvars['timesig'] = $this->_getTime($lvars);
                //$lvars['lastUsedBidPrice'] = $lvars['lastData'][$lvars['k']]; uses LAST
                $lvars['lastUsedBidPrice'] = $lvars['lastDataBid'][$lvars['k']]; // uses HIGHESTBID
                // get curent BTC value to calc $$ amount
                //echo "401-shares before:" . $lvars['shares'] . "\n";
                $lvars['shares'] = $this->_newShareVal($lvars, $cvars);
                //echo "403-shares sfter:" . $lvars['shares'] . "\n";
                $r = $this->_getStatStr($lvars);
                $this->logIt($r, $cvars);
                //update state
                $lvars['lastbidbtc'] = $lvars['BTC']; // record for comparison
                $lvars['BTC'] = 0;
                $lvars['bidcredit'] --; // update counters
                $lvars['askcredit'] ++;
                $lvars['bids'] ++;
                $lvars['action'] = ".";
            }
        }
        if ($lvars['action'] == "ask") {
            if ($lvars['askcredit'] > 0) {
                $lvars['timesig'] = $this->_getTime($lvars);
                //$lvars['lastUsedAskPrice'] = $lvars['lastData'][$lvars['k']]; //uses LAST
                $lvars['lastUsedAskPrice'] = $lvars['lastDataAsk'][$lvars['k']]; // uses LOWESTASK
                if ($lvars['lastUsedAskPrice'] > $lvars['lastUsedBidPrice'] * $cvars['takerFee']) {
                    $lvars['BTC'] = $this->_newBtcVal($lvars, $cvars);
                    $r = $this->_getStatStr($lvars);
                    $this->logIt($r, $cvars);
                    $lvars['askcredit'] --;
                    $lvars['bidcredit'] ++;
                    $lvars['BTC'] = $lvars['BTC'] * $cvars['takerFee']; // comission
                    $lvars['shares'] = 0;
                    $lvars['asks'] ++;
                    $lvars['action'] = ".";
                }
            }
        }
    }

    // **************************************************************************

    public function setAction_v1(&$lvars) {
        if (($lvars['macd'][$lvars['k']] < 0) && ($lvars['macd'][$lvars['k'] - 1] > 0)) {
            return("bid");
        } elseif ((($lvars['macd'][$lvars['k']] > 0) && ($lvars['macd'][$lvars['k'] - 1] < 0))) {
            return("ask");
        }
        return("-");
    }

    // **************************************************************************
    // this process analyzuz tikes on a mnute by mninute bases
    // **************************************************************************

    public function processByLessSimpleHist_v2(&$lvars, &$cvars) {
        // original one that works
        if ($lvars['action'] == "bid") {
            // there must be a 'credit' to bid as there can only be N asks for N bids... for now.  starts with a bid do bidcredit is initialized as 1
            if ($lvars['bidcredit'] > 0) {
                // only bid of teh decrease in price has dropped a certain percentage.. but cover the commissions as well
                //$_percent = $this->_getPercentDelta($lvars, $cvars);
                $lvars['timesig'] = $this->_getTime($lvars);
                //$lvars['lastUsedBidPrice'] = $lvars['lastData'][$lvars['k']]; uses LAST
                $lvars['lastUsedBidPrice'] = $lvars['lastDataBid'][$lvars['k']]; // uses HIGHESTBID
                // get curent BTC value to calc $$ amount

                $bidtx = $this->_newShareVal($lvars, $cvars);
                $lvars['shares'] = $bidtx['shares'];
                $lvars['BTC'] = $bidtx['BTCchange'];

//                print $this->b("you now have ${lvars['shares']} shares and ${lvars['BTC']} remaining\n");

                $r = $this->_getStatStr($lvars);

                $this->logIt($r, $cvars);
                //update state
                $lvars['lastbidbtc'] = $lvars['BTC']; // record for comparison
                $lvars['bidcredit'] --; // update counters
                $lvars['askcredit'] ++;
                $lvars['bids'] ++;
                $lvars['action'] = ".";
            }
        }
        if ($lvars['action'] == "ask") {
            if ($lvars['askcredit'] > 0) {
                $lvars['timesig'] = $this->_getTime($lvars);
                $lvars['proposedAskPrice'] = $lvars['lastData'][$lvars['k']];

                if ($lvars['proposedAskPrice'] * $cvars['takerFee'] > $lvars['lastUsedBidPrice']) {
                    //$lvars['lastUsedAskPrice'] = $lvars['lastData'][$lvars['k']]; //uses LAST
                    $lvars['lastUsedAskPrice'] = $lvars['lastDataAsk'][$lvars['k']]; // uses LOWESTASK
                    //$lvars['BTC'] = $this->_newBtcVal($lvars, $cvars);
                    $asktx = $this->_newBtcVal($lvars, $cvars);
                    $lvars['BTC'] = $asktx['BTCbal'];

                    $r = $this->_getStatStr($lvars);
                    $lvars['shares'] = $asktx['shares'];

//                print $this->b("you now have ${lvars['shares']} shares and ${lvars['BTC']} remaining\n");


                    $this->logIt($r, $cvars);
                    $lvars['askcredit'] --;
                    $lvars['bidcredit'] ++;
//                    $lvars['BTC'] = $lvars['BTC'] * $cvars['takerFee']; // comission
                    $lvars['shares'] = 0;
                    $lvars['asks'] ++;
                    $lvars['action'] = ".";
                }
            }
        }
    }

    // **************************************************************************
    public function setAction_v2(&$lvars) {
        $currentMacd = $lvars['macd'][$lvars['k']];
        $previousMacd = $lvars['macd'][$lvars['k'] - 1];

        // if macd is + and previous is - then the lines have crossed
        if (($currentMacd < 0) && ($previousMacd > 0)) {
            return("bid");
        } elseif ((($currentMacd > 0) && ($previousMacd < 0))) {
            return("ask");
        }
        return("-");
    }

    //*************************************************************************
    public function sumbmitBidRequest_LIVE(&$lvars, &$cvars) {
        $bidtx = $this->_newShareVal_LIVE($lvars, $cvars);
        if ($bidtx) {
            $lvars['shares'] = $bidtx['shares'];
            $lvars['BTC'] = $bidtx['BTC'];

//            echo "531-BID REQ:";
//            var_export($bidtx);

            $cbaly = $p->get_complete_balances();
            $pair = "BTC";
            print_r($cbaly[$pair]['available']);
        }
        return true;
    }

    //*************************************************************************
    public function sumbmitBidRequest(&$lvars, &$cvars) {
    $bidtx = $this->_newShareVal($lvars, $cvars);
        if ($bidtx) {
            $lvars['shares'] = $bidtx['shares'];
            $lvars['BTC'] = $bidtx['BTC'];
//            echo "547-BID REQ:";
//            var_export($bidtx);
            return true;
        }
        return(false);
    }

    //*************************************************************************
    public function sumbmitAskRequest(&$lvars, &$cvars) {
        $asktx = $this->_newBtcVal($lvars, $cvars);
        if ($asktx) {
//            echo "\nASK REQ:";
//            var_export($asktx);
            $lvars['BTC'] = $asktx['BTC'];
            $lvars['shares'] = $asktx['shares'];
            return true;
        }
        return(false);
    }

    // **************************************************************************
    // this process has call stubs to live server
    // **************************************************************************

    public function processByLessSimpleHist_v4(&$lvars, &$cvars) {
        // original one that works

        $proposedAskPrice = $lvars['lastDataAsk'][$lvars['k']];
        $sellAt = $proposedAskPrice;
        $boughtAt = $lvars['lastDataBid'][$lvars['k']];
        $thispct = $this->ratioIncrease($boughtAt, $sellAt);
        $lvars['pctok'] = (( ($thispct - $cvars['ar']['minpctup']) > 0) ? "passed" : "failed");

//echo $lvars['action']."\n";
//echo $lvars['bidcredit'];
//echo $lvars['askcredit'];
//echo "\n\n";

        if ($lvars['action'] == "bid") {
            $lvars['pctok'] = "N/A";
            // there must be a 'credit' to bid as there can only be N asks for N bids... for now.  starts with a bid do bidcredit is initialized as 1
            if ($lvars['bidcredit'] > 0) {
                // only bid of teh decrease in price has dropped a certain percentage.. but cover the commissions as well
                $lvars['timesig'] = $this->_getTime($lvars);
                $lvars['lastUsedBidPrice'] = $lvars['lastDataBid'][$lvars['k']]; // uses HIGHESTBID
                file_put_contents(".lastUsedBidPrice", json_encode(array('lastUsedBidPrice' => $lvars['lastUsedBidPrice'])));
                // get curent BTC value to calc $$ amount
                $rs = $this->sumbmitBidRequest($lvars, $cvars);
                if (!$rs) {
//                    print("ERROR IN BID SUBMISSION");
//                    exit;
                }
                //FIXME here is where we check for bottm level?
                $r = $this->_getStatStr($lvars);

                $this->logIt($r, $cvars);
                //update state
                $lvars['lastbidbtc'] = $lvars['BTC']; // record for comparison
                $lvars['bids'] ++;
                $lvars['action'] = ".";
            }
        }
        if ($lvars['action'] == "ask") {
            if ($lvars['askcredit'] > 0) {
                $lvars['timesig'] = $this->_getTime($lvars);
                $lvars['proposedAskPrice'] = $lvars['lastDataAsk'][$lvars['k']];
                $sellAt = $lvars['proposedAskPrice'];
                if (!$lvars['lastUsedBidPrice']) {
                    $load = json_decode(file_get_contents(".lastUsedBidPrice"), TRUE);
                    $lvars['lastUsedBidPrice'] = $load['lastUsedBidPrice'];
                }
                $boughtAt = $lvars['lastUsedBidPrice'];
                $thispct = $this->ratioIncrease($boughtAt, $sellAt);

                $this->recspec((( ($thispct - $cvars['ar']['minpctup']) > 0) ? "passed" : "failed"));
//                $this->recspec($lovars['pctok'] );

//echo "[$thispct  > ".$cvars['ar']['minpctup']."]<<<<<<<<<<<<<<<<<";                
                if ($thispct > $cvars['ar']['minpctup']) {   // JWFIX  for some ewason, even though thi was true, it sold below the buy 
//                    if ($boughtAt < $lvars['lastDataAsk'][$lvars['k']]) {  //... so make sure not selling below last bought... use LAST not LOWESTASK
                        $lvars['lastUsedAskPrice'] = $lvars['lastDataAsk'][$lvars['k']]; // uses LOWESTASK
                        $r = $this->_getStatStr($lvars);
                        $rs = $this->sumbmitAskRequest($lvars, $cvars);
                        if (!$rs) {
//                        print("ERROR IN ASK SUBMISSION");
//                        exit;
                        }
                        $this->logIt($r, $cvars);
//                    $lvars['shares'] = 0;
                        $lvars['asks'] ++;
                        $lvars['action'] = ".";
//                    }
                }
            }
        }
    }

    // **************************************************************************

    public function setAction_v3(&$lvars) {
        $currentMacd = $lvars['macd'][$lvars['k']];
        $previousMacd = $lvars['macd'][$lvars['k'] - 1];

        if (($currentMacd > 0) && ($previousMacd < 0)) {
            return("bid");
        } elseif (($currentMacd < 0) && ($previousMacd > 0)) {
            return("ask");
        }
        return("-");
    }

    // **************************************************************************
    public function setAction_v4(&$lvars) {
        $currentMacd = $lvars['macd'][$lvars['k']];
        $previousMacd = $lvars['macd'][$lvars['k'] - 1];

        // if macd is + and previous is - then the lines have crossed
        if (($currentMacd < 0) && ($previousMacd > 0)) {
            return("bid");
        } elseif ((($currentMacd > 0) && ($previousMacd < 0))) {
            return("ask");
        }
        return("-");
    }

    public function recspec($spec = null) {

        $s = sprintf("%20s", $spec);
        file_put_contents("specs.log", "${s}", FILE_APPEND);
    }

    // **************************************************************************
    public function setAction_v4a(&$lvars, &$cvars) {
        $currentMacd = $lvars['macd'][$lvars['k']];
        $previousMacd = $lvars['macd'][$lvars['k'] - 1];
        $stat = "-";
        // if macd is + and previous is - then the lines have crossed
        if (($currentMacd < 0) && ($previousMacd > 0)) {
            $lvars['direction'] = "sinking";
            $lvars['dnticks'] ++;
            if ($lvars['dnticks'] >= $cvars['ar']['dnticks']) {
                $lvars['dnticks'] = 0; // reset
                $stat = "bid";
            }
        } elseif ((($currentMacd > 0) && ($previousMacd < 0))) {
            $lvars['direction'] = "rising";
            $lvars['upticks'] ++;
            if ($lvars['upticks'] >= $cvars['ar']['upticks']) {
                $lvars['upticks'] = 0; // reset
                $stat = "ask";
            }
        } else {
            $lvars['direction'] = "-";
        }
        $this->recspec($lvars['direction']);
        $this->recspec($lvars['dnticks']);
        $this->recspec($lvars['upticks']);

        return($stat);
    }

    // **************************************************************************
    // this process only looks at MACD crossopvers
    // **************************************************************************

    public function processByLessSimpleHist_v3(&$lvars, &$cvars) {
        // original one that works
        if ($lvars['action'] == "bid") {
            // there must be a 'credit' to bid as there can only be N asks for N bids... for now.  starts with a bid do bidcredit is initialized as 1
            if ($lvars['bidcredit'] > 0) {
                // only bid of teh decrease in price has dropped a certain percentage.. but cover the commissions as well
                //$_percent = $this->_getPercentDelta($lvars, $cvars);
                $lvars['timesig'] = $this->_getTime($lvars);
                //$lvars['lastUsedBidPrice'] = $lvars['lastData'][$lvars['k']]; uses LAST
                $lvars['lastUsedBidPrice'] = $lvars['lastDataBid'][$lvars['k']]; // uses HIGHESTBID
                // get curent BTC value to calc $$ amount


                $rs = $this->sumbmitBidRequest($lvars, $cvars);
                if (!$rs) {
                    print("ERROR IN BID SUBMISSION");
                    exit;
                }

//                print $this->b("you now have ${lvars['shares']} shares and ${lvars['BTC']} remaining\n");

                $r = $this->_getStatStr($lvars);

                $this->logIt($r, $cvars);
                //update state
                $lvars['lastbidbtc'] = $lvars['BTC']; // record for comparison
                $lvars['bidcredit'] --; // update counters
                $lvars['askcredit'] ++;
                $lvars['bids'] ++;
                $lvars['action'] = ".";
            }
        }
        if ($lvars['action'] == "ask") {
            if ($lvars['askcredit'] > 0) {
                $lvars['timesig'] = $this->_getTime($lvars);
                $lvars['proposedAskPrice'] = $lvars['lastDataAsk'][$lvars['k']];



//                $r = "====>>>>  " . $lvars['proposedAskPrice'] . " * " . $cvars['takerFee'] . " (" . $lvars['proposedAskPrice'] * $cvars['takerFee'] . ")  > " . $lvars['lastUsedBidPrice'] . "\n";
//                if ($lvars['proposedAskPrice'] * $cvars['takerFee'] > $lvars['lastUsedBidPrice']) {
//                    print $this->g($r);
//                } else {
//                    print $this->r($r);
//                }


                $sellAt = $lvars['proposedAskPrice'];
                $boughtAt = $lvars['lastUsedBidPrice'];


                $thispct = $this->ratioIncrease($boughtAt, $sellAt);
//                print ("=======>>>>>> [".$minpct."]\n");


                if ($thispct > $cvars['ar']['minpctup']) {
                    //                if ($lvars['proposedAskPrice'] * $cvars['takerFee'] > $lvars['lastUsedBidPrice']) {
                    //print "selling\n";
                    //$lvars['lastUsedAskPrice'] = $lvars['lastData'][$lvars['k']]; //uses LAST
                    $lvars['lastUsedAskPrice'] = $lvars['lastDataAsk'][$lvars['k']]; // uses LOWESTASK
                    //$lvars['BTC'] = $this->_newBtcVal($lvars, $cvars);

                    $r = $this->_getStatStr($lvars);
                    $rs = $this->sumbmitAskRequest($lvars, $cvars);
                    if (!$rs) {
                        print("ERROR IN ASK SUBMISSION");
                        exit;
                    }
                    //$r = $this->_getStatStr($lvars);

                    $this->logIt($r, $cvars);
                    $lvars['askcredit'] --;
                    $lvars['bidcredit'] ++;
//                    $lvars['BTC'] = $lvars['BTC'] * $cvars['takerFee']; // comission
                    $lvars['shares'] = 0;
                    $lvars['asks'] ++;
                    $lvars['action'] = ".";
                }
            }
        }
    }

    // **************************************************************************
    public function getLogfile($pair) {
        $f = "trx_${pair}.log";
        return($f);
    }

    // **************************************************************************
    public function getCurrentBTCval() {
        $r = 650; //file_get_contents("https://api.bitcoinaverage.com/ticker/USD/last");
        return($r);
    }

// *****************************************************************************
    public function get_minFromDb($lvars, $cvars, $key) {

        $conn = $cvars['conn'];

        $str = "select min(${key}) as minx from " . $cvars['ar']['dataset'] . " where name = '" . $lvars['pair']['name'] . "'";
        $st = $conn->prepare($str);
        $st->execute();
        $data = $st->fetch();



//        var_dump($data);exit;
        return($data['minx']);
    }

// *****************************************************************************
    public function get_data(&$lvars, &$cvars, $key, $data, $scale = false) {
        $retval = array();
        foreach ($data as $bar) {
            $retval[] = $bar[$key];
        }
        if ($scale) {
            $xfactor = 100 / $this->get_minFromDb($lvars, $cvars, $key); //0000;//100/min($retval);

            $lvars['xfactor'] = $xfactor;
            print ("SCALE=:$xfactor \n");


            foreach ($retval as &$r) {
                $r = $r * $xfactor;
            }
        }
        return $retval;
    }

    // *****************************************************************************
    public function stdData(&$lvars, &$cvars, $tdata, $scale = false) {
        $retval = array();
        foreach ($tdata as $data) {
            $retval[] = $data['last'];
        }

        if ($scale) {
            $xfactor = $cvars['ar']['scale'] / $this->get_minFromDb($lvars, $cvars, "last"); //0000;//100/min($retval);
            $lvars['xfactor'] = $xfactor;
            foreach ($retval as &$r) {
                $r = $r * $xfactor;
            }
        }
        return $retval;
    }

    // *****************************************************************************
    public function skewData(&$lvars, &$cvars, $tdata, $scale = false) {
        $retval = array();
        foreach ($tdata as $data) {
            $x = (1 + $data['last']);
            $y = (1 - $highestBid = $data['highestBid']);
            $z = $x * $y;
            $nn = pow(abs($z), 1.618); //545
            $retval[] = $nn;
        }

        if ($scale) {
            $xfactor = $cvars['ar']['scale'] / $this->get_minFromDb($lvars, $cvars, "last"); //0000;//100/min($retval);
            $lvars['xfactor'] = $xfactor;
            foreach ($retval as &$r) {
                $r = $r * $xfactor;
            }
        }
        return $retval;
    }

// *****************************************************************************
    public function percent($cost, $value) {

        //$r = sprintf("%6.2", ((1 - $cost) / $value) * 100, 2);
        $r = (($value - $cost) / $cost) * 100;


        return sprintf("%6.2", ((1 - $cost) / $value) * 100, 2);



//        return number_format((1 - $cost / $value) * 100, 2);
    }

// *****************************************************************************
    public function ratioIncrease($cost, $value) {

        //$r = sprintf("%6.2", ((1 - $cost) / $value) * 100, 2);
        $r = (($value - $cost) / $cost);


        return($r);



//        return number_format((1 - $cost / $value) * 100, 2);
    }

// *****************************************************************************
    public function diffpercent($n1, $n2) {
        return number_format(($n1 / $n2) * 100, 2);
    }

// *****************************************************************************
    public function avg($dd) {
        $avg = 0;
        $tot = 0;
        for ($i = 0; $i < count($dd); $i++) {
            $tot += $dd[$i];
        }
        return($tot / count($dd));
    }

// *****************************************************************************
    public function nf($n, $d) {
//    print"[${n} ${d}]\n";
        $r = number_format($n, $d);
//    print ($r."\n");
        return($r);
    }

// *****************************************************************************

    public function showhelp() {
        //    $this->logIt("using: macd4.php -f" . $arglist['fastPeriod'] . " -s" . $arglist['slowPeriod'] . " -S" . $arglist['signalPeriod'] . " -p" . $arglist['pair'] . " -m" . $arglist['mode'] . " -c" . $arglist['BTCinv'] . " " . (isset($arglist['debug']) ? " -d" : '') . (isset($arglist['debug']) ? " -h" : '') . "\n", $cvars);
        $str = <<<EOF
                    
               macd4.php -f[fastPeriod] -s[slowPeriodl] -S[signalPeriod] -p[pairname] -c[BTC investment] -x[xsteps] -a[minupticks] -b[mindnticks] -F[frompoint] -d(ebug) -h(elp)

               Ex: (using default values)
                    ./trigger2.php -f26 -s12 -S4 -pBTC_AMP -c1.0 -x7 -a4 -b1 -F1 [-d] [-h] 

                        In 'test' mode modelook for 1 minute changes on BTC_AMP and look for 
                        triggers based on the MACD, and use 1.0 BTC as your purchasing amount 
               where:    
                   -f | --fastperiod
                        MACD fast Period (default 26)
                    
                   -s | --slowperiod
                        MACD slow Period (default 12)
                    
                   -S | --signalperiod
                        MACD signal Period (default 9)
                    
                   -p | --pair
                        This can a an SQL complient string like "BTC_%", or the following embedded query public functions
                    
                            f_delta         - TOP 10 BASED ON LARGEST BID/ASK DIFFERENCE
                            f_topbv         - TOP 10 BASED ON LARGEST BASE VOLUME
                            f_top2bv        - TOP 10 BASED ON 2nd LARGEST BASE VOLUME
                            f_top3qv        - TOP 10 BASED ON 3nd LARGEST BASE VOLUME
                            f_tophilo       - TOP 10 BASED ON LARGEST HI/LO DELTA
                            f_tophiloXpcXbv - TOP 10 BASED ON LARGEST HI/LO DELTA, BASE VOLUME AND PERCENT INCREASE
                            f_bothilo       - TOP 10 BASED ON SMALLEST HI/LO DELTA, BASE VOLUME AND PERCENT INCREASE


                   -x | --xsteps
                        How oftenm to sample the data bu mintes.  
                            -x1 = sample every minute
                            -x7 = sample every 10 minutes
                                (note: from my teasting -x7 is best when -f26 -s12 -S4)
                   -c | --btcinv
                        How much BTC to use as a seed

                   -U | --minpctup
                        minimum % the asset must have risen to sell

                   -D | --maxpctdn
                        maximum % the asset can fall before dumping

                   -X | --xstepd
                        user on ever X datapoints (1 datapoint = 1 minute)

                   -z | --dataset
                        use a different dataset
                
                        1 = local polo data
                        2 = remote polo data
                
                        (the following are for web only)
                
                        3 = random numbers
                        4 = testdata1 (FNGN, ADP)
                        5 = testdata2 (Sin/Cos/tan curves, brownian, fractal))
                        6 = testdata3 (temps from 1890 - present)
                    
                    -a | --minupticks
                    -b | --mindnticks
                    -F | --frompoint
                
                   -d | --debug 
                        1 = basic output
                        
EOF;
        print($str);

        exit;
    }

// *****************************************************************************
    public function get_dbconn($which = "local") {
        /*

          the database that sores the histpoircal data looks like this

          CREATE TABLE `hist` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(255) DEFAULT NULL,
          `last` decimal(32,16) DEFAULT NULL,
          `lowestAsk` decimal(32,16) DEFAULT NULL,
          `highestBid` decimal(32,16) DEFAULT NULL,
          `percentChange` decimal(32,16) DEFAULT NULL,
          `baseVolume` decimal(32,16) DEFAULT NULL,
          `quoteVolume` decimal(32,16) DEFAULT NULL,
          `isFrozen` int(11) DEFAULT NULL,
          `high24hr` decimal(32,16) DEFAULT NULL,
          `low24hr` decimal(32,16) DEFAULT NULL,
          `time` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
          )

          and is populated my a rouiting that every minute get the ticker data from
          poloniex and stores into this table
         */


        if ($which == "histremote") {
            $dsi = "mysql:host=108.161.131.78;dbname=polo;charset=utf8mb4";
            $dbuser = "expDBuser";
            $dbpass = "hddjw734ndfh";
            $conn = new PDO($dsi, $dbuser, $dbpass);
            $conn->exec("SET CHARACTER SET utf8");
            return($conn);
        } else {
            $dsi = "mysql:host=localhost;dbname=polo;charset=utf8mb4";
            $dbuser = "root";
            $dbpass = "";
            $conn = new PDO($dsi, $dbuser, $dbpass);
            $conn->exec("SET CHARACTER SET utf8");
            return($conn);
        }
    }

// *****************************************************************************
    public function get_trxdata(&$lvars, &$cvars) {

        $pair = $lvars['pair']['name'];
        $conn = $cvars['conn'];

        // limit to 'samples'

        $str = "SELECT * FROM (SELECT * FROM " . $cvars['ar']['dataset'] . " where name = '" . $pair . "' ORDER BY id DESC LIMIT " . $cvars['ar']['frompoint'] . "," . $cvars['samples'] . ") sub ORDER BY id ASC";
//        $str = "select * FRom " . $cvars['ar']['dataset'] . " where name = '" . $pair . "'";

        $st = $conn->prepare($str);
        $st->execute();
        $data = $st->fetchAll();
        return($data);
    }

// *****************************************************************************
    public function get_trxdataRnd($args) {

        $pair = "BTC_AMP";
        $k = $args['k'];
        $rechour = $args['rechour'];
        $conn = $args['conn'];
        //$mode = $args['mode'];

        $str = "select * from histrandom";
        //if ($mode == "t") {

            $to = $rechour * 3 + $k;
            $str = "SELECT * FROM (SELECT * FROM histrandom ORDER BY id DESC LIMIT $k, $to ) sub ORDER BY id ASC";
        //}
        $st = $conn->prepare($str);
        $st->execute();
        $data = $st->fetchAll();
        return($data);
    }

// *****************************************************************************
    public function doQuery($q, &$cvars) {
        $st = $cvars['conn']->prepare($q);
        $st->execute();
        return($st->fetchAll());
    }

// *****************************************************************************
    public function r($str) {
        $cstr = "";
        if (PHP_SAPI === 'cli') {
            $cstr = "\033[31m${str}\033[0m";
        } else {
            $cstr = "<span style='color:red'>${str}</span>";
        }
        return($cstr);
    }

// *****************************************************************************
    public function g($str) {
        $cstr = "";
        if (PHP_SAPI === 'cli') {
            $cstr = "\033[32m${str}\033[0m";
        } else {

            $cstr = "<span style='color:green'>${str}</span>";
        }
        return($cstr);
    }

// *****************************************************************************
    public function y($str) {
        $cstr = "";
        if (PHP_SAPI === 'cli') {
            $cstr = "\033[33m${str}\033[0m";
        } else {

            $cstr = "<span style='color:yellow'>${str}</span>";
        }
        return($cstr);
    }

// *****************************************************************************
    public function b($str) {
        $cstr = "";
        if (PHP_SAPI === 'cli') {
            $cstr = "\033[34m${str}\033[0m";
        } else {

            $cstr = "<span style='color:blue'>${str}</span>";
        }
        return($cstr);
    }

// *****************************************************************************
    public function chopary($ary, $cut) {

        $nary = array_slice($ary, $cut);
        return(array_values($nary));
    }

// *****************************************************************************
    public function dprint($str, $c = "-") {
        print "${c}[" . $str . "]\n";
    }

// *****************************************************************************
    public function get_top_bv($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
SELECT name
FROM 
    ${db}
where 
    name like 'BTC_%'
GROUP BY
    name
order by 
   ${db}.baseVolume 
        DESC limit 10
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_picked($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
SELECT DISTINCT name
from ${db}
WHERE
name = 'BTC_ETH'
or name = 'BTC_ETHC'
or name = 'BTC_XMR' 
or name = 'BTC_DASH'
or name = 'BTC_LTC' 
or name = 'BTC_DOGE'
or name = 'BTC_NXT' 
or name = 'BTC_BTCD'
or name = 'BTC_XRP';
EOX;

        /*

         */

        return($str);
    }

// *****************************************************************************
    public function get_top2_bv($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
SELECT *
FROM 
    ${db}
where 
    name like 'BTC_%'
GROUP BY
    name
order by 
    ${db}.baseVolume 
        DESC limit 10,10
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_top3_bv($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
SELECT *
FROM 
    ${db}
where 
    name like 'BTC_%'
GROUP BY
    name
order by 
    ${db}.baseVolume 
        DESC limit 20,10
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_top_qv($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
SELECT *
FROM 
    ${db}
where 
    name like 'BTC_%'
GROUP BY
    name
order by 
    ${db}.quoteVolume 
        DESC limit 10
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_top_pc($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
        SELECT distinct
${db}.`name`
FROM
${db}
where name like 'BTC_%'
GROUP BY
name,percentChange
order by percentChange DESC limit 10
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_top_delta($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
select name from (SELECT DISTINCT
${db}.`name`,
${db}.lowestAsk / ${db}.highestBid as delta
FROM
${db}
where name like 'BTC_%'
GROUP BY
name
order by delta desc
limit 10) as f
        
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_top_hilo($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
    select name from (SELECT DISTINCT
${db}.`name`,
${db}.high24hr / ${db}.low24hr as delta
FROM
${db}
where name like 'BTC_%'
GROUP BY
name
order by delta desc
limit 10) as f
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_delta_hilo($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
SELECT
g.name, f.name
FROM
	(
		SELECT DISTINCT
${db}.`name`,
${db}.lowestAsk / ${db}.highestBid as delta
FROM
${db}
where name like 'BTC_%'
GROUP BY
name
order by delta desc
	) AS f,
	(
		SELECT *
FROM 
    ${db}
where 
    name like 'BTC_%'
GROUP BY
    name
order by 
    ${db}.quoteVolume 
        DESC
	) AS g
WHERE
	f.NAME = g.NAME limit 10
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_delta_hilo_small($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
SELECT
g.name, f.name
FROM
	(
		SELECT DISTINCT
${db}.`name`,
${db}.lowestAsk / ${db}.highestBid as delta
FROM
${db}
where name like 'BTC_%'
GROUP BY
name
order by delta asc
	) AS f,
	(
		SELECT *
FROM 
    ${db}
where 
    name like 'BTC_%'
GROUP BY
    name
order by 
    ${db}.quoteVolume 
        DESC
	) AS g
WHERE
	f.NAME = g.NAME limit 10
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_top_delta_bv($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
SELECT
f.name
FROM
	(
		SELECT DISTINCT
${db}.`name`,
${db}.lowestAsk / ${db}.highestBid as delta
FROM
${db}
where name like 'BTC_%'
GROUP BY
name
order by delta desc 
	) AS f,
	(
		SELECT *
FROM 
    ${db}
where 
    name like 'BTC_%'
GROUP BY
    name
order by 
    ${db}.baseVolume 
        DESC 
	) AS g
WHERE
	f.NAME = g.NAME limit 10
EOX;

        return($str);
    }

    public function get_top_hiloXpcXbv($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
select f.name from (SELECT DISTINCT
	NAME
FROM
	${db}
WHERE
	NAME LIKE 'BTC_%'
ORDER BY
	(
		(
			(
				(lowestAsk - highestBid) / lowestAsk
			) * 1000000
		)
	) * (percentChange * 1000000) DESC limit 40
) as f , (SELECT distinct
${db}.`name`
FROM
${db}
where name like 'BTC_%'
GROUP BY
name
order by ${db}.baseVolume DESC limit 40) as g
where g.name = f.name limit 10
EOX;
        return($str);
    }

// *****************************************************************************
    public function get_top_hiloXpcXqv($cvars) {
        $db = $cvars['ar']['dataset'];

        $str = <<<EOX
select f.name from (SELECT DISTINCT
	NAME
FROM
	${db}
WHERE
	NAME LIKE 'BTC_%'
ORDER BY
	(
		(
			(
				(lowestAsk - highestBid) / lowestAsk
			) * 1000000
		)
	) * (percentChange * 1000000) DESC limit 40
) as f , (SELECT distinct
${db}.`name`
FROM
${db}
where name like 'BTC_%'
GROUP BY
name
order by ${db}.quoteVolume DESC limit 40) as g
where g.name = f.name limit 10
EOX;
        return($str);
    }

// *****************************************************************************
    public function logIt($str, &$cvars, $live = null) {
        file_put_contents("log/" . $cvars['logfile'], $str, FILE_APPEND);
        if ($cvars['ar']['debug']) {
            print $str;
        }
        if ($live) {
            file_put_contents("LIVE_" . $cvars['logfile'], $str, FILE_APPEND);
        }
    }

// *****************************************************************************
    public function getQstr($pair, $cvars) {
        $str = 0;
        if ($pair == "f_delta") {
            print "[f_delta] TOP 10 BASED ON SMALLEST HI/LO DELTA, BASE VOLUME AND PERCENT INCREASE\n";
            $str = $this->get_top_delta_bv($cvars);
        }
        if ($pair == "f_topbv") {
            print "[f_topbv] TOP 10 BASED ON LARGEST BASE VOLUME\n";
            $str = $this->get_top_bv($cvars);
        }
        if ($pair == "f_top2bv") {
            print "[f_top2bv] TOP 10 BASED ON 2nd LARGEST BASE VOLUME\n";
            $str = $this->get_top2_bv($cvars);
        }
        if ($pair == "f_top3bv") {
            print "[f_top3bv] TOP 10 BASED ON 3rd LARGEST BASE VOLUME\n";
            $str = $this->get_top3_bv($cvars);
        }
        if ($pair == "f_topqv") { //broke
            print "[f_topqv] TOP 10 BASED ON LARGEST QUERY VOLUME\n";
            $str = $this->get_top3_qv($cvars);
        }
        if ($pair == "f_tophilo") {
            print "[f_tophilo] TOP 10 BASED ON LARGEST HI/LO DELTA\n";
            $str = $this->get_top_hilo($cvars);
        }
        if ($pair == "f_tophiloXpcXbv") {
            print "[f_tophiloXpcXbv]  TOP 10 BASED ON LARGEST HILO DELTA, BASE VOLUME AND PERCENT INCREASE\n";
            $str = $this->get_top_hiloXpcXbv($cvars);
        }
        if ($pair == "f_picked") {
            print "[f_picker]  GROUP OF HAND PICKED PAIRS\n";
            $str = $this->get_picked($cvars);
        }
        if ($pair == "f_bothilo") {
            print "[f_bothilo]  BOTTOM 10 BASED ON SMALLEST HI/LO DELTA\n";
            $str = $this->get_delta_hilo_small($cvars);
        }
        return($str);
    }

    /**
     * Get options from the command line or web request
     * 
     * @param string $options
     * @param array $longopts
     * @return array
     */
    public function getoptreq($options, $longopts) {
        if (PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR'])) {  // command line
            return getopt($options, $longopts);
        } else if (isset($_REQUEST)) {  // web script
            $found = array();

            $shortopts = preg_split('@([a-z0-9][:]{0,2})@i', $options, 0, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $opts = array_merge($shortopts, $longopts);

            foreach ($opts as $opt) {
                if (substr($opt, -2) === '::') {  // optional
                    $key = substr($opt, 0, -2);

                    if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key]))
                        $found[$key] = $_REQUEST[$key];
                    else if (isset($_REQUEST[$key]))
                        $found[$key] = false;
                }
                else if (substr($opt, -1) === ':') {  // required value
                    $key = substr($opt, 0, -1);

                    if (isset($_REQUEST[$key]) && !empty($_REQUEST[$key]))
                        $found[$key] = $_REQUEST[$key];
                }
                else if (ctype_alnum($opt)) {  // no value
                    if (isset($_REQUEST[$opt]))
                        $found[$opt] = false;
                }
            }

            return $found;
        }

        return false;
    }

    public function getARopts(&$cvars) {
        $xr = $this->getoptreq('f:s:S:p:c:x:U:D:X:z:a:b:F:d:h', array('fastperiod:', 'slowperiod:', 'signalperiod:', 'pairq:', 'btcinv:', 'xsteps:', 'minpctup:', 'maxpctdn:', 'method:', 'data:', 'minupticks:', 'mindnticks:', 'frompoint' . 'debug:', 'help'));

        $cvars['ar']['debug'] = 0;
        foreach (array_keys($xr) as $opt)
            switch ($opt) {
                case 'f':
                    $cvars['ar']['fastPeriod'] = $xr['f'];
                    break;

                case 's':
                    $cvars['ar']['slowPeriod'] = $xr['s'];
                    break;

                case 'S':
                    $cvars['ar']['signalPeriod'] = $xr['S'];
                    break;

                case 'p':
                    $cvars['ar']['pairq'] = $xr['p'];
                    break;

                case 'c':
                    $cvars['ar']['BTCinv'] = $xr['c'];
                    break;

                case 'x':
                    $cvars['ar']['xsteps'] = $xr['x'];
                    break;

                case 'U':
                    $cvars['ar']['minpctup'] = $xr['U'] / 100;
                    break;

                case 'D':
                    $cvars['ar']['maxpctdn'] = $xr['D'] / 100;
                    break;

                case 'X':
                    $cvars['ar']['method'] = $xr['X'];
                    break;

                case 'z':
                    switch ($xr['z']) {
                        case '1':
                            $cvars['ar']['dataset'] = 'hist';
                            break;
                        case '2':
                            $cvars['ar']['dataset'] = 'histremote';
                            break;
                        case '3':
                            $cvars['ar']['dataset'] = 'histrandom';
                            break;
                        case '4':
                            $cvars['ar']['dataset'] = 'testcase1';
                            break;
                        case '5':
                            $cvars['ar']['dataset'] = 'testcase2';
                            break;
                        case '6':
                            $cvars['ar']['dataset'] = 'testcase3';
                            break;
                    }
                    break;

                case 'a':
                    $cvars['ar']['upticks'] = $xr['a'];
                    break;

                case 'b':
                    $cvars['ar']['dnticks'] = $xr['b'];
                    break;

                case 'F':
                    $cvars['ar']['frompoint'] = $xr['F'];
                    break;

                case 'd':
                    $cvars['ar']['debug'] = 1;
                    break;

                case 'h':
                    $this->showhelp();
                    $cvars['ar']['help'] = 1;
                    break;
            }

        $cvars['ar']['fastPeriod'] = (isset($cvars['ar']['fastPeriod']) ? $cvars['ar']['fastPeriod'] : 23);
        $cvars['ar']['slowPeriod'] = (isset($cvars['ar']['slowPeriod']) ? $cvars['ar']['slowPeriod'] : 14);
        $cvars['ar']['signalPeriod'] = (isset($cvars['ar']['signalPeriod']) ? $cvars['ar']['signalPeriod'] : 9);
        $cvars['ar']['pairq'] = (isset($cvars['ar']['pairq']) ? $cvars['ar']['pairq'] : "BTC_AMP");
        $cvars['ar']['BTCinv'] = (isset($cvars['ar']['BTCinv']) ? $cvars['ar']['BTCinv'] : 1);
        $cvars['ar']['xsteps'] = (isset($cvars['ar']['xsteps']) ? $cvars['ar']['xsteps'] : 7);
        $cvars['ar']['minpctup'] = (isset($cvars['ar']['minpctup']) ? $cvars['ar']['minpctup'] : 0.0125);
        $cvars['ar']['maxpctdn'] = (isset($cvars['ar']['maxpctdn']) ? $cvars['ar']['maxpctdn'] : .06);
        $cvars['ar']['dataset'] = (isset($cvars['ar']['dataset']) ? $cvars['ar']['dataset'] : 'hist');
        $cvars['ar']['method'] = (isset($cvars['ar']['method']) ? $cvars['ar']['method'] : 4);
        $cvars['ar']['upticks'] = (isset($cvars['ar']['upticks']) ? $cvars['ar']['upticks'] : 3);
        $cvars['ar']['dnticks'] = (isset($cvars['ar']['dnticks']) ? $cvars['ar']['dnticks'] : 2);
        $cvars['ar']['frompoint'] = (isset($cvars['ar']['frompoint']) ? $cvars['ar']['frompoint'] : 1);
        $cvars['ar']['debug'] = (isset($cvars['ar']['debug']) ? $cvars['ar']['debug'] : 7);

        //return($ar);
    }

//***************************************************************************************************
    public function makeGraphs1(&$lvars, &$cvars, &$macdary) {

        system("rm img/*");

        if (PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
//        if (1 == 0) {
        } else {
            $w = count($lvars['lastData']) * 3;
            if ($cvars['ar']['expand'] != 0) {
                $w = 4068;
            }
            $h = 500;

            $of[0] = "Rlast_" . $lvars['pair']['name'] . ".png"; // start with last_BTC_XMR.png
            $of[1] = "Rbuypv_" . $lvars['pair']['name'] . ".png";
            $of[2] = "Rsellpv_" . $lvars['pair']['name'] . ".png";
//            $a = $this->normalize($lvars['buyPointsVal'], $miny, $maxy);
//            $miny = min($lvars['sellPointsVal']);
//            $maxy = max($lvars['buyPointsVal']);
//            $miny = min($lvars['lastData']);
//            $maxy = max($lvars['lastData']);
//
//            print "[$miny][$maxy]\n";
//            var_export($lvars['lastData']);
//            $miny -= $miny*4000;
//            $maxy += $maxy*4000;
///////////////////////////////////////////////////////////
            $data = $lvars['lastData'];


            foreach ($data as $i => $v) {
                $data[$i] = $data[$i] * 1000000;
            }
            $min = min($data);
            $max = max($data);
            $avg = ($max - $min) / 2;
            foreach ($data as $i => $v) {
                $data[$i] = $data[$i] - $avg;
            }

            $miny = min($data);
            $maxy = max($data);



            $this->genPlot(array('pData' => $this->dataPrepLast($data), 'w' => $w, 'h' => $h, 'of' => $of[0], 'miny' => $miny, 'maxy' => $maxy, 'title' => "Signal", 'color' => "black", 'type' => "lines"));
///////////////////////////////////////////////////////////
            $data = $lvars['buyPointsVal'];

            foreach ($data as $i => $v) {
                $data[$i] = $data[$i] * 1000000 - $avg;
            }
            $this->genPlot(array('pData' => $this->dataPrepLast($data), 'w' => $w, 'h' => $h, 'of' => $of[1], 'miny' => $miny, 'maxy' => $maxy, 'title' => "Signal", 'color' => "blue", 'type' => "points"));
///////////////////////////////////////////////////////////
            $data = $lvars['sellPointsVal'];

            foreach ($data as $i => $v) {
                $data[$i] = $data[$i] * 1000000 - $avg;
            }

            $this->genPlot(array('pData' => $this->dataPrepLast($data), 'w' => $w, 'h' => $h, 'of' => $of[2], 'miny' => $miny, 'maxy' => $maxy, 'title' => "Signal", 'color' => "green", 'type' => "points"));







//          $a = $this->normalize($lvars['buyPointsVal'], $miny, $maxy);
//            $a = $lvars['buyPointsVal'];
//            $pData = $this->dataPrepBuy($lvars['buyPointsVal']);
//            genPlot(array('pData' => $pData, 'w' => $w, 'h' => $h, 'of' => $of[1], 'miny' => $miny, 'maxy' => $maxy, 'title' => "Signal", 'color' => "blue", 'type' => "points"));
////
//////   
////   
////   
////   
////   //   'point' sell line    
////            if (isset($of[5])) {
////                $a = $this->normalize($lvars['sellPointsVal'], $miny, $maxy);
//                $pData = $this->dataPrepSell($lvars['sellPointsVal']);
//                genPlot(array('pData' => $pData, 'w' => $w, 'h' => $h, 'of' => $of[2], 'miny' => $miny, 'maxy' => $maxy, 'title' => "Signal", 'color' => "green", 'type' => "points"));
////            }
//

            $src = array();
            $out = array();
            $savedas = "";
//////////////////
            //for ($j = 0; $j < count($of)-1 ; $j++) {
            $savedas = $this->genPlotMake($of);
            //print "final =  [$savedas]\n";
            //}

            print "<div style='width:${w}px;position:relative;padding:30px;background-color:#eeFFFF;border:6px solid black'><img id='./img/" . $savedas . "'src='./img/" . $savedas . "' /></div>";
            //          echo "3<div style='position:relative;'><img src='combined.png' /></div>";
        }
    }

//***************************************************************************************************
//***************************************************************************************************
//***************************************************************************************************
//***************************************************************************************************
    public function makeGraphs2(&$lvars, &$cvars, &$macdary) {
        if (PHP_SAPI === 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
//        if (1 == 0) {
        } else {

            //$w = count($lvars['lastData']) * 3;
            $w = count($lvars['lastData']) * 3;
            if ($cvars['ar']['expand'] != 0) {
                $w = 4068;
            }
            $h = 500;
            $miny = -1;
            $maxy = 1;


            $of[0] = "last_" . $lvars['pair']['name'] . ".png"; // start with last_BTC_XMR.png
            $of[1] = "macd_" . $lvars['pair']['name'] . ".png"; // and add macd_BTC_XMR.png
            $of[2] = "buyp_" . $lvars['pair']['name'] . ".png";
            $of[3] = "sellp_" . $lvars['pair']['name'] . ".png";
            //$of[4] = "buypv_" . $lvars['pair']['name'] . ".png";
            //$of[5] = "sellpv_" . $lvars['pair']['name'] . ".png";
            $miny = min($macdary[0]);
            $maxy = max($macdary[0]);



//  last    
            if (isset($of[0])) {
                $a = $this->normalize($lvars['lastData'], $miny, $maxy);
                $pData = $this->dataPrepLast($a);
                $this->genPlot(array('pData' => $pData, 'w' => $w, 'h' => $h, 'of' => $of[1], 'miny' => $miny, 'maxy' => $maxy, 'title' => "Signal", 'color' => "black", 'type' => "lines"));
            }
//  macd
            if (isset($of[1])) {
                $pData = $this->dataPrepMacd($macdary);
                $this->genPlot(array('pData' => $pData, 'w' => $w, 'h' => $h, 'of' => $of[0], 'miny' => $miny, 'maxy' => $maxy, 'title' => "Signal", 'color' => array('red', 'blue', 'green'), 'type' => "lines"));
            }
//  buy
            if (isset($of[2])) {
                $a = $lvars['buyPoints'];
                $pData = $this->dataPrepBuy($a);
                $this->genPlot(array('pData' => $pData, 'w' => $w, 'h' => $h, 'of' => $of[2], 'miny' => $miny, 'maxy' => $maxy, 'title' => "Signal", 'color' => "blue", 'type' => "points"));
            }
//   'point' sell line    
            if (isset($of[3])) {
                $a = $lvars['sellPoints'];
                $pData = $this->dataPrepSell($a);
                $this->genPlot(array('pData' => $pData, 'w' => $w, 'h' => $h, 'of' => $of[3], 'miny' => $miny, 'maxy' => $maxy, 'title' => "Signal", 'color' => "green", 'type' => "points"));
//   'point' buy line    
            }



            $src = array();
            $out = array();
            $savedas = "";
//////////////////
            //for ($j = 0; $j < count($of)-1 ; $j++) {
            $savedas = $this->genPlotMake($of);
            //print "final =  [$savedas]\n";
            //}

            print "<div style='width:${w}px;position:relative;padding:30px;background-color:#FFeeFF;border:6px solid black'><img id='./img/" . $savedas . "'src='./img/" . $savedas . "' /></div>";
            //          print "3<div style='position:relative;'><img src='combined.png' /></div>";
        }
    }

    public function genPlot($args) {

        $pData = $args['pData'];
        $w = $args['w'];
        $h = $args['h'];
        $of = "./img/" . $args['of'];
        $miny = $args['miny'];
        $maxy = $args['maxy'];
        $title = $args['title'];
        $color = $args['color'];
        $type = $args['type'];


        $plot4 = new PHPlot($w, $h, $of);
        $plot4->SetImageBorderType('plain');
        $plot4->SetPlotType($type);
        $plot4->SetDataType('data-data');
        $plot4->SetTransparentColor(array(255, 255, 255));
        $plot4->SetDataColors($color);
        $plot4->SetDataValues($pData);
        $plot4->SetLineStyles('solid');

        if ($type == 'points') {
            $plot4->SetPointSizes(15);
        } else {
            $plot4->SetLineWidths(2);
        }
# Main plot title:
        $plot4->SetTitle($title);
        $plot4->SetPlotAreaWorld(NULL, $miny, null, $maxy);
        $plot4->SetPrintImage(true);
        $plot4->SetFileFormat("png");
        $plot4->SetIsInline(true);

        $plot4->SetOutputFile($of);
        $plot4->DrawGraph();
        //print "writing to $of\n";
//            print "outputting SELL data to ${of[3]}\n";
//            print "<div style='position:relative; z-index:10'><img src=' ${of[3]}' /></div>";
//            exit;
//            print "<div style='position:relative; z-index:10;top:-600px'><img src='${of}' /></div>";
// now combinethem
    }

    public function genPlotMake($of) {
        $src = array();
        for ($v = 0; $v < count($of) - 1; $v++) {
//            $c = ($v>0?($v-1)."_":'');
//            $out = $v . "_" . $of[$v];
            $out = $v . ".png";
            //print "combining " . $of[$v] . " + " . $of[$v + 1] . " > $out\n";
            $src[$v] = new \Imagick("./img/" . $of[$v]);
            $src[$v + 1] = new \Imagick("./img/" . $of[$v + 1]); //            print "combining: ${of[0]} + ${of[1]} -> ${out[$v]}\n";

            $src[$v]->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);
            $src[$v]->setImageArtifact('compose:args', "1,0,-0.5,0.5");
            $src[$v]->compositeImage($src[$v + 1], Imagick::COMPOSITE_MATHEMATICS, 0, 0);
            //rint "Combined output to ./img/".$out."\n";
            $of[$v + 1] = $out;
            $src[$v]->writeImage("./img/" . $out);
        }

        $uout = uniqid() . $out;
        rename("./img/" . $out, "./img/" . $uout);

        return($uout);
    }

    public function normalize($data, $new_min = 0, $new_max = 0) {
        $min = min($data);
        $max = max($data);
        if ($min + $max != 0) {
            foreach ($data as $i => $v) {
                $data[$i] = ((($new_max - $new_min) * ($v - $min)) / ($max - $min)) + $new_min;
            }
        }

        return($data);
    }

    public function normalizeX($data, $new_min = 0, $new_max = 0) {
        $min = min($data);
        $max = max($data);
        if ($min + $max != 0) {
            foreach ($data as $i => $v) {
                $data[$i] = (((($new_max - $new_min) * ($v - $min)) / ($max - $min)) + $new_min) * 100000;
            }
        }

        return($data);
    }

    public function dataPrepMacd($macdary) {
        $pData = array();

        $macd1 = array_values($macdary[0]);
        $macd2 = array_values($macdary[1]);
        $macd3 = array_values($macdary[2]);

        for ($q = 0; $q < count($macd1); $q++) {
            $x = array(''
                , $q
                , $macd1[$q]
                , $macd2[$q]
                , $macd3[$q]
            );
            array_push($pData, $x);
        }
        return( $pData );
    }

    public function dataPrepLast($a) {
        $pData = array();
        foreach ($a as $q => $v) {
            $x = array(''
                , $q
                , $v
            );
            array_push($pData, $x);
        }
        return($pData);
    }

    public function dataPrepBuy($a) {
        $pData = array();
        foreach ($a as $q => $v) {
            $x = array(''
                , $q
                , $v
            );
            array_push($pData, $x);
        }
        return($pData);
    }

    public function dataPrepSell($a) {
        $pData = array();
        foreach ($a as $q => $v) {
//        for ($q = 0; $q < count($a); $q++) {
            $x = array(''
                , $q
                , $v
            );
            array_push($pData, $x);
        }
        return($pData);
    }

}

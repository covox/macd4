#!/usr/sbin/php
<?php
$conn = new PDO('mysql:host=localhost;dbname=polo;charset=utf8mb4', 'root', '');
$conn->exec("SET CHARACTER SET utf8");

$count = 1;


$last = 10;
$lowestAsk = 10;
$highestBid = 10;
$percentChange = 10;
$baseVolume = 10;
$quoteVolume = 10;
$isFrozen = 0;
$high24hr = 10;
$low24hr = 10;
$time = $date = date('Y-m-d H:i:s');

$params = array();

$str = <<<EOX
                delete from testdata2 where isFrozen <1
EOX;
$st = $conn->prepare($str);
$st->execute();

$k = 0;

        $s = c9($j, $conn);
exit;

for ($i = 1; $i <= $count; $i++) {
    print "\n$i - ";

    for ($j = 0; $j <= 360; $j = $j + .01) {
        $time = $date = date('Y-m-d H:i:s');
        //$s = c1($j);
        //$s = c2($j);
        //$s = c3($j);
        //$s = c4($j);
        //$s = c5($j);
        //$s = c6($j);
        //$s = c7($j, $conn);
        $last = $s['last'];
        $lowestAsk = $s['lowestAsk'];
        $highestBid = $s['highestBid'];
        $name = $s['name'];
//        print "last: $last\t\tlow: $lowestAsk\t\thigh: $highestBid\n";
// turn off for C7       wdb($conn, $last, $loestAsk, $highestBid);


        $k++;
        if ($k > 1290) {
            exit;
        }
    }
}

function c9($j, $conn) {

    $x = array();
    $y = array();
    $x = array();






    $name = "C9 - fractal waves 3";
    $time = $date = date('Y-m-d H:i:s');
    $s = sin($j);
    $lowestAsk = $s * 1.01; // (mt_rand(0, 1));
    $highestBid = $s * .99; // (mt_rand(0, 1));
    //return(array('name' => $name, 'last' => $s, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));

    $a=0;
    $b=0;
    $c=0;
    
    
    for ($o = 0; $o < 1000; $o++) {
        $a += .01; 
        $x[] = sin($a);
    }
    for ($p = 0; $p < 1000; $p++) {
        $b += .01; 
        $y[] = mt_rand(-.000000001, 000000001);
        
    }
    for ($i = 0; $i < count($x); $i++) {
        $k = ($x[$i]+100) + abs($y[$i]) ;


        $lowestAsk = $k * 1.01; // (mt_rand(0, 1));
        $highestBid = $k * .99; // (mt_rand(0, 1));
        print "last: $k\t\tlow: $lowestAsk\t\thigh: $highestBid\n";

        wdb($conn, $k, $lowestAsk, $highestBid, $name);
    }
}

function c8($j, $conn) {

    $x = array();
    $y = array();
    $x = array();






    $name = "C8 - fractal waves 2";
    $time = $date = date('Y-m-d H:i:s');
    $s = sin($j);
    $lowestAsk = $s * 1.01; // (mt_rand(0, 1));
    $highestBid = $s * .99; // (mt_rand(0, 1));
    //return(array('name' => $name, 'last' => $s, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));

    $a=0;
    $b=0;
    $c=0;
    
    for ($o = 0; $o < 1000; $o++) {
        $a += .001; 
        $x[] = sin($a);
    }
    for ($p = 0; $p < 1000; $p++) {
        $b += .01; 
        $y[] = sin($b);
    }
    for ($q = 0; $q < 1000; $q++) {
        $c += .01; 
        $z[] = sin($c);
    }

    for ($i = 0; $i < count($x); $i++) {
        $k = $x[$i] + $y[$i] + $z[$i];


        $lowestAsk = $k * 1.01; // (mt_rand(0, 1));
        $highestBid = $k * .99; // (mt_rand(0, 1));
        print "last: $k\t\tlow: $lowestAsk\t\thigh: $highestBid\n";

        wdb($conn, $k, $lowestAsk, $highestBid, $name);
    }
}

function c7($j, $conn) {
    $name = "C7 - fractal waves";
    $time = $date = date('Y-m-d H:i:s');
    $s = sin($j);
    $lowestAsk = $s * 1.01; // (mt_rand(0, 1));
    $highestBid = $s * .99; // (mt_rand(0, 1));
    //return(array('name' => $name, 'last' => $s, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));
    for ($o = 0; $o < 360; $o+=3) {
        $s1 = sin($o) + $s;
        $lowestAsk = $s1 * 1.01; // (mt_rand(0, 1));
        $highestBid = $s1 * .99; // (mt_rand(0, 1));
//        return(array('name' => $name, 'last' => $s1, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));
        print "last: $s1\t\tlow: $lowestAsk\t\thigh: $highestBid\n";

        wdb($conn, $s1, $lowestAsk, $highestBid, $name);
    }
}

function c6($j) {
    $name = "C6 - incline";
    $time = $date = date('Y-m-d H:i:s');

    for ($o = 0; $o < 360; $o++) {
        $k = $j;
        $s = ((sin($k + $o) * (cos($k + $o) * tan($k + $o)) + 10)); //* mt_rand(0,($s*.1))+60); ;   //get y-axis with farmula

        $last = abs($k);
        $lowestAsk = $k * 1.01; // (mt_rand(0, 1));
        $highestBid = $k * .99; // (mt_rand(0, 1));
        return(array('name' => $name, 'last' => $last, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));
    }
}

function c5($j) {
    $name = "C5 - wave test s*c*t";
    $time = $date = date('Y-m-d H:i:s');

    $s = ((sin($j) * (cos($j) * tan($j)) + 10)); //* mt_rand(0,($s*.1))+60); ;   //get y-axis with farmula

    $last = abs($s);
    $lowestAsk = $s * 1.01; // (mt_rand(0, 1));
    $highestBid = $s * .99; // (mt_rand(0, 1));


    return(array('name' => $name, 'last' => $last, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));
}

function c4($j) {
    $name = "C4 - wave test - slikes - s+c+t ";
    $time = $date = date('Y-m-d H:i:s');

    $s = ((sin($j) + (cos($j) + tan($j)) + 10)); //* mt_rand(0,($s*.1))+60); ;   //get y-axis with farmula

    $last = abs($s);
    $lowestAsk = $s * 1.01; // (mt_rand(0, 1));
    $highestBid = $s * .99; // (mt_rand(0, 1));


    return(array('name' => $name, 'last' => $last, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));
}

function c3($j) {
    $name = "C3 - moving last, stable hilo";
    $time = $date = date('Y-m-d H:i:s');

    $s = ((sin($j) + 10)); //* mt_rand(0,($s*.1))+60); ;   //get y-axis with farmula

    $last = abs($s);
    $lowestAsk = $s * 1.01; // (mt_rand(0, 1));
    $highestBid = $s * .99; // (mt_rand(0, 1));

    return(array('name' => $name, 'last' => $last, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));
}

function c2($j) {
    $name = "C2 - moving last, stable hilo"; // 1% movng hilo
    $last = abs(((sin($j)) * 10)); //* mt_rand(0,($s*.1))+60); ;   //get y-axis with farmula
    $last += mt_rand($last * .90, $last * 1.1); // 10% variance
    $lowestAsk = $last; // + (mt_rand($rn * -1, $rn));
    $highestBid = $last; // - (mt_rand($rn * -1, $rn));
    return(array('name' => $name, 'last' => $last, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));
}

function c1($j) {
    $name = "C1 - static hilo, stable last";
    $s = ((sin($j) * 10)); //* mt_rand(0,($s*.1))+60); ;   //get y-axis with farmula
    $last = abs($s);
    $rn = mt_rand(($last), ($last));
    $lowestAsk = $s; //+ (mt_rand($rn * -1, $rn));
    $highestBid = $s; // (mt_rand($rn * -1, $rn));
    return(array('name' => $name, 'last' => $last, 'lowestAsk' => $lowestAsk, 'highestBid' => $highestBid));
}

function wdb(&$conn, $last, $lowestAsk, $highestBid, $name) {

    if (($last != 0) || ($highestBid != 0) || ($lowestAsk != 0)) {
        //print "[xxxx] wdb(conn, $last, $lowestAsk, $highestBid,$name) \n";
        $str = "insert into testdata2 (id,last ,lowestAsk ,highestBid ,percentChange ,baseVolume ,quoteVolume ,isFrozen ,high24hr ,low24hr ,name ,time) values (0, :last ,:lowestAsk, :highestBid, :percentChange, :baseVolume , :quoteVolume ,:isFrozen ,:high24hr ,:low24hr ,:name ,:time)";
        $st = $conn->prepare($str);
        $st->bindParam(":last", $last);
        $st->bindParam(":lowestAsk", $lowestAsk);
        $st->bindParam(":highestBid,", $highestBid);
        $st->bindParam(":percentChange", $percentChange);
        $st->bindParam(":baseVolume", $baseVolume);
        $st->bindParam(":quoteVolume", $quoteVolume);
        $st->bindParam(":isFrozen", $isFrozen);
        $st->bindParam(":high24hr", $high24hr);
        $st->bindParam(":low24hr", $low24hr);
        $st->bindParam(":name", $name);
        $st->bindParam(":time", $time);
        $st->execute([':last' => $last, ':lowestAsk' => $lowestAsk, ':highestBid' => $highestBid, ':percentChange' => $percentChange, ':baseVolume' => $baseVolume, ':quoteVolume' => $quoteVolume, ':isFrozen' => 0, ':high24hr' => $high24hr, ':low24hr' => $low24hr, ':name' => $name, ':time' => $time]);
    } else {
        print "SLKIPPED (0)\n";
    }
}

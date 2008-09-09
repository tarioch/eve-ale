<?php

header("Content-type: text/plain");
require_once('class.api.php');
require_once('class.quicklook2.php');
require_once('class.marketstat.php');
require_once('class.minerals.php');

$evec = new evec();

$params = array();
$params[(string) 'typeid'] = 34;
//$params[(string) 'typeid'] = 35; should be able to query multiple typeid for marketstats and multiple regionlimits for both. however whenever I try I 500 error from server. 
print "\n\nMarket Orders\n\n";

$xml = $evec->getQuickLook($params);
$a = QuickLook::getQuickLook($xml);

print "\n\nMarketStats\n\n";

$xml = $evec->getMarketStat($params);
$a = MarketStat::getMarketStat($xml);

print "\n\nEvemon Minerals pricelist\n\n";
$xml = $evec->getMinerals();
$a = Minerals::getMinerals($xml);

print_r($a);

?>

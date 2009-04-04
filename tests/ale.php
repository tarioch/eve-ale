<?php
set_time_limit(10);

require 'common.php';

define('ALE_CONFIG_DIR', dirname(__FILE__));

require_once _ROOT_ALE.DS.'factory.php';

$i = 0;
$config = array();
//$api = AleFactory::get('filecache');
$api = AleFactory::get('mysqlcache');
//var_dump($api); die();


try {
	echo '<br><br>',$i++,'.<br>';
	$xml = $api->eve->ErrorList();
	printxml($xml);
	$cached = $api->isFromCache();
	var_dump($cached);
} catch (Exception $e) {
	echo $e->getMessage();
}

try {
	echo '<br><br>',$i++,'.<br>';
	$xml = $api->account->Characters();
	printxml($xml);
	$cached = $api->isFromCache();
	var_dump($cached);
} catch (Exception $e) {
	echo $e->getMessage();
}

try {
	echo '<br><br>',$i++,'.<br>';
	$xml = $api->char->CharacterSheet();
	printxml($xml);
	$cached = $api->isFromCache();
	var_dump($cached);
} catch (Exception $e) {
	echo $e->getMessage();
}

$api->setCredentials('1662448', 'X54VJbI61rVIyCbQJIhH76YtmI7jsQKsXMRIKWTaTgX9KHZnn5hueajIX5ENAHlf', '406769056');

try {
	echo '<br><br>',$i++,'.<br>';
	$xml = $api->account->Characters();
	printxml($xml);
	$cached = $api->isFromCache();
	var_dump($cached);
} catch (Exception $e) {
	echo $e->getMessage();
}

$api->setCredentials('1662448', 'key', '1');

//$api->setConfig('serverError', 'throwException'); //default
try {
	echo '<br><br>',$i++,'.<br>';
	$xml = $api->char->CharacterSheet();
	printxml($xml);
	$cached = $api->isFromCache();
	var_dump($cached);
} catch (Exception $e) {
	echo $e->getMessage();
}

$api->setConfig('serverError', 'returnParsed');
try {
	echo '<br><br>',$i++,'.<br>';
	$xml = $api->char->CharacterSheet();
	printxml($xml);
	$cached = $api->isFromCache();
	var_dump($cached);
} catch (Exception $e) {
	echo $e->getMessage();
}

$api->setConfig('serverError', 'returnParsed');
try {
	echo '<br><br>',$i++,'.<br>';
	error_reporting(E_ERROR);
	$xml = $api->NoFunction();
	printxml($xml);
	$cached = $api->isFromCache();
	var_dump($cached);
} catch (Exception $e) {
	echo $e->getMessage();
}

?>
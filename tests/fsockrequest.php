<?php
require 'common.php';

define('ALE_BASE', dirname(__FILE__).DS.'..'.DS.'ale');

require_once _ROOT_ALE.DS.'request'.DS.'fsock.php';

$request = new AleRequestFsock(array('timeout'=>10));

$result = $request->query('http://localhost/');

echo htmlentities($result);


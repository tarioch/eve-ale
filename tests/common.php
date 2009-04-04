<?php
define ('DS', DIRECTORY_SEPARATOR);
define ('_ROOT_ALE', dirname(dirname(__FILE__)).DS.'ale');
define ('_ROOT_TEST', dirname(__FILE__));

function printxml($xml) {
	if (strtolower(get_class($xml)) == 'aleparserxmlelement') {
		try {
			echo '<pre>'.print_r($xml->toArray(), true).'</pre>';
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	
	if (is_object($xml)) {
		$xml = $xml->asXML();
	}
	echo '<pre>'.htmlentities($xml).'</pre>';
}

?>
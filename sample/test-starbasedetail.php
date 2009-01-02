<?php
/**************************************************************************
	PHP Api Lib
	Copyright (c) 2008 Dustin Tinklin
	Portions Copyright (c) 2008 Thorsten Behrens

	This file is part of PHP Api Lib.

	PHP Api Lib is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	PHP Api Lib is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with PHP Api Lib.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/
require_once('./classes/eveapi/class.api.php');
require_once('./classes/eveapi/class.characters.php');
require_once('./classes/eveapi/class.starbaselist.php');
require_once('./classes/eveapi/class.starbasedetail.php');
require_once('./classes/eveapi/class.starbases.php'); //  Legacy function, for testing purposes only

require_once('./print-as-html.php');
require_once('./config.php');

$api = new Api();
$api->debug(true);
$api->cache(true); // that's the default, done for testing purposes
$api->setTimeTolerance(5); // also the default value
$api->setCredentials($apiuser,$apipass);

$apicharsxml = $api->getCharacters();
$apichars = Characters::getCharacters($apicharsxml);

// Find the character I'm interested in

foreach($apichars as $index => $thischar)
{
	if($thischar['charname']==$mychar)
	{
		$apichar=$thischar['charid'];
	}
}
// Set Credentials
$api->setCredentials($apiuser,$apipass,$apichar);

print("<P>Raw starbase detail output</P>");

$dataxml = $api->getStarbaseList();
$data = StarbaseList::getStarbaseList($dataxml);

if(!empty($data))
{
	$baseid = $data[0]['itemID'];
	$data2xml = $api->getStarbaseDetail($baseid);
	$data2 = StarbaseDetail::getStarbaseDetail($data2xml);
	print_as_html(print_r($data2,TRUE));
} else {
	print('No starbases found<br>');
}

unset ($dataxml,$data,$data2xml,$data2);

$api->printErrors();
?>

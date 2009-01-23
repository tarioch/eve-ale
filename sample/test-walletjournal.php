<?php
/**************************************************************************
	Ale API Library for EvE
	Copyright (c) 2008 Dustin Tinklin
	Portions Copyright (c) 2008 Thorsten Behrens

	This file is part of Ale API Library for EvE.

	Ale API Library for EvE is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Ale API Library for EvE is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with Ale API Library for EvE.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/
require_once('./classes/eveapi/class.api.php');
require_once('./classes/eveapi/class.characters.php');
require_once('./classes/eveapi/class.walletjournal.php');

require_once('./print-as-html.php');
require_once('./config.php');

$api = new Api();
$api->setDebug(true);
$api->setUseCache(true); // that's the default, done for testing purposes
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
		$apicorp=$thischar['corpid'];
	}
}
// Set Credentials
$api->setCredentials($apiuser,$apipass,$apichar);

print("<P>Raw char Wallet Journal output</P>");

$beforeRefID = null;
$i = 1;
do
{
	$dataxml = $api->getWalletJournal($beforeRefID);
	$data = WalletJournal::getWalletJournal($dataxml);

	if(!$data)
	{
		print("<P>Received empty wallet data on run $i</P>");
		break;
	}

	print("<P>This was cached at ".date("r",$api->getCacheTime())." which is in local: ".date("r",$api->getCacheTime(true)));
	print("<P>This will expire at ".date("r",$api->getExpiryTime())." which is in local: ".date("r",$api->getExpiryTime(true)));

	print("<P>Run $i yields the following wallet journal data</P>");
	print_as_html(print_r($data,TRUE));

	$i++;
	// Set the last refID in the array to be the one we're grabbing from next
	$beforeRefID = $data[count($data)-1]['refID'];
} while(count($data) == 1000); // API never returns more than 1000. If it returned 1000, there may be more data

unset ($dataxml,$data);

print("<P>Raw corp Wallet Journal output</P>");

$beforeRefID = null;
$i = 1;
do
{
	$dataxml = $api->getWalletJournal($beforeRefID,TRUE);
	$data = WalletJournal::getWalletJournal($dataxml);

	if(!$data)
	{
		print("<P>Received empty wallet data on run $i</P>");
		break;
	}

	print("<P>This was cached at ".date("r",$api->getCacheTime())." which is in local: ".date("r",$api->getCacheTime(true)));
	print("<P>This will expire at ".date("r",$api->getExpiryTime())." which is in local: ".date("r",$api->getExpiryTime(true)));

	print("<P>Run $i yields the following wallet journal data</P>");
	print_as_html(print_r($data,TRUE));
	$i++;
	// Set the last refID in the array to be the one we're grabbing from next
	$beforeRefID = $data[count($data)-1]['refID'];
} while(count($data) == 1000); // API never returns more than 1000. If it returned 1000, there may be more data

unset ($dataxml,$data);

$api->printErrors();
?>

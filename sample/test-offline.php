<?php
/**************************************************************************
	Ale API Library for EvE
	Copyright (c) 2008 Thorsten Behrens

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
require_once('./classes/eveapi/class.charactersheet.php');

require_once('./print-as-html.php');
require_once('./config.php');

print ("<P>Forcing an offline error, debug on</P>");
$api = new Api();
$api->setDebug(true);
$api->setApiSite("eve-online.com");

$api->getCharacterSheet();

$api->printErrors();
unset ($api);

print ("<P>Forcing an offline error, debug off, testing caching logic</P>");
$api = new Api();
$api->setDebug(false);
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

$api->setApiSite("eve-online.com"); // This will force a 404
$api->getCharacterSheet(); // This may return from cache, hence all the activity above

if ($api->getCacheStatus())
	print("Received data from cache after offline error<BR>");
else
	print("Did NOT receive any data from cache after offline error<BR>");

$api->printErrors();
unset ($api);

// Test timeout offline

print ("<P>Forced offline timeout test</P>");
$api = new Api();
$api->setDebug(true);
$api->setApiSite("localhost");
$api->getCharacterSheet();

$api->printErrors();
unset ($api);
?>
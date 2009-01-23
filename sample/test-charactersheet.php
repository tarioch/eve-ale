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

print ("<P>Raw character sheet output</P>");
$dataxml = $api->getCharacterSheet();
$data = CharacterSheet::getCharacterSheet($dataxml);
print_as_html(print_r($data,TRUE));

unset ($dataxml,$data);

$api->printErrors();
?>
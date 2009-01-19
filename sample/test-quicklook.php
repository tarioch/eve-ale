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
require_once('./classes/eveapi/class.quicklook.php');

require_once('./print-as-html.php');

$api = new Api();
$api->debug(true);
$api->cache(true); // that's the default, done for testing purposes
$api->setTimeTolerance(5); // also the default value

print("<P>Raw EvE-Central Quick Look output</P>");

// QuickLook retrieves market order details for item of typeid, limited by the other arguments
// params required: typeid optional: sethours, regionlimit (multiples), usesystem, setminQ 
$regionlimit[] = 10000002;
$regionlimit[] = 10000052;
print "<P>Market Orders</P>";
$dataxml = $api->getQuickLook(34,null,$regionlimit);
$data = QuickLook::getQuickLook($dataxml);

print_as_html(print_r($data,TRUE));

unset ($dataxml,$data);

$api->printErrors();
?>

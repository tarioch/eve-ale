<?php
/**************************************************************************
	PHP Api Lib
	Copyright (c) 2008 Thorsten Behrens

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
require_once('./classes/eveapi/class.facwarsystems.php');

require_once('./print-as-html.php');

$api = new Api();
$api->debug(true);
$api->cache(true); // that's the default, done for testing purposes
$api->setTimeTolerance(5); // also the default value

print ("<P>Raw factional warfare occupancy map</P>");
$dataxml = $api->getFacWarSystems();
$data = FacWarSystems::getFacWarSystems($dataxml);
print_as_html(print_r($data,TRUE));

unset ($dataxml,$data);

$api->printErrors();
?>
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
require_once('./classes/eveapi/class.marketstat.php');

require_once('./print-as-html.php');

$api = new Api();
$api->debug(true);
$api->cache(true); // that's the default, done for testing purposes
$api->setTimeTolerance(5); // also the default value

print("<P>Raw EvE-Central market statistics output</P>");

// MarketStat returns buy, sell and combine stats for unfulfilled buy sell orders for items as give by typeid and stats scope limited by the optional arguments.
// params required: typeid optional: sethours, setminQ, usesystem, regionlimit (multiples) 
$params = array();
$params[(string) 'typeid'][] = 34;  // multiples added by numberical sub array 
$params[(string) 'typeid'][] = 35; 
$params[(string) 'regionlimit'][] = 10000002;
$params[(string) 'regionlimit'][] = 10000052;
$dataxml = $api->getMarketStat($params);
$data = MarketStat::getMarketStat($dataxml);

print_as_html(print_r($data,TRUE));

unset ($dataxml,$data);

$api->printErrors();
?>
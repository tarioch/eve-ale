<?php
/**************************************************************************
	Ale API Library for EvE Eve Central Minerals Class
	Copyright (c) 2008 Dustin Tinklin

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

// If I were to follow library convention to a T, this class would be
// EvEMon - but I can't bring myself to do it
class Minerals
{
	function getMinerals($contents)
	{
		if (!empty($contents) && is_string($contents))
		{
	       	$output = array();
		// no xml line or initial tags, adding.
		$contents = '<?xml version="1.0" encoding="utf-8" ?>' ."\n" .'<evec_api version="2.0" method="minerals_xml">' ."\n" . $contents .'</evec_api>';
	 		$xml = new SimpleXMLElement($contents);
			foreach ($xml->minerals->children() as $min)
			{
				$index = count($output);
				foreach($min->children() as $key=>$value)
				{
					$output[$index][(string) $key] = (string) $value;
				}
			}
			unset ($xml); // manual garbage collection
			return $output;
		}
		else
		{
			return null;
		}
	}
}
?>

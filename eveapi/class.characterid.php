<?php
/**************************************************************************
	PHP Api Lib CharacterID Class
	Copyright (c) 2008 Dustin Tinklin

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

class CharacterID
{
	function getCharacterID($contents)
	{
		if (!empty($contents) && is_string($contents))
		{
	 	    $output = array();
	 		$xml = new SimpleXMLElement($contents);
			// BUGBUG - CCP returns <row:name/> here instead of <row>, with an xmlns:row="characterID" declaration. Hence, we have to use children() to step through that namespace
			// Once CCP fixes the API, this parser will need to return to "standard" parsing, commented out below, instead of the two lines used now
//			foreach ($xml->result->rowset->row as $row) 
			$rows = $xml->result->rowset->children('characterID'); // Referencing the row namespace by its 'characterID' value
			foreach ($rows as $row)
			{
				$index = count($output);
				foreach ($row->attributes() as $name => $value)
				{
					$output[$index][(string) $name] = (string) $value;
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


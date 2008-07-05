<?php
/**************************************************************************
	PHP Api Lib
	Copyright (C) 2007  Kw4h

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/
class CharSelect
{	
	static function getCharacters($contents)
	{		
		if (!empty($contents) && is_string($contents))
		{
			// create our xml parser
			$characters = array();
			$xml = new SimpleXMLElement($contents);
			
			foreach ($xml->result->rowset->row as $row)
			{
				$index = count($characters);
				$characters[$index]['charname'] = (string) $row['name'];
				$characters[$index]['charid'] = (int) $row['characterID'];
				$characters[$index]['corpname'] = (string) $row['corporationName'];
				$characters[$index]['corpid'] = (int) $row['corporationID'];
			}
			
			return $characters;
		}
		else
		{
			return null;
		}
	}
}

class Characters
{
	static function getCharacters($contents)
	{
		$output = CharSelect::getCharacters($contents);
		
		return $output;
	}
}
?>
<?php
/**************************************************************************
	Ale API Library for EvE CharSelect Class
	Portions Copyright (C) 2007 Kw4h
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

class Characters
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
			unset ($xml); // manual garbage collection			
			return $characters;
		}
		else
		{
			return null;
		}
	}
}
?>
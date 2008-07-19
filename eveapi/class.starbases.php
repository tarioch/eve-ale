<?php
/**************************************************************************
	PHP Api Lib StarbaseList/Details Class
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

class StarbaseList
{	
	static function getStarbaseList($contents)
	{		
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
			
			foreach ($xml->result->rowset->row as $row)
			{
				$index = count($output);
				foreach ($row->attributes() as $key => $val)
				{
					$output[$index][(string) $key] = (string) $val;
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

class StarbaseDetail
{
	static function getStarbaseDetail($contents)
	{
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			
			$output = array();
			
			// get the general settings of the starbase
			$output['generalSettings'] = array();
			foreach ($xml->result->generalSettings->children() as $name => $value)
			{
				$output['generalSettings'][(string) $name] = (string) $value;
			}

			// get the combat settings of the starbase
			$output['combatSettings'] = array();
			foreach ($xml->result->combatSettings->children() as $row)
			{	
				foreach ($row->attributes() as $key => $val)
				{
					$output['combatSettings'][(string) $row->getName()][(string) $key] = (string) $val;
				}
			}
						
			// get the fuel status of the starbase
			$output['fuel'] = array();
			foreach ($xml->result->rowset->row as $row)
			{
				$index = count($output['fuel']);
				foreach ($row->attributes() as $key => $val)
				{
					$output['fuel'][$index][(string) $key] = (string) $val;
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
<?php
/**************************************************************************
	PHP Api Lib Corporation Sheet Class
	Copyright (C) 2008  Rynlam

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

class Corporationsheet 
{
	static function getCorporationSheet($contents) 
	{
		if (!empty($contents) && is_string($contents)) 
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
			foreach ($xml->result->children() as $name => $value) 
			{
				if (((string) $name) != "logo" && ((string) $name) != "rowset") 
				{
				$output[(string) $name] = (string) $value;
				} 
				elseif (((string) $name) == "logo") 
				{
					foreach ($xml->result->logo->children() as $nameb => $valueb)
					{
						$output[(string) $name][(string) $nameb] = (string) $valueb;
					}
				} 
				elseif (((string) $name) == "rowset") 
				{
					foreach ($xml->result->rowset as $rowset) 
					{
						foreach ($rowset->attributes() as $attrn => $attrv) 
						{
							if ($attrn == "name") 
							{ 
								$rsname = (string) $attrv; 
							}
						}
						foreach ($rowset->row as $row) 
						{
							$index = count($output[$rsname]);
							foreach ($row->attributes() as $nameb => $valueb) 
							{
								if ($index < 7) 
								{
									$output[$rsname][$index][(string) $nameb] = (string) $valueb;
								}
							}
						}
					}
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
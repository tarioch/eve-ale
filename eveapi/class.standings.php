<?php
/**************************************************************************
	Ale API Library for EvE Standings Class
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

class Standings
{
	function getToFrom($standing)
	{
		foreach($standing->children() as $tf)
		{
		$tfname = $tf->getName();
			foreach ($tf->rowset as $rowst)
			{
				$rowatts = $rowst->attributes();
				$rowname = $rowatts['name'];
				foreach ($rowst->row as $row)
				{
					$index = count($op[$tfname][(string)$rowname]);
					foreach ($row->attributes() as $name => $value)
					{
						$op[$tfname][(string) $rowname][$index][(string) $name] = (string) $value;
					}
				}	
			}
		}
	return $op;
	}	

	function getStandings($contents)
	{
		if (!empty($contents) && is_string($contents))
		{
	       	$output = array();
	 		$xml = new SimpleXMLElement($contents);
			if($xml->result->allianceStandings->standingsTo)
			{
				$output[(string) 'allianceStandings'] = Standings::getToFrom($xml->result->allianceStandings);
			}
			if($xml->result->corporationStandings->standingsTo)
			{
				$output[(string) 'corporationStandings'] = Standings::getToFrom($xml->result->corporationStandings);
			}
			else
			{
				$output[(string) 'characterStandings']= Standings::getToFrom($xml->result);
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

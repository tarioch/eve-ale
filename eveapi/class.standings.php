<?php
/**************************************************************************
	PHP Api Lib Standings Class
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

class Standings
{
	function getStandings($contents)
	{
	if (!empty($contents) && is_string($contents))
		{
	        	$output = array();
	 		$xml = new SimpleXMLElement($contents);
			if($xml->result->corporationStandings->standingsTo)
			{
			$st = $xml->result->corporationStandings->standingsTo;
			$sf = $xml->result->corporationStandings->standingsFrom;
			}
			else
			{
			$st = $xml->result->standingsTo;
			$sf = $xml->result->standingsFrom;
			}
			foreach ($st->rowset as $rowst)
			{
				$rowatts = $rowst->attributes();
				$rowname = $rowatts['name'];
				foreach ($rowst->row as $arow)
					{
					$aindex = count($output[(string) 'standingsto'][(string)$rowname]);
					foreach ($arow->attributes() as $aname => $avalue)
						{
					$output[(string) 'standingsto'][(string) $rowname][$aindex][(string) $aname] = (string) $avalue;
						}
					}	
				}
			foreach ($sf->rowset as $rowst)	
				{
				$rowatts = $rowst->attributes();
				$rowname = $rowatts['name'];
				foreach ($rowst->row as $arow)
				{
				$aindex = count($output[(string) 'standingsfrom'][(string)$rowname]);
				foreach ($arow->attributes() as $aname => $avalue)
				{
				$output[(string) 'standingsfrom'][(string) $rowname][$aindex][(string) $aname] = (string) $avalue;
				}
				}
			}
			return $output;
		}
	else
		{
			return null;
		}
	}
}

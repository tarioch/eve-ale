<?php


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

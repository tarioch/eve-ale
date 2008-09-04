<?php


class KillLog
{
	function getKillLog($contents)
	{
	if (!empty($contents) && is_string($contents))
		{
	        	$output = array();
	 		$xml = new SimpleXMLElement($contents);
			foreach ($xml->result->rowset->row as $row)
			{
				$index = count($output);
				foreach ($row->attributes() as $name => $value)
				{
				$output[$index][(string) $name] = (string) $value;
				foreach  ($row->victim->attributes() as $name => $value)
					{
					$output[$index]['victim'][(string) $name] = (string) $value;
					}
				foreach ($row->rowset as $srow)
				{
				$rowatts = $srow->attributes();
				$rowname = $rowatts['name'];
				foreach ($srow->row as $arow)
					{
					$aindex = count($output[$index][(string)$rowname]);
					foreach ($arow->attributes() as $aname => $avalue)
						{
					$output[$index][(string) $rowname][$aindex][(string) $aname] = (string) $avalue;
						}
					}	
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

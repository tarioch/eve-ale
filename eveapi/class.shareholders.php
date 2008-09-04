<?php


class ShareHolders
{
	function getShareHolders($contents)
	{
	if (!empty($contents) && is_string($contents))
		{
	        	$output = array();
	 		$xml = new SimpleXMLElement($contents);
			foreach($xml->result->rowset as $rowset)
			{
			$att = $rowset->attributes();
			$type = $att['name'];
			foreach ($rowset->row as $row)
			{
					$index = count($output[$type]);
					foreach ($row->attributes() as $name => $value)
						{
					$output[(string) $type][$index][(string) $name] = (string) $value;
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

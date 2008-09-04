<?php


class ContainerLog
{
	function getContainerLog($contents)
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

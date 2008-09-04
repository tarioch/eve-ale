<?php


class FactionalOccupancy
{
	function getFactionalOccupancy($contents)
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
class FactionalStats
{
	function getFactionalStats($contents)
	{
	if (!empty($contents) && is_string($contents))
		{
	        	$output = array();
	 		$xml = new SimpleXMLElement($contents);
			foreach ($xml->result->row as $row)
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
class FactionalTop100
{
	function getFactionalTop100($contents)
	{
	if (!empty($contents) && is_string($contents))
		{
	        	$output = array();
	 		$xml = new SimpleXMLElement($contents);
			foreach ($xml->result->characters->rowset->row as $row)
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
?>

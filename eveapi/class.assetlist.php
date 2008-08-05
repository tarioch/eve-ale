<?php
/**************************************************************************
	PHP Api Lib AssetList Class
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

class AssetList
{
	function getContents($child)
	{
		print ("Getting dar contents\n");
		print_r($child);
		foreach ($child->rowset->row as $row)
		{
			$index = count($C1);
			foreach ($row->attributes() as $name => $value)
			{
				$C1[$index][(string) $name] = (string) $value;
			}
			if($row->children())
			{
				print("We has the children\n");
				print_r($row->children());
				$C1[$index]["contents"] = AssetList::getContents($row->children());
			}
		}
		return $C1;
	}

	function getAssetList($contents)
	{
		if (!empty($contents) && is_string($contents))
		{
	        $output = array();
	 		$xml = new SimpleXMLElement($contents);

	        // add all accounts in an array
			foreach ($xml->result->rowset->row as $row)
			{
				$index = count($output);
				foreach ($row->attributes() as $name => $value)
				{
				  $output[$index][(string) $name] = (string) $value;
				}
			    if($row->children())
				{
				  $output[$index]["contents"] = AssetList::getContents($row->children());
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

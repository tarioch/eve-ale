<?php
/**************************************************************************
	PHP Api Lib CertificateTree Class
	Copyright (c) 2008 Dustin Tinklin
	Portions Copyright (c) 2008 Thorsten Behrens

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

class CertificateTree
{
	function descendTree($child)
	{
		foreach ($child->rowset as $rs)
		{
			$rsatts = $rs->attributes();
			$rsname = $rsatts["name"];
			foreach ($rs->row as $row)
			{
				$rat = $row->attributes();
				$index++;
				if(count((array)$row->children()) > 0) 
				{
					$clatts = $row->rowset->attributes;
					$clid = $clatts["name"];
					$co[(string) $rsname][$index] = CertificateTree::descendTree($row->children());
				}
				foreach ($row->attributes() as $name => $value)
				{
					$co[(string) $rsname][$index][(string) $name] = (string) $value;
				}
			}
		}
		
		return $co;
	}
	
				
	static function getCertificateTree($contents)
	{		
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
			foreach ($xml->result->rowset as $rs)
			{
				$rsatts = $rs->attributes();
				$rsname = $rsatts[(string) "name"];
				foreach ($rs->row as $row)
				{
					$rat = $row->attributes();
					$index++;
					if(count((array)$row->children()) > 0) 
					{
						$output[(string) $rsname][$index]  = CertificateTree::descendtree($row->children());
					}
					foreach ($row->attributes() as $name => $value)
					{
						$output[(string) $rsname][$index][(string) $name] = (string) $value;
					}
				}
			}

			unset ($xml); // manual garbage collection			
			return ($output);
		}
		else
		{
			return null;
		}
	}
}
?>
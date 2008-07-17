<?php
/**************************************************************************
	PHP Api Lib
	Copyright (C) 2007  Kw4h

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
class MemberTrack
{	
	static function getMembers($contents)
	{		
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
			
			foreach ($xml->result->rowset->row as $row)
			{
				$index = count($output);
				foreach ($row->attributes() as $key => $val)
				{
					$output[$index][(string) $key] = (string) $val;
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

class MemberTracking
{
	static function getMemberTracking($contents)
	{
		$output = MemberTrack::getMembers($contents);
		
		return $output;
	}
}
?>
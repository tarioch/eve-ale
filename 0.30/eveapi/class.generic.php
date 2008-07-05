<?php
/**************************************************************************
	PHP Api Lib
	Copyright (C) 2007  Kw4h
	Parts Copyright (c) 2008 Yorick Downe's player

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
class Generic
{
	static function getSkillTree($contents)
	{		
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
			
			// categories
			foreach ($xml->result->rowset->row as $row)
			{
				$output[(string) $row['groupName']]['groupID'] = (int) $row['groupID'];
				$output[(string) $row['groupName']]['skills'] = array();
				
				// get all skills
				foreach ($row->rowset->row as $row2)
				{
					$index = count($output[(string) $row['groupName']]['skills']);
					$output[(string) $row['groupName']]['skills'][$index]['requiredSkills'] = array();
					$output[(string) $row['groupName']]['skills'][$index]['requiredAttributes'] = array();
					
					// foreach attribute of the skill
					foreach ($row2->attributes() as $key => $val)
					{
						$output[(string) $row['groupName']]['skills'][$index][(string) $key] = (string) $val;
					}
					
					// all the subitems, except 'rowset' & ' requiredAttributes'
					foreach ($row2->children() as $key => $val)
					{
						if ((string) $key != "requiredAttributes" && (string) $key != "rowset")
						{
							$output[(string) $row['groupName']]['skills'][$index][(string) $key] = (string) $val;
						}
					}
					
					// get all the required skills
					foreach ($row2->rowset->row as $row3)
					{
						$index2 = count($output[(string) $row['groupName']]['skills'][$index]['requiredSkills']);
						
						// attributes
						foreach ($row3->attributes() as $key => $val)
						{
							$output[(string) $row['groupName']]['skills'][$index]['requiredSkills'][$index2][(string) $key] = (string) $val;
						}
					}
					
					// get all required attributes
					foreach ($row2->requiredAttributes->children() as $key => $val)
					{
						$output[(string) $row['groupName']]['skills'][$index]['requiredAttributes'][(string) $key] = (string) $val;
					}
				}
				
			}
			
			return ($output);
		}
		else
		{
			return null;
		}
	}
	
	static function getRefTypes($contents)
	{		
		if (!empty($contents) && is_string($contents))
		{
			$xml = new SimpleXMLElement($contents);
			$output = array();
			
			foreach ($xml->result->rowset->row as $row)
			{
				$output[(int) $row['refTypeID']] = (string) $row['refTypeName'];
			}
			
			return $output;
		}
		else
		{
			return null;
		}
	}
}

// Classes to bring getSkillTree and getRefTypes in line with how all other parsers work

class SkillTree
{
	static function getSkillTree($contents)
	{
		$output = Generic::getSkillTree($contents);
		
		return $output;
	}
}

class RefTypes
{
	static function getRefTypes($contents)
	{
		$output = Generic::getRefTypes($contents);
		
		return $output;
	}
}

?>
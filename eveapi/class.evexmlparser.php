<?php
/**************************************************************************
	PHP Api Lib EvEXMLParser Class
	Portions Copyright (C) 2007 Kw4h
	Portions Copyright (c) 2008 Thorsten Behrens
	Portions Copyright (c) 2008 Dustin Tinklin

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

/*
An EvE API XML result may contain the following elements:
o eveapi with a version attribute. If this is not present, return an error / throw an exception
o currentTime and cachedUntil, given in GMT (EvE Time), CCP's caching hints
o result, which contains the actual data as children
o A rowset with rows, possibly nested. The rowset has these attributes:
   name (always)
   key (assume optional)
   columns (always, comma-separated)
o rows with attributes. The attributes correspond to the colums that the rowset defined. Rows do not have values. They may have further children, however - nested rowsets.
o Regular children with values, possibiy nested. Used nested in characterSheet, for example.
o Regular children with attributes. These have not been seen nested "in the wild". Also not seen with values, only with attributes. Used in StarbaseDetail. 
o This parser assumes that a rowset has only row children, no other children
o This parser assumes that rows and rowsets have no values, only attributes. Rows may have further rowset children, however.
o This parser assumes that a child with attributes has no value, only attributes
*/

class EvEXMLParser
{	
    /*
	XMLtoArray() will parse an XML "string" and return an associative, multi-dimensional array. Specifically, it will parse the result elements in an EvEAPI XML return like this:
	o Children's names immediately "inside" <result> become array indices, with the value being the value of the child. 
		E.g., "<gender>Male</gender>" becomes $output['gender'] = 'Male';
           o If a child has further nested children, the array index will be child1.child2, and the value will be the one of the "inside" child. To be fancy, I will call this "pseudo E4X syntax".
		E.g., "<attributeEnhancers><memoryBonus><augmentatorName>Memory Augmentation - Standard</augmentatorName>[...]" becomes $output['attributeEnhancers.memoryBonus.augmentorName'] = 'Memory Augmentation - Standard';
	o If a child has no value, but has attributes, that child is translated into an array. The index into the $output array is the child's name, and the indices of its array are the names of the attributes. Note this may occur with nested children.
		E.g., "<combatSettings><onStandingDrop standing="10" />" becomes $output['combatSettings.onStandingDrop']['standing'] = '10';
	o Rowsets are translated into two-dimensional arrays. The index into the $output array is the value of the "name" attribute of the rowset.
		E.g., "<rowset name="assets" key="itemID" columns="itemID,locationID,typeID,quantity,flag,singleton">" becomes $output['assets'] = array();
           o Rows become array elements of the rowset array. Their index into the rowset array is the value of the attribute corresponding to the "key" attribute of the rowset, or a straight numeric index if there is no "key" attribute to the rowset. Their attributes become indices with values.
		   E.g., assuming the above rowset, "<row itemID="133628911" locationID="60002392" typeID="25593" quantity="69" flag="4" singleton="0" />" becomes $output['assets']['133628911']['itemID'] = '133628911'; &c for locationID, typeID, quantity, flag and singleton.
	o If a row contains further rowset children, these are added into the row array, following the same parsing rules as above.
		E.g., again assuming the above rowset setup, "<row itemID="166536499" locationID="60015111" typeID="607" quantity="1" flag="4" singleton="1"> <rowset name="contents" key="itemID" columns="itemID,typeID,quantity,flag,singleton">" will become the same
		type of row array as just above, with an additional [contents] element, which in turn contains further row elements. 
*/
	static function XMLtoArray($xmlpage)
	{		
		if (!empty($xmlpage) && is_string($xmlpage))
		{
			$xml = new SimpleXMLElement($contents);
			
			$output = array();
			foreach ($xml->result->rowset as $rs)
			{
				$rsatts = $rs->attributes();
				$rsname = $rsatts[(string) "name"];
				foreach ($rs->row as $row)
				{
					$index = count($output[(string) $rsname]);
					foreach ($row->attributes() as $name => $value)
					{
						$output[(string) $rsname][$index][(string) $name] = (string) $value;
					}
				}
			}
			return $output;
		}
		else
		{
			return FALSE;
		}
	}
	
?>

<?php
/**************************************************************************
	Ale API Library for EvE CharSelect Class legacy include file and CharSelect Class def
	Portions Copyright (C) 2007 Kw4h
	Portions Copyright (c) 2008 Thorsten Behrens

	This file is part of Ale API Library for EvE.

	Ale API Library for EvE is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Ale API Library for EvE is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with Ale API Library for EvE.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/
// class.charselect.php was renamed to be in line with new naming conventions - this file allows for legacy code to continue working
require_once(dirname(__FILE__).'/class.characters.php'); 

class CharSelect
{
	static function getCharacters($contents)
	{		
		$output = Characters::getCharacters($contents);
		
		return $output;
	}
}
?>
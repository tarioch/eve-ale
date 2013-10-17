<?php
/**
 * @version $Id$
 * @license GNU/LGPL, see COPYING and COPYING.LESSER
 * This file is part of Ale - PHP API Library for EVE.
 * 
 * Ale is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Ale is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with Ale.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Ale\Exception;

use \RuntimeException;

class EveException extends RuntimeException {
	private $_cachedUntil;
	
	function __construct($message, $code, $cachedUntil) {
		parent::__construct($message, $code);
		$this->_cachedUntil = $cachedUntil;
	}
	
	function getCachedUntil() {
		return $this->_cachedUntil;
	}
	
}

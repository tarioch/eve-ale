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

namespace Ale\Cache;

use Ale\Exception\CacheException;

class MySQL extends AbstractDb {
	protected $nameQuote = '`';

	public function __construct(array $config = array()) {
		parent::__construct($config);
		if (isset($config['db']) && is_resource($config['db'])) {
			$this->db = $config['db'];
		} else {
			$config['host'] = $this->getWithDefault($config, 'host', null);
			$config['user'] = $this->getWithDefault($config, 'user', null);
			$config['password'] = $this->getWithDefault($config, 'password', null);
			$config['new_link'] = (bool) $this->getWithDefault($config, 'new_link', false);
			$config['client_flags'] = $this->getWithDefault($config, 'client_flags', 0);
			if ($this->getWithDefault($config, 'persistent')) {
				$this->db = mysql_pconnect($config['host'], $config['user'], $config['password'], $config['client_flags']);
			} else {
				$this->db = mysql_connect($config['host'], $config['user'], $config['password'], $config['new_link'], $config['client_flags']);
			}
			if ($this->db == false) {
				throw new CacheException(mysql_error(), mysql_errno());
			}
			if (isset($config['database'])) {
				$result = mysql_select_db($config['database'], $this->db);
				if ($result === false) {
					throw new CacheException(mysql_error($this->db), mysql_errno($this->db));
				}
			}
		}
	}

	protected function escape($string) {
		return mysql_real_escape_string($string);
	}

	protected function &execute($query) {
		$result = mysql_query($query, $this->db);
		if ($result === false) {
			throw new CacheException(mysql_error($this->db), mysql_errno($this->db));
		}
		return $result;
	}

	protected function &fetchRow(&$result) {
		$row = mysql_fetch_assoc($result);
		return $row;
	}

	protected function freeResult(&$result) {
		mysql_free_result($result);
	}

}

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

class PgSQL extends AbstractDb {

	public function __construct(array $config = array()) {
		parent::__construct($config);
		if (isset($config['db']) && is_resource($config['db'])) {
			$this->db = $config['db'];
		} else {
			$config['host'] = $this->getWithDefault($config, 'host', null);
                        $config['port'] = $this->getWithDefault($config, 'port', null);
                        $config['database'] = $this->getWithDefault($config, 'database', null);
			$config['user'] = $this->getWithDefault($config, 'user', null);
			$config['password'] = $this->getWithDefault($config, 'password', null);
			$config['new_link'] = (bool) $this->getWithDefault($config, 'new_link', false);

                        $connection_string = "host='".$config['host']."' ".
                                             "port='".$config['port']."' ".
                                             "dbname='".$config['database']."' ".
                                             "user='".$config['user']."' ".
                                             "password='".$config['password']."' ";

			if ($this->getWithDefault($config, 'persistent')) {
				$this->db = pg_pconnect($connection_string, $config['new_link']);
			} else {
				$this->db = pg_connect($connection_string, $config['new_link']);
			}

			if ($this->db == false) {
				throw new CacheException(pg_last_error(), pg_connection_status());
			}
		}
	}

	protected function escape($string) {
		return pg_escape_string($string);
	}

	protected function &execute($query) {
		$result = pg_query($this->db, $query);
		if ($result === false) {
			throw new CacheException(pg_last_error($this->db), pg_result_status($result));
		}
		return $result;
	}

	protected function &fetchRow(&$result) {
		$row = pg_fetch_assoc($result);
		return $row;
	}

	protected function freeResult(&$result) {
		pg_free_result($result);
	}

}

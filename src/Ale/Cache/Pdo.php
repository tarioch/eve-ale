<?php
/**
 * @version $Id: pdo.php 5 2011-10-01 15:54:13Z kovalikp@gmail.com $
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

use \PDO;

class Pdo implements Cache {
	/** var PDO */
	protected $db;
	protected $dsn;
	protected $table;
	protected $host;
	protected $path;
	protected $paramsRaw;
	protected $params;
	protected $maxDataSize;
	protected $row;

	public function __construct(array $config = array()) {
		$this->table = $this->getWithDefault($config, 'table', 'alecache');
		$this->maxDataSize = $this->getWithDefault($config, 'maxDataSize', null);
		if (isset($config['db']) &&  ($config['db'] instanceof PDO)) {
			$this->db = $config['db'];
		} else {
			$config['dsn'] = $this->getWithDefault($config, 'dsn', null);
			$config['user'] = $this->getWithDefault($config, 'user', null);
			$config['password'] = $this->getWithDefault($config, 'password', null);
			$options = array();
			$this->db = new PDO($config['dsn'], $config['user'], $config['password'], $options);
		}
	}


	/**
	 * Set host URL
	 *
	 * @param string $host
	 */
	public function setHost($host) {
		$this->host = $host;
	}

	/**
	 * Set call parameters
	 *
	 * @param string $path
	 * @param array $params
	 */
	public function setCall($path, array $params = array()) {
		$this->path = $path;
		$this->paramsRaw = $params;
		$this->params = sha1(http_build_query($params, '', '&'));

		$sql = "SELECT * FROM ".$this->table." WHERE host = :host AND path = :path AND params = :params";
		$query = $this->db->prepare($sql);
		$query->bindValue(":host", $this->host);
		$query->bindValue(":path", $this->path);
		$query->bindValue(":params", $this->params);
		$query->execute();
		$this->row = $query->fetchObject();
	}

	/**
	 * Store content
	 *
	 * @param string $content
	 * @param string $cachedUntil
	 * @return null
	 */
	public function store($content, $cachedUntil) {
		if ($this->maxDataSize && strlen($content) > $this->maxDataSize) {
			return;
		}
		if ($this->row) {
			$this->row->content = $content;
			$this->row->cachedUntil = $cachedUntil;
			$sql = "UPDATE alecache SET content = :content, cachedUntil = :cachedUntil WHERE host = :host AND path = :path AND params = :params;";
			$query = $this->db->prepare($sql);
			foreach (get_object_vars($this->row) as $key => $value) {
				$query->bindValue(":".$key, $value);
			}
			$query->execute();
			
		} else {
			$this->row = new stdClass();
			$this->row->content = $content;
			$this->row->cachedUntil = $cachedUntil;
			foreach (array('host', 'path', 'params') as $field) {
				$this->row->$field = $this->$field;
			}
			$sql = "INSERT INTO alecache (host, path, params, content, cachedUntil) VALUES (:host, :path, :params, :content, :cachedUntil);";
			$query = $this->db->prepare($sql);
			foreach (get_object_vars($this->row) as $key => $value) {
				$query->bindValue(":".$key, $value);
			}
			$query->execute();
		}
	}


	/**
	 * Update cachedUntil value of recent call
	 *
	 * @param string $time
	 */
	public function updateCachedUntil($time) {
		if ($this->row) {
			$this->row->cachedUntil = $time;
			$cachedUntil = $time ? $time : null;
			$sql = 'UPDATE '.$this->table.' SET cachedUntil = :cachedUntil WHERE host = :host AND path = :path AND params = :params';
			$query = $this->db->prepare($sql);
			$query->bindValue(":host", $this->host);
			$query->bindValue(":path", $this->path);
			$query->bindValue(":params", $this->params);
			$query->bindValue(":cachedUntil", $cachedUntil);
			$query->execute();
		}

	}

	/**
	 * Retrieve content as string
	 *
	 */
	public function retrieve() {
		if ($this->row) {
			return $this->row->content;
		}
		return null;
	}

	/**
	 * Check if target is stored
	 *
	 * @return int|null
	 */
	public function isCached() {
		if ($this->row == false) {
			return ALE_CACHE_MISSING;
		}

		$tz = new DateTimeZone('UTC');
		$now = new DateTime(null, $tz);
		$cachedUntil = new DateTime($this->row->cachedUntil, $tz);

		if ((int) $cachedUntil->format('U') < (int) $now->format('U')) {
			return ALE_CACHE_EXPIRED;
		}

		return ALE_CACHE_CACHED;
	}

	/**
	 * Remove old data from cache
	 *
	 * @param bool $all
	 */
	public function purge($all = false) {
		if ($all) {
			$sql = "DELETE FROM ".$this->table." WHERE host = :host";
			$query = $this->db->prepare($sql);
			$query->bindValue(":host", $this->host);
		} else {
			$tz = new DateTimeZone('UTC');
			$now = new DateTime(null, $tz);
			$sql = "DELETE FROM ".$this->table." WHERE host = :host AND cachedUntil < :cachedUntil ";
			$query = $this->db->prepare($sql);
			$query->bindValue(":host", $this->host);
			$query->bindValue(":cachedUntil", $now->format('Y-m-d H:i:s'));
		}
		$query->execute();
	}

}

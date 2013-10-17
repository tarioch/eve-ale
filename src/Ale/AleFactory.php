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

namespace Ale;

if (!defined('ALE_CONFIG_DIR')) {
	define('ALE_CONFIG_DIR', __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..');
}

class AleFactory {
	/**
	 * Instances of Ale classes
	 *
	 * @var array
	 */
	private static $instances = array();
	
	/**
	 * Get value from array if exists, or return default
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	private static function _default(&$array, $key, $default) {
		return isset($array[$key]) ? $array[$key] : $default;
	}
	
	/**
	 * Initialise new instance of Ale class
	 *
	 * @param string $name
	 * @param array $config
	 */
	private static function init($name, $config) {
		$_name = strtolower($name);
		$configfile = self::_default($config, 'config', ALE_CONFIG_DIR.DIRECTORY_SEPARATOR.$_name.'.ini');
		if ($configfile !== false) {
			if (file_exists($configfile)) {
				$tmp = parse_ini_file($configfile, true);
			} else {
				throw new LogicException(sprintf("Configuration file '%s' not found", $configfile));
			}
			if ($tmp === false) {
				throw new LogicException(sprintf("Could not parse configuration file '%s'", $configfile));
			}
		} else {
			$tmp = array();
		}
		
		$mainConfig 	= self::_default($tmp, 'main', array());
		$cacheConfig 	= self::_default($tmp, 'cache', array());
		$requestConfig 	= self::_default($tmp, 'request', array());
		
		foreach($config as $key => $value) {
			$split = explode('.', $key, 2);
			if (count($split) == 2) {
				if ($split[0] == 'main' || $split[0] == 'cache' || $split[0] == 'request') {
					$key = $split[0];
					$value = array($split[1] => $value);
				}
				if ($key == 'main' && is_array($value)) {
					foreach ($value as $k => $v) {
						$mainConfig[$k] = $v;
					}
				} elseif ($key == 'cache' && is_array($value)) {
					foreach ($value as $k => $v) {
						$cacheConfig[$k] = $v;
					}
				} elseif ($key == 'request' && is_array($value)) {
					foreach ($value as $k => $v) {
						$requestConfig[$k] = $v;
					}
				} else {
					$mainConfig[$key] = $value;
				}
			} else {
				$mainConfig[$key] = $value;
			}
		}
		
		$mainName 	= self::_default($mainConfig, 'class', $name);
		$cacheName 	= self::_default($cacheConfig, 'class', 'Dummy');
		$requestName 	= self::_default($requestConfig, 'class', 'Curl');
		
		$mainClass 	= 'Ale\\'.$mainName;
		$cacheClass 	= 'Ale\\Cache\\'.$cacheName;
		$requestClass 	= 'Ale\\Request\\'.$requestName;
		
		$request 		= new $requestClass($requestConfig);
		$cache 			= new $cacheClass($cacheConfig); 
		$main 			= new $mainClass($request, $cache, $mainConfig);

		self::$instances[$_name] = $main;
		
	}
	
	/**
	 * Loads configuration file and returns instance of Ale class
	 * If object already exists and no new config is provided,
	 * method returns old instance
	 *
	 * @param string $name file name
	 * @param array $config
	 * @return Base Base object or its descendant
	 */
	public static function get($name, array $config = array(), $newInstance = false) {
		$_name = strtolower($name);
		if ($newInstance || !isset(self::$instances[$_name])) {
			self::init($name, $config);
		}
		return self::$instances[$_name];
	}
	
	/**
	 * Loads configuration file and returns instance of EveOnline class
	 *
	 * @param array $config
	 * @return EveOnline
	 */
	public static function getEveOnline(array $config = array(), $newInstance = false) {
		return self::get('EveOnline', $config, $newInstance);
	}
	
	/**
	 * Loads configuration file and returns instance of EveCentral class
	 *
	 * @param array $config
	 * @return EveCentral
	 */
	public static function getEveCentral(array $config = array(), $newInstance = false) {
		return self::get('EveCentral', $config, $newInstance);
	}
	
}

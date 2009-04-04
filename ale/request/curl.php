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

defined('ALE_BASE') or die('Restricted access');

require_once ALE_BASE.DIRECTORY_SEPARATOR.'interface'.DIRECTORY_SEPARATOR.'request.php';

require_once ALE_BASE.DIRECTORY_SEPARATOR.'exception'.DIRECTORY_SEPARATOR.'request.php';


class AleRequestCurl implements AleInterfaceRequest  {
	protected $config = array();
	
	/**
	 * Constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		if (!function_exists('curl_init')) {
			throw new LogicException('Curl extension is missing');
		}
		$this->config['timeout'] = isset($config['timeout']) ? (int) $config['timeout'] : 30;
	}
	
	/**
	 * Read response header
	 * Throws exception on 4** and 5** responses
	 *
	 * @param resource $ch
	 * @param string $header
	 * @return int
	 */
	protected function readHeader($ch, $header) {
		$matches = array();
		if (!preg_match('#^HTTP/[0-9]\\.[0-9] +([0-9]+) +(.*)$#', $header, $matches)) {
			return strlen($header);
		}
		if ($matches[1] >= 400) {
			curl_close($ch);
			throw new AleExceptionRequest('Server Response Error::'. $matches[2], $matches[1]);
		}
		return strlen($header);
	}
	
	/**
	 * Fetch respone from target URL
	 *
	 * @param string $url
	 * @param array $params
	 */
	public function query($url, array $params = null) {
		//curl magic
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout']);
		if ($params) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'readHeader'));
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		$contents = trim(@curl_exec($ch));
		
		//chceck for connection errors
		$errno = curl_errno($ch);
		if ($errno > 0) {
			$errstr = curl_error($ch);
			//TODO: API exception
			curl_close ($ch);
			throw new AleExceptionRequest($errstr, $errno);
		}
		
		curl_close ($ch);
		
		return $contents;
	}

}
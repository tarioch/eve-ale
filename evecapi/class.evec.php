<?php
/**************************************************************************
	eve central hacked from the PHP Api Lib, v0.22, 2008-07-19

	Portions Copyright (C) 2007  Kw4h
	Portions Copyright (C) 2008 Pavol Kovalik
	Portions Copyright (C) 2008 Gordon Pettey
	Portions Copyright (C) 2008 Thorsten Behrens
	Portions Copyright (C) 2008 Dustin Tinklin

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

class evec
{
	private $apisite = "eve-central.com";
	private $cachedir = './xmlcache/evec';
	public $debug = false;
	private $msg = array();
	private $usecache = false;
	private $timetolerance = 5; // minutes to wait after cachedUntil, to allow for the server's time being fast

	
	public function debug($bool)
	{
		if (is_bool($bool))
		{
			$this->debug = $bool;
			return true;
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error","debug: parameter must be present and boolean");
			}
			return false;
		}
	}
	
	public function cache($bool)
	{
		if (is_bool($bool))
		{
			$this->usecache = $bool;
			return true;
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error","cache: parameter must be present and boolean");
			}
			return false;
		}
	}

	public function setCacheDir($dir)
	{
		if (is_string($dir))
		{
			$this->cachedir = $dir;
			return true;
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error","setCacheDir: parameter must be present and a string");
			}
			return false;
		}
	}
	
	public function setTimeTolerance($tolerance)
	{
		if (is_int($tolerance))
		{
			$this->timetolerance = $tolerance;
			return true;
		} else {
			if ($this->debug)
				$this->addMsg("Error","setTimeTolerance: parameter must be present and an integer");
			return false;
		}

	}
	
	// add error message - both params are strings and are formatted as: "$type: $message"
	private function addMsg($type, $message)
	{
		if (!empty($type) && !empty($message))
		{
			$index = count($this->msg);
			
			$this->msg[$index]['type'] = $type;
			$this->msg[$index]['msg'] = $message;
			return 1;
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error","addMsg: type and message must not be empty");
			}
			return 0;
		}
	}
	
	/**********************
		Retrieve an XML File
		$path	path relative to the $apisite url
		$timeout	amount of time to keep the cached data before re-requesting it from the API, in minutes
		$cachePath	optional array of string values . These can be indizes into $params, or arbitrary strings, 
				and will be used to build the relative path to the cache file
		$params	optional array of paramaters (exclude apikey and userid, and charid)
				$params['characterID'] = 123456789;
	***********************/
	public function retrieveXml($path, $timeout = null, $cachePath = null, $params = null)
	{
		if ($cachePath != null && !is_array($cachePath))
		{			
			if ($this->debug)
			{
				$this->addMsg("Error","retrieveXml: Non-array value of cachePath param, reverting to default value");
			}
			$cachePath = null;
		}
		
		if ($params != null && !is_array($params))
		{			
			if ($this->debug)
			{
				$this->addMsg("Error","retrieveXml: Non-array value of params param, reverting to default value");
			}
			$params = null;
		}

		if (!empty($path))
		{
			if (!is_array($params))
			{
				$params = array();
			}

						
			// continue when not cached
			if (!$this->usecache || !$this->isCached($path, $params, $cachePath, $timeout))
			{
				// Presumably, if it's not set to '&', they might have had a reason for that - be a good citizen
				$sep = ini_get('arg_separator.output');
				// Necessary so that http_build_query does not spaz and give us '&amp;' as a separator on certain hosting providers
				ini_set('arg_separator.output','&');
				// poststring
				if (count($params) > 0)
					$poststring = http_build_query($params); // which has been forced to use '&' by ini_set, at the end of this file
				else
					$poststring = "";
				// And set it back to whatever sensical or non-sensical value it was in the 1st place
				ini_set('arg_separator.output','&');
				
				// open connection to the api
				// Note some free PHP5 servers block fsockopen() - in that case, find a different hosting provider, please
				$fp = fsockopen($this->apisite, 80, $errno, $errstr, 30);

				if (!$fp)
				{
					if ($this->debug)
						$this->addMsg("Error", "retrieveXml: Could not connect to API URL at $this->apisite, error $errstr ($errno)");
				}
				else
				{
					// request the xml
					fputs ($fp, "POST " . $path . " HTTP/1.0\r\n");
					fputs ($fp, "HOST: " . $this->apisite . "\r\n");
					fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
					fputs ($fp, "User-Agent: PHPApi\r\n");
					fputs ($fp, "Content-Length: " . strlen($poststring) . "\r\n");
					fputs ($fp, "Connection: close\r\n\r\n");
					if (strlen($poststring) > 0)
						fputs ($fp, $poststring."\r\n");
					
					// retrieve contents
					$contents = "";
					while (!feof($fp))
					{
						$contents .= fgets($fp);
					}
					
					// close connection
					fclose($fp);
					
					$start = strpos($contents, "\r\n\r\n");
					if ($start !== FALSE)
					{
						$contents = substr($contents, $start + strlen("\r\n\r\n"));
						
						// check if there's an error or not
						$xml = new SimpleXMLElement($contents);
						
						$error = (string) $xml->error;
						if (!empty($error))
						{
							if ($this->debug)
							{
								$this->addMsg("Api", $error);
							}
							
							if ($this->isCached($path, $params, $cachePath, $timeout))
							{
								return $this->loadCache($path, $params, $cachePath);
							}
							
							return null;
						}
						
						unset ($xml); // reduce memory footprint

						if (!$this->isCached($path, $params, $cachePath, $timeout))
						{
							$this->store($contents, $path, $params, $cachePath);
						}
						
						return $contents;
					}
					
					if ($this->debug)
					{
						$this->addMsg("Error", "retrieveXml: Could not parse contents");
					}
					
					return null;
				}
			}
			else
			{
				return $this->loadCache($path, $params, $cachePath);
			}
		}
		elseif ($this->debug)
		{
			$this->addMsg("Error", "retrieveXml: path is empty");
		}
		
		return null;
	}
	
	private function getCacheFile($path, $params, $cachePath)
	{
		$realpath = $this->cachedir;
		
		if ($cachePath != null)
		{
			foreach ($cachePath as $segment)
			{
				if (isset($params[$segment]))
				{
					$realpath .= '/'.$params[$segment];
				}
				else
				{
					$realpath .= '/'.$segment;
				}
			}
		}
		
		$realpath .= $path;
				
		return $realpath;
	}
	
	private function store($contents, $path, $params, $cachePath)
	{
		$file = $this->getCacheFile($path, $params, $cachePath);

		if (!file_exists(dirname($file)))
		{
			mkdir(dirname($file), 0777, true);
		}
		
		$fp = fopen($file, "w");
		
		if ($fp)
		{
			fwrite($fp, $contents);
			fclose($fp);
			
			if ($this->debug)
			{
				$this->addMsg("Info","store: Created cache file:" . $file);
			}
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error", "store: Could not open cache file for writing: " . $file);
			}
		}
		
	}
	
	private function loadCache($path, $params, $cachePath)
	{
		// its cached, open it and use it
		$file = $this->getCacheFile($path, $params, $cachePath);
		
		$fp = fopen($file, "r");
		if ($fp)
		{
			$contents = fread($fp, filesize($file));
			fclose($fp);

			if ($this->debug)
			{
				$this->addMsg("Info","loadCache: Fetched cache file:" . $file);
			}
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Error", "loadCache: Could not open cache file for reading: " . $file);
			}
		}

		return $contents;
	}
	
	// checking if the cache expired or not based on TQ time
	private function isCached($path, $params, $cachePath, $timeout)
		{
		$file = $this->getCacheFile($path, $params, $cachePath);

		if (file_exists($file))
		{
			$fp = fopen($file, "r");
			
			if ($fp)
			{
				$contents = fread($fp, filesize($file));
				fclose($fp);
				
				// check cache
				$xml = new SimpleXMLElement($contents);
				
				$cachetime = (string) $xml->currentTime;
				$time = strtotime($cachetime);
				
				$expirytime = (string) $xml->cachedUntil;
				$until = strtotime($expirytime);
				
				unset($contents); // Free us some memory
				unset($xml); // and free memory for this one, too

				// get GMT time
				$timenow = time();
				$now = $timenow - date('Z', $timenow);

// Uncomment in case we need some deep-dive debug. There's a TODO here - have levels of debug				
//				if ($this->debug) {
//				   $this->addMsg("Info","Got this at ".$time.", keep it until ".$until.", it is now ".$now);
//				   $this->addMsg("Info","Formatted: Got this at ".strftime("%b %d %Y %X",$time).", keep it until ".strftime("%b %d %Y %X",$until).", it is now ".strftime("%b %d %Y %X",$now));
//				}

				if (!$timeout) // no explicit timeout given, use the cachedUntil time CCP gave us
				{
					if (($until + $this->timetolerance * 60) < $now) // time to fetch again, with some minutes leeway
						return false;
				} else {
					// if now is $timeout minutes ahead of the cached time, pretend this file is not cached
					$minutes = ($timeout + $this->timetolerance) * 60;
					if ($now >= $time + $minutes)
						return false;
				}

				return true; // default fall-through - cache is still valid
			}
			else
			{
				if ($this->debug)
				{
					$this->addMsg("Error", "isCached: Could not open cache file for reading: " . $file);
				}
				return false;
			}
		}
		else
		{
			if ($this->debug)
			{
				$this->addMsg("Info", "isCached: Cache file does not (yet?) exist: " . $file);
			}
			return false;
		}
	}
	
	public function printErrors()
	{
		foreach ($this->msg as $msg)
		{
			echo ("<b>" . $msg['type'] . "</b>: " . $msg['msg'] . "</br>\n");
		}
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Functions to retrieve data
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


	public function getMinerals($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getMinerals: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}
		$contents = $this->retrieveXml("/api/evemon", $timeout);
		
		return $contents;
	}

	public function getQuickLook($params = array(),$timeout = null)
	{
		if (empty($params) or empty($params[(string) 'typeid']))
		{	
				$this->addMsg("Error","getQuickLook: typeid is a required element of $params");
				return null;
			}
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getQuickLook: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}
		$contents = $this->retrieveXml("/api/quicklook", $timeout, null, $params);
		
		return $contents;
	}


	public function getMarketStat($params = array(),$timeout = null)
	{
		if (empty($params) or empty($params[(string) 'typeid']))
			{
				$this->addMsg("Error","getMarketStat: typeid is a required element of $params");
				return null;
			}
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
			{
				$this->addMsg("Error","getMarketStat: Non-numeric value of timeout param, reverting to default value");
			}
			$timeout = null;
		}
		$contents = $this->retrieveXml("/api/marketstat", $timeout, null, $params);
		
		return $contents;
	}


}
?>

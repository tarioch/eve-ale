<?php
/**************************************************************************
	PHP Api Lib, v0.23, 2009-01-10

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

class Api
{
	private $apikey = null;
	private $userid = null;
	private $charid = null;
	private $apisite = "api.eve-online.com";
	private $apisiteevec = "eve-central.com";
	private $useragent = "eve-apiphp-0.23";
	private $cachedir = './xmlcache';
	private $debug = false;
	private $msg = array();
	private $usecache = true;
	private $cachestatus = false;
	private $apierror = 0; // API Error code, if any
	private $cachehint = true; // A kludge to handle data without cachedUntil hint. Again, inheritance would make this go away 
	private $timetolerance = 5; // minutes to wait after cachedUntil, to allow for the server's time being fast

	public function setCredentials($userid, $apikey, $charid = null)
	{
		// Allow wiping of credentials by passing "null" in
		if ($userid === null && $apikey === null)
		{
			$this->userid = null;
			return true;
		}
		if ($charid === null)
			$this->charid = null;

		if (empty($userid) || empty ($apikey))
		{
			if ($this->debug)
				$this->addMsg("Error","setCredentials: userid and apikey must not be empty");
			throw new Exception('setCredentials: userid and apikey must not be empty');
		}

		if (!is_numeric($userid))
		{
			if ($this->debug)
				$this->addMsg("Error","setCredentials: userid must be a numeric value");
			throw new Exception('setCredentials: userid must be a numeric value');
		}
		
		if (!is_string($apikey))
		{
			if ($this->debug)
				$this->addMsg("Error","setCredentials: apikey must be a string value");
			throw new Exception('setCredentials: apikey must be a string value');
		}
		
		if (!empty($charid) && !is_numeric($charid))
		{
			if ($this->debug)
				$this->addMsg("Error","setCredentials: charid must be a numeric value");
			throw new Exception('setCredentials: charid must be a numeric value');
		}
	
		$this->userid = $userid;
		$this->apikey = $apikey;
		
		if (!empty($charid))
			$this->charid = $charid;
		
		return true;
	}
	
	public function getCredentials()
	{
		$result = array();
		$result['userid'] = $this->userid;
		$result['apikey'] = $this->apikey;
		$result['charid'] = $this->charid;
		
		return $result;
	}
	
	public function setDebug($bool)
	{
		if (is_bool($bool))
		{
			$this->debug = $bool;
			return true;
		}
		else
		{
			if ($this->debug)
				$this->addMsg("Error","setDebug: parameter must be present and boolean");
			throw new Exception('setDebug: parameter must be present and boolean');
		}
	}

	public function debug($bool)
	{ // legacy name of setDebug
		$r = $this->setDebug($bool);
		return $r;
	}
	
	public function getDebug()
	{
		return $this->debug;
	}

	public function setUseCache($bool)
	{
		if (is_bool($bool))
		{
			$this->usecache = $bool;
			return true;
		}
		else
		{
			if ($this->debug)
				$this->addMsg("Error","setUseCache: parameter must be present and boolean");
			throw new Exception('setUseCache: parameter must be present and boolean');
		}
	}

	public function cache($bool)
	{ // legacy name of setUseCache
		$r = $this->setUseCache($bool);
		return $r;
	}
	
	public function getUseCache()
	{
		return $this->usecache;
	}

	public function setUserAgent($agent)
	{
		if (is_string($agent))
		{
			$this->useragent = $agent;
			return true;
		}
		else
		{
			if ($this->debug)
				$this->addMsg("Error","setUserAgent: parameter must be present and a string");
			throw new Exception('setUserAgent: parameter must be present and a string');
		}
	}
	
	public function getUserAgent()
	{
		return $this->useragent;
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
				$this->addMsg("Error","setCacheDir: parameter must be present and a string");
			throw new Exception('setCacheDir: parameter must be present and a string');
		}
	}
	
	public function getCacheDir()
	{
		return $this->cachedir;
	}

	private function setCacheStatus($bool)
	{
		if (is_bool($bool))
		{
			$this->cachestatus = $bool;
			return true;
		}
		else
		{
			if ($this->debug)
				$this->addMsg("Error","setCacheStatus: parameter must be present and boolean");
			throw new Exception('setCacheStatus: parameter must be present and boolean');
		}

	}

	public function getCacheStatus()
	{
		return $this->cachestatus;	
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
			throw new Exception('setTimeTolerance: parameter must be present and an integer');
		}

	}


	public function getTimeTolerance()
	{
		return $this->timetolerance;
	}
	
	public function setApiSite($site)
	{
		if (is_string($site))
		{
			$this->apisite = $site;
			return true;
		} else {
			if ($this->debug)
				$this->addMsg("Error","setApiSite: parameter must be present and a string");
			throw new Exception('setApiSite: parameter must be present and a string');
		}
	}
	
	public function getApiSite()
	{
		return $this->apisite;
	}
	
	public function setApiSiteEvEC($site)
	{
		if (is_string($site))
		{
			$this->apisiteevec = $site;
			return true;
		} else {
			if ($this->debug)
				$this->addMsg("Error","setApiSiteEvEC: parameter must be present and a string");
			throw new Exception('setApiSiteEvEC: parameter must be present and a string');
		}
	}
	
	public function getApiSiteEvEC()
	{
		return $this->apisiteevec;
	}
	
	private function setApiError($code)
	{
		$this->apierror = $code;
	}

	public function getApiError()
	{
		return $this->apierror;
	}

	// add error message - both params are strings and are formatted as: "$type: $message"
	private function addMsg($type, $message)
	{
		if (!empty($type) && !empty($message))
		{
			$index = count($this->msg);
			
			$this->msg[$index]['type'] = $type;
			$this->msg[$index]['msg'] = $message;
			return true;
		}
		else
		{
			if ($this->debug)
				$this->addMsg("Error","addMsg: type and message must not be empty");
			throw new Exception('addMsg: type and message must not be empty');
		}
	}

	public function printErrors()
	{
		foreach ($this->msg as $msg)
			echo ("<b>" . $msg['type'] . "</b>: " . $msg['msg'] . "</br>\n");
	}
	
	/**********************
		Retrieve an XML File
		$path	path relative to the $apisite url
		$timeout	amount of time to keep the cached data before re-requesting it from the API, in minutes
		$cachePath	optional array of string values . These can be indizes into $params, or arbitrary strings, 
				and will be used to build the relative path to the cache file
		$params	optional array of paramaters (exclude apikey and userid, and charid)
				$params['characterID'] = 123456789;
		$binary	optional boolean - if true, treat the returned data as binary, not XML
	***********************/
	public function retrieveXml($path, $timeout = null, $cachePath = null, $params = null, $binary = false)
	{
		$this->setCacheStatus(false);
		if ($cachePath != null && !is_array($cachePath))
		{			
			if ($this->debug)
				$this->addMsg("Error","retrieveXml: Non-array value of cachePath param, not supported");
			throw new Exception('retrieveXml: Non-array value of cachePath param, not supported');
		}
		
		if ($params != null && !is_array($params))
		{			
			if ($this->debug)
				$this->addMsg("Error","retrieveXml: Non-array value of params param, not supported");
			throw new Exception('retrieveXml: Non-array value of params param, not supported');
		}
		
		if (empty($path))
		{
			if ($this->debug)
				$this->addMsg("Error","retrieveXml: path must not be empty");
			throw new Exception('retrieveXml: path must not be empty');
		}

		if (!is_array($params))
			$params = array();

		if ($this->userid != null && $this->apikey != null)
		{
			$params['userID'] = $this->userid;
			$params['apiKey'] = $this->apikey;
		}
		
		if ($this->charid != null)
			$params['characterID'] = $this->charid;
		
		// Save ourselves some calls and figure caching status out once for this function
		if ($this->usecache)
			$iscached = $this->isCached($path,$params,$cachePath,$timeout,$binary);
		// continue when not cached
		if (!$this->usecache || !$iscached)
		{
			// Presumably, if it's not set to '&', they might have had a reason for that - be a good citizen
			$sep = ini_get('arg_separator.output');
			// Necessary so that http_build_query does not spaz and give us '&amp;' as a separator on certain hosting providers
			ini_set('arg_separator.output','&');
			// query string
			if (count($params) > 0)
			{
				$query_string = http_build_query($params); // which has been forced to use '&' by ini_set, above. 5.2 notation being spurned to allow the code to run on 5.1
				$query_string = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $query_string);
				// http_build_query introduces array notation when it encounters multiple parameters of the same name, such as ?this=that&this=theother
				// This is encountered when making EvE-Central API calls, which use that notation
				// The preg_replace takes single dimension arrays and encodes them back the way we expect it
				// This works because the '=' character can't appear non-urlencoded except for exactly where I expect it to appear (between key / value pairs)
				// The above preg_replace will work  when a parameter has multiple "simple" values, resulting in single dimension arrays.
				// It will likely mangle anything more complex than that.
				// This preg_replace code is courtesy of donovan jimenez, taken from http://www.php.net/http_build_query
				// Dustin "Eldstrom" Tinklin suggested similar code, that did not account for encountering [...] elsewhere in the string, however - unlikely as that is 
				// Query strings can pretty much look like anything they want, but the square-bracket notation is not in widespread-use, as it goes against
				// W3C recommendations for field-value pairs in query strings
			} else {
				$query_string = "";
			}
			// And set it back to whatever sensical or non-sensical value it was in the 1st place
			ini_set('arg_separator.output',$sep);

			// open connection to the api
			// Note some free PHP5 servers block fsockopen() - in that case, find a different hosting provider, please
			$fp = fsockopen($this->apisite, 80, $errno, $errstr, 30);

			if (!$fp)
			{
				if ($this->debug)
					$this->addMsg("Error", "retrieveXml: Could not connect to API URL at $this->apisite, error $errstr ($errno)");
				// If we do have this in cache regardless of freshness, return it
				if ($this->usecache && $this->isCached($path,$params,$cachePath,0,$binary))
					return $this->loadCache($path, $params, $cachePath,$binary);
			}
			else
			{
				// request the xml
				fputs ($fp, "POST " . $path . " HTTP/1.0\r\n");
				fputs ($fp, "Host: " . $this->apisite . "\r\n");
				fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
				fputs ($fp, "User-Agent: " . $this->useragent . "\r\n");
				fputs ($fp, "Content-Length: " . strlen($query_string) . "\r\n");
				fputs ($fp, "Connection: close\r\n\r\n");
				if (strlen($query_string) > 0)
					fputs ($fp, $query_string."\r\n");
				
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
		
					if (!$binary)
					{
						if(!$this->debug) // turn off warnings that SimpleXML may throw
							$errlevel = error_reporting(E_ERROR);

						// check if there's an error or not
						try {
							$xml = new SimpleXMLElement($contents);
						} catch (Exception $e) {
							// If SimpleXML throws an Exception, there was likely a good reason - but as 0.2x doesn't "do" exceptions
							// I'll stay with just returning null or from cache. A "proper" rewrite would throw exception here, and elsewhere in the library
							if(!$this->debug) // Set PHP error reporting back to original value
								$errlevel = error_reporting(E_ERROR);
							else
								$this->addMsg("Failure","SimpleXML threw an exception on ".$contents);
							
							// If we do have this in cache regardless of freshness, return it
							if ($this->usecache && $this->isCached($path, $params, $cachePath, 0))
								return $this->loadCache($path, $params, $cachePath);
							
							return null;
						}

						$error = (string) $xml->error;
						if (!empty($error))
						{
							$code = $xml->error->attributes()->code;
							$this->setApiError($code);

							if ($this->debug)
								$this->addMsg("API Error", $code." : ".$error);
							else // Set PHP error reporting back to original value
								error_reporting($errlevel);

							// If we do have this in cache regardless of freshness, return it
							if ($this->usecache && $this->isCached($path, $params, $cachePath, 0))
								return $this->loadCache($path, $params, $cachePath);
							
							return null;
						}
						if(!$this->debug) // Set PHP error reporting back to original value
							error_reporting($errlevel); 							
						unset ($xml); // reduce memory footprint
					}
					
					$this->setApiError(0); // We fetched successfully


					if ($this->usecache && !$iscached)
					{
						$this->store($contents, $path, $params, $cachePath,$binary);
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
		else // We are to use a cache and the api results are still valid in cache
			return $this->loadCache($path, $params, $cachePath,$binary);
	}
	
	private function getCacheFile($path, $params, $cachePath, $binary = false)
	{
		$realpath = $this->cachedir;
		
		if ($cachePath != null)
		{
			if (!$binary)
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
			else // for binary files, we construct a file name, not a path name. Really only valid for the JPEGs I'm doing - this logic can always be changed if CCP adds more binary stuff. Which I doubt.
			{
				$realpath .= '/';
				foreach ($cachePath as $segment)
				{
					if (isset($params[$segment]))
					{
						$realpath .= $params[$segment];
					}
					else
					{
						$realpath .= $segment;
					}
				}
			}
		}
		
		if (!$binary)
		{
			$realpath .= $path;
		}
				
		return $realpath;
	}
	
	private function store($contents, $path, $params, $cachePath, $binary = false)
	{
		$file = $this->getCacheFile($path, $params, $cachePath, $binary);

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
	
	private function loadCache($path, $params, $cachePath, $binary = false)
	{
		// its cached, open it and use it
		$file = $this->getCacheFile($path, $params, $cachePath, $binary);
		
		$fp = fopen($file, "r");
		if ($fp)
		{
			$contents = fread($fp, filesize($file));
			fclose($fp);
			$this->setCacheStatus(true);
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
	// $path - The API path as given in the API URL, including the actual filename
	// $params - optional array of parameters for the API URL
	// $cachePath - optional array of strings or indizes into params to build the relative path to the cache file on disk
	// $timeout - minutes to keep the cache. Special value NULL means to use CCP's cachedUntil hint, and 0 means to just check for the file, don't check for freshness
	private function isCached($path, $params, $cachePath, $timeout, $binary = false)
	{
		$file = $this->getCacheFile($path, $params, $cachePath, $binary);

		if (file_exists($file) && filesize($file) > 0) // Added filesize to catch error on 0 length files. 
		{
			if ($timeout === 0) // timeout is 0, not NULL - magic value to indicate we want to know whether the file is there, never mind the caching time
				return true;

			if ($this->cachehint) // This file contains a cachedUntil hint - workaround because we don't have easy inheritance
			{
				$contents = file_get_contents($file);
				
				// check cache
				$xml = new SimpleXMLElement($contents);
			
				$cachetime = (string) $xml->currentTime;
				$time = strtotime($cachetime);
				
				$expirytime = (string) $xml->cachedUntil;
				$until = strtotime($expirytime);
				
				unset($contents); // Free us some memory
				unset($xml); // and free memory for this one, too

				if ($time === $until) // currentTime and cachedUntil are equal - CCP's way of telling us "don't cache"
					return false;
				
				// get GMT time
				$timenow = time();
				$now = $timenow - date('Z', $timenow);

// Uncomment in case we need some deep-dive debug. There's a TODO here - have levels of debug				
//				if ($this->debug) {
//				   $this->addMsg("Info","Got this at ".$time.", keep it until ".$until.", it is now ".$now);
//				   $this->addMsg("Info","Formatted: Got this at ".strftime("%b %d %Y %X",$time).", keep it until ".strftime("%b %d %Y %X",$until).", it is now ".strftime("%b %d %Y %X",$now));
//				}

				if ($timeout === NULL) // no explicit timeout given, use the cachedUntil time CCP gave us
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
			else // no cachedUntil hint, use the file date
			{
				// get local time
				$now = time();
				$time = filemtime($file); // Get the file modification time
				if ($timeout === NULL) // no explicit timeout given, which is not a supported thing to do in this case
				{
					if ($this->debug)
					{
						$this->addMsg("Error","isCached: $timeout cannot be NULL if no cachedUntil hint is present");
					}
					return false;
				}
				else
				{
					$minutes = $timeout * 60; // Don't need a time tolerance in this case
					if ($now >= $time + $minutes) // Time to fetch again
						return false;
					else // Cache is still valid
						return true;
				}
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
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Functions to retrieve data
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getAccountBalance($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getAccountBalance: Non-numeric value of timeout param, not supported");
			throw new Exception('getAccountBalance: Non-numeric value of timeout param, not supported');
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
				$this->addMsg("Error","getAccountBalance: Non-bool value of corp param, not supported");
			throw new Exception('getAccountBalance: Non-bool value of corp param, not supported');
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/AccountBalance.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/AccountBalance.xml.aspx", $timeout, $cachePath);
		}
		
		return $contents;
	}
	
	public function getSkillInTraining($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getSkillInTraining: Non-numeric value of timeout param, not supported");
			throw new Exception('getAccountBalance: Non-numeric value of timeout param, not supported');
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/char/SkillInTraining.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getCharacterSheet($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getCharacterSheet: Non-numeric value of timeout param, not supported");
			throw new Exception('getCharacterSheet: Non-numeric value of timeout param, not supported');
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
	
		$contents = $this->retrieveXml("/char/CharacterSheet.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getCharacters($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getCharacters: Non-numeric value of timeout param, not supported");
			throw new Exception('getAccountBalance: Non-numeric value of timeout param, not supported');
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
	
		$contents = $this->retrieveXml("/account/Characters.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getServerStatus($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getServerStatus: Non-numeric value of timeout param, not supported");
			throw new Exception('getServerStatus: Non-numeric value of timeout param, not supported');
		}

		$contents = $this->retrieveXml("/Server/ServerStatus.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getSkillTree($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getSkillTree: Non-numeric value of timeout param, not supported");
			throw new Exception('getAccountBalance: Non-numeric value of timeout param, not supported');
		}

		$contents = $this->retrieveXml("/eve/SkillTree.xml.aspx", $timeout);
		
		return $contents;
	}
	
	public function getCertificateTree($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getCertificateTree: Non-numeric value of timeout param, not supported");
			throw new Exception('getCertificateTree: Non-numeric value of timeout param, not supported');
		}

		$contents = $this->retrieveXml("/eve/CertificateTree.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getRefTypes($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getRefTypes: Non-numeric value of timeout param, not supported");
			throw new Exception('getRefTypes: Non-numeric value of timeout param, not supported');
		}

		$contents = $this->retrieveXml("/eve/RefTypes.xml.aspx", $timeout);
		
		return $contents;
	}
	
	public function getMemberTracking($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getMemberTracking: Non-numeric value of timeout param, not supported");
			throw new Exception('getMemberTracking: Non-numeric value of timeout param, not supported');
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/MemberTracking.xml.aspx", $timeout, $cachePath);

		return $contents;
	}
	
	public function getStarbaseList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getStarbaseList: Non-numeric value of timeout param, not supported");
			throw new Exception('getStarbaseList: Non-numeric value of timeout param, not supported');
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/StarbaseList.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getStarbaseDetail($id, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getStarbaseDetail: Non-numeric value of timeout param, not supported");
			throw new Exception('getStarbaseDetail: Non-numeric value of timeout param, not supported');
		}

		if (!is_numeric($id))
		{
			if ($this->debug)
				$this->addMsg("Error","getStarbaseDetail: Non-numeric value of id param, not supported");
			throw new Exception('getStarbaseDetail: Non-numeric value of id param, not supported');
		}

		$params = array();
		$params['itemID'] = $id;

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$cachePath[2] = 'itemID';
		
		$contents = $this->retrieveXml("/corp/StarbaseDetail.xml.aspx", $timeout, $cachePath, $params);
		
		return $contents;
	}
	
	public function getWalletTransactions($transid = null, $corp = false, $accountkey = 1000, $timeout = 65)
	// BUGBUG $timeout is hard-coded because of a bug in the EvE API, see http://myeve.eve-online.com/ingameboard.asp?a=topic&threadID=802053
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getWalletTransactions: Non-numeric value of timeout param, not supported");
			throw new Exception('getWalletTransactions: Non-numeric value of timeout param, not supported');
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
				$this->addMsg("Error","getWalletTransactions: Non-bool value of corp param, not supported");
			throw new Exception('getWalletTransactions: Non-bool value of corp param, not supported');
		}
		
		if ($transid != null && !is_numeric($transid))
		{
			if ($this->debug)
				$this->addMsg("Error","getWalletTransactions: Non-numeric value of transid param, not supported");
			throw new Exception('getWalletTransactions: Non-numeric value of transid param, not supported');
		}

		if (!is_numeric($accountkey))
		{
			if ($this->debug)
				$this->addMsg("Error","getWalletTransactions: Non-numeric value of accountkey param, not supported");
			throw new Exception('getWalletTransactions: Non-numeric value of accountkey param, not supported');
		}

		$params = array();
		
		// accountKey
		$params['accountKey'] = $accountkey;

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$cachePath[2] = 'accountKey';
		
		// beforeTransID
		if ($transid != null)
		{
			$params['beforeTransID'] = $transid;
			$cachePath[3] = 'beforeTransID';
		}

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/WalletTransactions.xml.aspx", $timeout, $cachePath, $params);
		}
		else
		{
			$contents = $this->retrieveXml("/char/WalletTransactions.xml.aspx", $timeout, $cachePath, $params);
		}
		
		return $contents;
	}
	
	public function getWalletJournal($refid = null, $corp = false, $accountkey = 1000, $timeout = 65)
	// BUGBUG $timeout is hard-coded because of a bug in the EvE API, see http://myeve.eve-online.com/ingameboard.asp?a=topic&threadID=802053
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getWalletJournal: Non-numeric value of timeout param, not supported");
			throw new Exception('getWalletJournal: Non-numeric value of timeout param, not supported');
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
				$this->addMsg("Error","getWalletJournal: Non-bool value of corp param, not supported");
			throw new Exception('getWalletJournal: Non-bool value of corp param, not supported');
		}
		
		if ($refid != null && !is_numeric($refid))
		{
			if ($this->debug)
				$this->addMsg("Error","getWalletJournal: Non-numeric value of refid param, not supported");
			throw new Exception('getWalletJournal: Non-numeric value of refid param, not supported');
		}

		if (!is_numeric($accountkey))
		{
			if ($this->debug)
				$this->addMsg("Error","getWalletJournal: Non-numeric value of accountkey param, not supported");
			throw new Exception('getWalletJournal: Non-numeric value of accountkey param, not supported');
		}

		$params = array();
		
		// accountKey
		$params['accountKey'] = $accountkey;

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$cachePath[2] = 'accountKey';
		
		// beforeRefID
		if ($refid != null && is_numeric($refid))
		{
			$params['beforeRefID'] = $refid;
			$cachePath[3] = 'beforeRefID';
		}

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/WalletJournal.xml.aspx", $timeout, $cachePath, $params);
		}
		else
		{
			$contents = $this->retrieveXml("/char/WalletJournal.xml.aspx", $timeout, $cachePath, $params);
		}
		
		return $contents;
	}

	public function getCorporationSheet($corpid = null, $timeout = null) 
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getCorporationSheet: Non-numeric value of timeout param, not supported");
			throw new Exception('getCorporationSheet: Non-numeric value of timeout param, not supported');
		}
		
		if ($corpid != null && !is_numeric($corpid))
		{
			if ($this->debug)
				$this->addMsg("Error","getCorporationSheet: Non-numeric value of corpid param, not supported");
			throw new Exception('getCorporationSheet: Non-numeric value of corpid param, not supported');
		}

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corpid != null && is_numeric($corpid))
		{
			$params = array();
			$params['corporationID'] = $corpid;
			$cachePath[2] = 'corporationID';
		}
		
 		$contents = $this->retrieveXml("/corp/CorporationSheet.xml.aspx", $timeout, $cachePath, $params);

 		return $contents;
	}

	public function getAllianceList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getAllianceList: Non-numeric value of timeout param, not supported");
			throw new Exception('getAllianceList: Non-numeric value of timeout param, not supported');
		}

		$contents = $this->retrieveXml("/eve/AllianceList.xml.aspx", $timeout);

 		return $contents;
	}
	
	public function getAssetList($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getAssetList: Non-numeric value of timeout param, not supported");
			throw new Exception('getAssetList: Non-numeric value of timeout param, not supported');
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
				$this->addMsg("Error","getAssetList: Non-bool value of corp param, not supported");
			throw new Exception('getAssetList: Non-bool value of corp param, not supported');
		}
	   
		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/AssetList.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/AssetList.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}
	
	public function getIndustryJobs($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getIndustryJobs: Non-numeric value of timeout param, not supported");
			throw new Exception('getIndustryJobs: Non-numeric value of timeout param, not supported');
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
				$this->addMsg("Error","getIndustryJobs: Non-bool value of corp param, not supported");
			throw new Exception('getIndustryJobs: Non-bool value of corp param, not supported');
		}
		
		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corp == true)
		{
			$contents = $this->retrieveXml("/corp/IndustryJobs.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/IndustryJobs.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}

	public function getFacWarSystems($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getFacWarSystems: Non-numeric value of timeout param, not supported");
			throw new Exception('getFacWarSystems: Non-numeric value of timeout param, not supported');
		}

		$contents = $this->retrieveXml("/map/FacWarSystems.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getFacWarStats($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getFacWarStats: Non-numeric value of timeout param, not supported");
			throw new Exception('getFacWarStats: Non-numeric value of timeout param, not supported');
		}

		if (!is_bool($corp))
		{
			if ($this->debug)
				$this->addMsg("Error","getFacWarStats: Non-bool value of corp param, not supported");
			throw new Exception('getFacWarStats: Non-bool value of corp param, not supported');
		}
		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
		{
			$contents = $this->retrieveXml("/corp/FacWarStats.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/FacWarStats.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}

	public function getFacWarTopStats($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getFacWarTopStats: Non-numeric value of timeout param, not supported");
			throw new Exception('getFacWarTopStats: Non-numeric value of timeout param, not supported');
		}

		$contents = $this->retrieveXml("/eve/FacWarTopStats.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getJumps($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getJumps: Non-numeric value of timeout param, not supported");
			throw new Exception('getJumps: Non-numeric value of timeout param, not supported');
		}

		$contents = $this->retrieveXml("/map/Jumps.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getSovereignty($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getMapSovereignty: Non-numeric value of timeout param, not supported");
			throw new Exception('getMapSovereignty: Non-numeric value of timeout param, not supported');
		}
		$contents = $this->retrieveXml("/map/Sovereignty.xml.aspx", $timeout);
		return $contents;
	}

	public function getKills($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getKills: Non-numeric value of timeout param, not supported");
			throw new Exception('getKills: Non-numeric value of timeout param, not supported');
		}
		$contents = $this->retrieveXml("/map/Kills.xml.aspx", $timeout);
		return $contents;
	}

	public function getKillLog($killid = null, $corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getKillLog: Non-numeric value of timeout param, not supported");
			throw new Exception('getKillLog: Non-numeric value of timeout param, not supported');
		}
	 
		if (!is_bool($corp))
		{
			if ($this->debug)
				$this->addMsg("Error","getKillLog: Non-bool value of corp param, not supported");
			throw new Exception('getKillLog: Non-bool value of corp param, not supported');
		}
		
		if ($killid != null && !is_numeric($killid))
		{
			if ($this->debug)
				$this->addMsg("Error","getKillLog: Non-numeric value of killid param, not supported");
			throw new Exception('getKillLog: Non-numeric value of killid param, not supported');
		}
		
		$params = array();
			
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		
		//beforeKillID
		if ($killid != null)
		{
			$params['beforeKillID'] = $killid;
			$cachePath[3] = 'beforeKillID';
		}

		if($corp == true)
			$contents = $this->retrieveXml("/corp/Killlog.xml.aspx", $timeout, $cachePath,$params);
		else
			$contents = $this->retrieveXml("/char/KillLog.xml.aspx", $timeout, $cachePath,$params);
		return $contents;
	}

	public function getMemberMedals($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getMemberMedals: Non-numeric value of timeout param, not supported");
			throw new Exception('getMemberMedals: Non-numeric value of timeout param, not supported');
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/MemberMedals.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getMedals($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getMedals: Non-numeric value of timeout param, not supported");
			throw new Exception('getMedals: Non-numeric value of timeout param, not supported');
		}
		if (!is_bool($corp))
		{
			if ($this->debug)
				$this->addMsg("Error","getMedals: Non-bool value of corp param, not supported");
			throw new Exception('getMedals: Non-bool value of corp param, not supported');
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
		{
			$contents = $this->retrieveXml("/corp/Medals.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/Medals.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}

	public function getMarketOrders($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getMarketOrders: Non-numeric value of timeout param, not supported");
			throw new Exception('getMarketOrders: Non-numeric value of timeout param, not supported');
		}
		if (!is_bool($corp))
		{
			if ($this->debug)
				$this->addMsg("Error","getMarketOrders: Non-bool value of corp param, not supported");
			throw new Exception('getAccountBalance: Non-bool value of corp param, not supported');
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
		{
			$contents = $this->retrieveXml("/corp/MarketOrders.xml.aspx", $timeout, $cachePath);
		}
		else
		{
			$contents = $this->retrieveXml("/char/MarketOrders.xml.aspx", $timeout, $cachePath);
		}
		return $contents;
	}

	public function getConquerableStationList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getConquerableStationList: Non-numeric value of timeout param, not supported");
			throw new Exception('getConquerableStationList: Non-numeric value of timeout param, not supported');
		}
		$contents = $this->retrieveXml("/eve/ConquerableStationList.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getStandings($corp = false,$timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getStandings: Non-numeric value of timeout param, not supported");
			throw new Exception('getStandings: Non-numeric value of timeout param, not supported');
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		if($corp == true)
			$contents = $this->retrieveXml("/corp/Standings.xml.aspx", $timeout, $cachePath);
		else 
			$contents = $this->retrieveXml("/char/Standings.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getContainerLog($timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getContainerLog: Non-numeric value of timeout param, not supported");
			throw new Exception('getContainerLog: Non-numeric value of timeout param, not supported');
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/ContainerLog.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getShareHolders($timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getShareHolders: Non-numeric value of timeout param, not supported");
			throw new Exception('getShareHolders: Non-numeric value of timeout param, not supported');
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/ShareHolders.xml.aspx", $timeout, $cachePath);
		return $contents;
	}
	
	public function getMemberSecurity($timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getMemberSecurity: Non-numeric value of timeout param, not supported");
			throw new Exception('getMemberSecurity: Non-numeric value of timeout param, not supported');
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/MemberSecurity.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getMemberSecurityLog($timeout = null)
	{

		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getMemberSecurityLog: Non-numeric value of timeout param, not supported");
			throw new Exception('getMemberSecurityLog: Non-numeric value of timeout param, not supported');
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/MemberSecurityLog.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getTitles($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getTitles: Non-numeric value of timeout param, not supported");
			throw new Exception('getTitles: Non-numeric value of timeout param, not supported');
		}
		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$contents = $this->retrieveXml("/corp/Titles.xml.aspx", $timeout, $cachePath);
		return $contents;
	}

	public function getErrorList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getErrorList: Non-numeric value of timeout param, not supported");
			throw new Exception('getErrorList: Non-numeric value of timeout param, not supported');
		}
		$contents = $this->retrieveXml("/eve/ErrorList.xml.aspx", $timeout);
		return $contents;
	}

	public function getCharacterName($ids, $timeout = null )
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getCharacterName: Non-numeric value of timeout param, not supported");
			throw new Exception('getCharacterName: Non-numeric value of timeout param, not supported');
		}

		if (is_numeric($ids))
		{
			$params = array();
			$params['ids'] = $ids;
			
			$cachePath = array();
			$cachePath[0] = 'ids';

			$contents = $this->retrieveXml("/eve/CharacterName.xml.aspx",$timeout,$cachePath,$params);

			return $contents;		
		}
		else if (is_array($ids))
		{
			// Sort elements of $ids in order
			sort($ids);

			$params = array();
			$params['ids'] = implode(',',$ids);
			
			$cachePath = array();
			$cachePath[0] = 'ids';

			$contents = $this->retrieveXml("/eve/CharacterName.xml.aspx",$timeout,$cachePath,$params);

			return $contents;		
		}
		else
		{
			if ($this->debug)
				$this->addMsg("Error","getCharacterName: Non-numeric/non-array or empty value of ids param, not supported");
			throw new Exception('getCharacterName: Non-numeric/non-array or empty value of ids param, not supported');
		}
	}
	
	public function getCharacterID($names, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getCharacterID: Non-numeric value of timeout param, not supported");
			throw new Exception('getCharacterID: Non-numeric value of timeout param, not supported');		
		}

		if (is_string($names))
		{
			$params = array();
			$params['names'] = $names;

			$cachePath = array();
			$cachePath[0] = 'names';

			$contents = $this->retrieveXml("/eve/CharacterID.xml.aspx",$timeout,$cachePath,$params);

			return $contents;
		}
		else if (is_array($names))
		{
			// Sort elements of $names in order
			sort($names);

			$params = array();
			$params['names'] = implode(',',$names);

			$cachePath = array();
			$cachePath[0] = 'names';

			$contents = $this->retrieveXml("/eve/CharacterID.xml.aspx",$timeout,$cachePath,$params);

			return $contents;
		}
		else
		{
			if ($this->debug)
				$this->addMsg("Error","getCharacterID: Non-string/non-array or empty value of names param, not supported");
			throw new Exception('getCharacterID: Non-string/non-array or empty value of names param, not supported');
		}
	}
	
	// getCharacterPortrait works quite differently from anything else. It returns a path to a JPEG file in the cache dir, not the actual data. There is no XML parsing, since there's no XML

	public function getCharacterPortrait($id, $size = 64, $timeout = 1440)
	{
	
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getCharacterPortrait: Non-numeric value of timeout param, not supported");
			throw new Exception('getCharacterPortrait: Non-numeric value of timeout param, not supported');		
		}

		if (!is_numeric($size)) // possible values are 64 and 256, but that's not checked, as CCP may change their mind
		{
			if ($this->debug)
				$this->addMsg("Error","getCharacterPortrait: Non-numeric value of size param, not supported");
			throw new Exception('getCharacterPortrait: Non-numeric value of size param, not supported');		
		}

		if (!is_numeric($id))
		{
			if ($this->debug)
				$this->addMsg("Error","getCharacterPortrait: Non-integer or empty value of id param, not supported");
			throw new Exception('getCharacterPortrait: Non-integer or empty value of id param, not supported');		
		}

		// Change site and cache directory for this function
		$site = $this->getApiSite();
		$this->setApiSite('img.eve.is');
		
		$cachedir = $this->getCacheDir();
		$this->setCacheDir($cachedir."/imgcache");

		$params = array();
		$params['s'] = $size;
		$params['c'] = $id;

		$cachePath = array();
		$cachePath[0] = 'c';
		$cachePath[1] = '-';
		$cachePath[2] = 's';
		$cachePath[3] = '.jpg';

		$this->cachehint = false; // workaround, inheritance would resolve this
		$this->retrieveXml("/serv.asp",$timeout,$cachePath,$params,TRUE); // optional "binary" parameter
		$this->cachehint = true;

		$result = $this->getCacheFile("/serv.asp", $params, $cachePath,TRUE);
		
		// Set site and cache directory back to what they were
		$this->setApiSite($site);
		$this->setCacheDir($cachedir);

		return $result;
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Functions to retrieve EvE-Central API data
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

	// Can we say kludge, boys and girls? However, as 0.2x doesn't lend itself to inheritance and I don't want to add yet another parameter to already-burdened retrieveXML, this will have to do for now

	// EvE-Central "evemon" function - really median mineral prices
	public function getMinerals($timeout = 30)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getMinerals: Non-numeric value of timeout param, not supported");
			throw new Exception('getMinerals: Non-numeric value of timeout param, not supported');	
		}
		
		$site = $this->getApiSite(); // current
		$this->setApiSite($this->getApiSiteEvEC()); // gonna use EvE-C
		$this->cachehint = false; // workaround, inheritance would resolve this
		$contents = $this->retrieveXml("/api/evemon", $timeout);
		$this->cachehint = true;
		$this->setApiSite($site); // and switch back
		
		return $contents;
	}

	// EvE-Central QuickLook function
	// regionlimit can exist as multiples, and can thus be passed as a numeric, a string, or an array
	public function getQuickLook($typeid,$sethours = null, $regionlimit = null, $usesystem = null, $setminQ = null, $timeout = 30)
	{

		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getQuickLook: Non-numeric value of timeout param, not supported");
			throw new Exception('getQuickLook: Non-numeric value of timeout param, not supported');	
		}

		if (!is_numeric($typeid))
		{
			if ($this->debug)
				$this->addMsg("Error","getQuickLook: Non-numeric or empty value of typeid param, not supported");
			throw new Exception('getQuickLook: Non-numeric or empty value of typeid param, not supported');	
		}
		
		if ($sethours && !is_numeric($sethours))
		{
			if ($this->debug)
				$this->addMsg("Error","getQuickLook: Non-numeric value of sethours param, not supported");
			throw new Exception('getQuickLook: Non-numeric value of sethours param, not supported');
		}

		if ($usesystem && !is_numeric($usesystem))
		{
			if ($this->debug)
				$this->addMsg("Error","getQuickLook: Non-numeric value of usesystem param, not supported");
			throw new Exception('getQuickLook: Non-numeric value of usesystem param, not supported');
		}

		if ($setminQ && !is_numeric($setminQ))
		{
			if ($this->debug)
				$this->addMsg("Error","getQuickLook: Non-numeric value of setminQ param, not supported");
			throw new Exception('getQuickLook: Non-numeric value of setminQ param, not supported');
		}

		$params = array();
		$cachePath = array();

		$params['typeid'] = $typeid;
		$cachePath[] = 'typeid';

		if ($regionlimit)
		{
			if (is_numeric($regionlimit))
			{
				$params['regionlimit'] = $regionlimit;
				$cachePath[] = 'regionlimit';
			}
			else if (is_array($regionlimit))
			{
				sort($regionlimit); // really just for the cache path
				$params['regionlimit'] = $regionlimit;
				$cachePath[] = implode(',',$regionlimit); // Comma-separated string for the cache path
			}
			else
			{
				if ($this->debug)
					$this->addMsg("Error","getQuickLook: Non-numeric and non-array value of regionlimit param, not supported");
				throw new Exception('getQuickLook: Non-numeric and non-array value of regionlimit param, not supported');
			}
		}

		if ($sethours)
		{
			$params['sethours'] = $sethours;
			$cachePath[] = 'sethours';
		}

		if	($usesystem)
		{
			$params['usesystem'] = $usesystem;
			$cachePath[] = 'usesystem';
		}

		if	($setminQ)
		{
			$params['setminQ'] = $setminQ;
			$cachePath[] = 'setminQ';
		}
		
		$site = $this->getApiSite(); // current
		$this->setApiSite($this->getApiSiteEvEC()); // gonna use EvE-C
		$this->cachehint = false; // workaround, inheritance would resolve this
		$contents = $this->retrieveXml("/api/quicklook", $timeout, $cachePath, $params);
		$this->cachehint = true;
		$this->setApiSite($site); // and switch back
		
		return $contents;
	}


	public function getMarketStat($typeid, $sethours = null, $regionlimit = null, $setminQ = null, $timeout = 30)
	{
		if ($timeout && !is_numeric($timeout))
		{
			if ($this->debug)
				$this->addMsg("Error","getMarketStat: Non-numeric value of timeout param, not supported");
			throw new Exception('getMarketStat: Non-numeric value of timeout param, not supported');	
		}
		
		if ($sethours && !is_numeric($sethours))
		{
			if ($this->debug)
				$this->addMsg("Error","getMarketStat: Non-numeric value of sethours param, not supported");
			throw new Exception('getMarketStat: Non-numeric value of sethours param, not supported');
		}

		if ($setminQ && !is_numeric($setminQ))
		{
			if ($this->debug)
				$this->addMsg("Error","getMarketStat: Non-numeric value of setminQ param, not supported");
			throw new Exception('getMarketStat: Non-numeric value of setminQ param, not supported');
		}

		$params = array();
		$cachePath = array();

		if (is_numeric($typeid))
		{
			$params['typeid'] = $typeid;
			$cachePath[] = 'typeid';
		}
		else if (is_array($typeid))
		{
			sort($typeid); // really just for the cache path
			$params['typeid'] = $typeid;
			$cachePath[] = implode(',',$typeid); // Comma-separated string for the cache path
		}
		else 
		{
			if ($this->debug)
				$this->addMsg("Error","getMarketStat: Non-numeric or empty value of typeid param, not supported");
			throw new Exception('getMarketStat: Non-numeric or empty value of typeid param, not supported');
		}

		if ($regionlimit)
		{
			if (is_numeric($regionlimit))
			{
				$params['regionlimit'] = $regionlimit;
				$cachePath[] = 'regionlimit';
			}
			else if (is_array($regionlimit))
			{
				sort($regionlimit); // really just for the cache path
				$params['regionlimit'] = $regionlimit;
				$cachePath[] = implode(',',$regionlimit); // Comma-separated string for the cache path
			}
			else
			{
				if ($this->debug)
					$this->addMsg("Error","getMarketStat: Non-numeric and non-array value of regionlimit param, not supported");
				throw new Exception('getMarketStat: Non-numeric and non-array value of regionlimit param, not supported');
			}
		}

		if ($sethours)
		{
			$params['hours'] = $sethours;
			$cachePath[] = 'hours';
		}
		
		if	($setminQ)
		{
			$params['minQ'] = $setminQ;
			$cachePath[] = 'minQ';
		}

		$site = $this->getApiSite(); // current
		$this->setApiSite($this->getApiSiteEvEC()); // gonna use EvE-C
		$this->cachehint = false; // workaround, inheritance would resolve this
		$contents = $this->retrieveXml("/api/marketstat", $timeout, $cachePath, $params);
		$this->cachehint = true;
		$this->setApiSite($site); // and switch back
		
		return $contents;
	}

}
?>
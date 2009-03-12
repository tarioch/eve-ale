<?php
/**************************************************************************
	Ale API Library for EvE, v0.23, 2009-01-23

	Portions Copyright (C) 2007 Kw4h
	Portions Copyright (C) 2008 Pavol Kovalik
	Portions Copyright (C) 2008 Gordon Pettey
	Portions Copyright (C) 2008 Thorsten Behrens
	Portions Copyright (C) 2009 Dustin Tinklin

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
	private $cachestatus = false; // Was the last fetch serviced from cache?
	private $cachefile = null; // Cache file name for the last fetch
	private $cachedat = null; // timestamp (UNIX epoch) of last fetch's cacheTime, if any
	private $cacheduntil = null; // timestamp (UNIX epoch) of last fetch's cachedUntil hint, if any
	private $apierror = 0; // API Error code, if any
	private $apierrortext = ''; // API Error text, if any
	private $cachehint = true; // A kludge to handle data without cachedUntil hint. Again, inheritance would make this go away 
	private $timetolerance = 5; // minutes to wait after cachedUntil, to allow for the server's time being fast

	public function setCredentials($userid, $apikey, $charid = null)
	{
		// Allow wiping of credentials by passing "null" for $userid
		if ($userid === null)
		{
			$this->userid = null;
			$this->apikey = null;
			$this->charid = null;
			return true;
		}

		if ($charid === null)
			$this->charid = null;

		if (empty($userid) || empty ($apikey))
			throw new Exception('setCredentials: userid and apikey must not be empty');

		if (!is_numeric($userid))
			throw new Exception('setCredentials: userid must be a numeric value');
		
		if (!is_string($apikey))
			throw new Exception('setCredentials: apikey must be a string value');
		
		if (!empty($charid) && !is_numeric($charid))
			throw new Exception('setCredentials: charid must be a numeric value');
	
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
		if (!is_bool($bool))
			throw new Exception('setDebug: parameter must be present and boolean');

		$this->debug = $bool;
		return true;
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
		if (!is_bool($bool))
			throw new Exception('setUseCache: parameter must be present and boolean');

		$this->usecache = $bool;
		return true;
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


	public function setCacheDir($dir)
	{
		if (!is_string($dir))
			throw new Exception('setCacheDir: parameter must be present and a string');

		$this->cachedir = $dir;
		return true;
	}
	
	public function getCacheDir()
	{
		return $this->cachedir;
	}

	private function setCacheStatus($bool)
	{
		if (!is_bool($bool))
			throw new Exception('setCacheStatus: parameter must be present and boolean');

		$this->cachestatus = $bool;
		return true;
	}

	public function getCacheStatus()
	{
		return $this->cachestatus;	
	}

	private function setCacheFile($file)
	{ // Record the cache file the last fetch created or used
		if ($file != null && !is_string($file))
			throw new Exception('setCacheFile: parameter must be present and a string');

		$this->cachefile = $file;

		return true;
	}

	public function getCacheFile()
	{
		return $this->cachefile;
	}
	
	// Set the cache times for retrieval, using the returned XML content
	// Stores cache time and expiry time in timestamp format
	private function setCacheTimes($contents)
	{
		if ($contents === null)
		{
			$this->cachedat = null;
			$this->cacheduntil = null;
			return true;
		}

		if (!is_string($contents))
			throw new Exception('setCacheTimes: parameter must be present and a string');
	
		$xml = new SimpleXMLElement($contents);

		$cachetime = (string) $xml->currentTime;
		$time = strtotime($cachetime);
		
		$expirytime = (string) $xml->cachedUntil;
		$until = strtotime($expirytime);
				
		unset($contents); // Free us some memory
		unset($xml); // and free memory for this one, too

		$this->cachedat = $time;
		$this->cacheduntil = $until;
	}
	
	// Value of currentTime on last fetch; or null if last fetch did not use cache / cacheTime
	// Returns timestamp format
	public function getCacheTime($localtime = false)
	{
		if (!$localtime) // return as GMT time
			return $this->cachedat;
		else // return as local time
				return $this->cachedat + date('Z', time());
	}

	// Value of cachedUntil on last fetch; or null if last fetch did not use cache / cachedUntil
	// Returns timestamp format
	public function getExpiryTime($localtime = false)
	{
		if (!$localtime) // return as GMT time
			return $this->cacheduntil;
		else // return as local time
			return $this->cacheduntil + date('Z', time()); 
	}

	public function setUserAgent($agent)
	{
		if (!is_string($agent))
			throw new Exception('setUserAgent: parameter must be present and a string');

		$this->useragent = $agent;
		return true;
	}
	
	public function getUserAgent()
	{
		return $this->useragent;
	}

	public function setTimeTolerance($tolerance)
	{
		if (!is_int($tolerance))
			throw new Exception('setTimeTolerance: parameter must be present and an integer');

		$this->timetolerance = $tolerance;
		return true;
	}


	public function getTimeTolerance()
	{
		return $this->timetolerance;
	}
	
	public function setApiSite($site)
	{
		if (!is_string($site))
			throw new Exception('setApiSite: parameter must be present and a string');

		$this->apisite = $site;
		return true;
	}
	
	public function getApiSite()
	{
		return $this->apisite;
	}
	
	public function setApiSiteEvEC($site)
	{
		if (!is_string($site))
			throw new Exception('setApiSiteEvEC: parameter must be present and a string');

		$this->apisiteevec = $site;
		return true;
	}
	
	public function getApiSiteEvEC()
	{
		return $this->apisiteevec;
	}
	
	private function setApiError($code)
	{
		if (!is_numeric($code))
			throw new Exception('setApiError: parameter must be present and numeric');

		$this->apierror = $code;
		return true;
	}

	public function getApiError()
	{
		return $this->apierror;
	}

	private function setApiErrorText($text)
	{
		if (!is_string($text))
			throw new Exception('setApiErrorText: parameter must be present and a string');

		$this->apierrortext = $text;
		return true;
	}

	public function getApiErrorText()
	{
		return $this->apierrortext;
	}

	// add error message - both params are strings and are formatted as: "$type: $message"
	private function addMsg($type, $message)
	{
		if (empty($type) || empty($message))
			throw new Exception('addMsg: type and message must not be empty');

		$index = count($this->msg);
		
		$this->msg[$index]['type'] = $type;
		$this->msg[$index]['msg'] = $message;
		return true;
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
		$this->setCacheTimes(null);
		$this->setCacheFile(null);

		if ($cachePath != null && !is_array($cachePath))
			throw new Exception('retrieveXml: Non-array value of cachePath param, not supported');
		
		if ($params != null && !is_array($params))
			throw new Exception('retrieveXml: Non-array value of params param, not supported');
		
		if (empty($path))
			throw new Exception('retrieveXml: path must not be empty');

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
		if ($this->getUseCache())
		{
			$cachefile = $this->getCacheFileName($path,$cachePath,$params,$binary);
			$iscached = $this->isCached($cachefile,$timeout);
		}

		// continue if not cached
		if (!$this->getUseCache() || !$iscached)
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
				if ($this->getUseCache() && $this->isCached($cachefile,0)) // Special timeout 0 means "just check whether the file exists"
					return $this->loadCache($cachefile);
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
							if ($this->getUseCache() && $this->isCached($cachefile, 0)) // special timeout 0 means "just check whether the file exists"
								return $this->loadCache($cachefile);

							return null;
						}

						$error = (string) $xml->error;
						if (!empty($error))
						{
							$code = (int) $xml->error->attributes()->code;
							$this->setApiError($code);
							$this->setApiErrorText($error);

							if ($this->debug)
								$this->addMsg("API Error", $code." : ".$error);
							else // Set PHP error reporting back to original value
								error_reporting($errlevel);

							// If we do have this in cache regardless of freshness, return it
							if ($this->getUseCache() && $this->isCached($cachefile, 0)) // special timeout 0 means "just check whether the file exists"
								return $this->loadCache($cachefile);

							return null;
						}

						if(!$this->debug) // Set PHP error reporting back to original value
							error_reporting($errlevel); 							
						unset ($xml); // reduce memory footprint
						
						if ($this->cachehint)
							$this->setCacheTimes($contents); // Record cache time and expiry time as given by CCP
					}
					
					$this->setApiError(0); // We fetched successfully
					$this->setApiErrorText('');

					if ($this->getUseCache() && !$iscached)
						$this->storeCache($cachefile,$contents);

					return $contents;
				} // if (start != FALSE)
				
				if ($this->debug)
					$this->addMsg("Error", "retrieveXml: Could not parse contents, unexpected API response");
				
				return null;
			}
		}
		else // We are to use a cache and the api results are still valid in cache
		{
			$this->setApiError(0); // Clear API errors that may still be hanging around
			$this->setApiErrorText('');
			return $this->loadCache($cachefile);
		}
	}

	// Get the name of the cache file - actually the path to it and the name
	// $path - The API path as given in the API URL, including the actual filename
	// $cachePath - optional array of strings or indizes into params to build the relative path to the cache file on disk	
	// $params - optional array of parameters for the API URL
	// $binary - whether this is a binary file, currently only used for characterPortrait JPEGs
	private function getCacheFileName($path,$cachePath,$params,$binary = false)
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
	
	private function storeCache($file,$contents)
	{
		if (!file_exists(dirname($file)))
		{
			mkdir(dirname($file), 0777, true);
		}
		if(file_put_contents($file,$contents))
		{
			$this->setCacheFile($file);
			
			if ($this->debug)
				$this->addMsg("Info","storeCache: Created cache file:" . $file);
			return true;
		}
		else
			throw new Exception("storeCache: Could not open cache file for writing: " . $file);
	}
	
	private function loadCache($file)
	{
		// its cached, open it and use it

		$contents = file_get_contents($file);
		if (!$contents) // Cache is empty or does not exist
			throw new Exception("loadCache: Cache file ".$file." did not load");

		$this->setCacheStatus(true);
		$this->setCacheFile($file);
		if ($this->cachehint)
			$this->setCacheTimes($contents); // Record cache time and expiry time as given by CCP

		if ($this->debug)
			$this->addMsg("Info","loadCache: Fetched cache file:" . $file);

		return $contents;
	}
	
	// checking if the cache expired or not based on TQ time
	// $file - the cache file to check
	// $timeout - minutes to keep the cache. Special value NULL means to use CCP's cachedUntil hint, and 0 means to just check for the file, don't check for freshness
	private function isCached($file, $timeout)
	{
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
						$this->addMsg("Error","isCached: $timeout cannot be NULL if no cachedUntil hint is present");
					throw new Exception('isCached: $timeout cannot be NULL if no cachedUntil hint is present');
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
				$this->addMsg("Info", "isCached: Cache file does not (yet?) exist: " . $file);			
			return false;
		}
	}

	// Delete file(s) in the cache directory, and empty cache directories as well
	// $path gives the file to be deleted, including its path
	// $recursive instructs on whether to find files of the same name in subdirectories and delete them
	// $deleteroot instructs on whether to also delete the file given explicitly in $path, if $recursive is true
	private function deleteCache($path,$recursive=true,$deleteroot=false)
	{
		if (!is_string($path))
			throw new Exception('deleteCache: Parameter $path must be present and a string');

		if (!$recursive)
		{
			if (unlink($path))
			{
				if ($this->getDebug())
					$this->addMsg("info","Deleted stale cache file $path");
				return true;
			}
		}
		// recursive processing from here
		if ($deleteroot)
		{
			if (unlink($path))
				if ($this->getDebug())
					$this->addMsg("info","Deleted stale cache file $path");
		}

		$name = basename($path);
		$dir = dirname($path);

		$entries = scandir($dir);

		$i = 0; // Keep track of directory entries so we can figure out whether it's now empty and we ought to delete it
		foreach ($entries as $entry)
		{
			if ($entry == '.' || $entry == '..') // skip over this dir and higher dir entries - I want to recurse down, not up
				continue;

			$i++;
			$file = $dir.'/'.$entry; // Full path to this directory entry
			if (is_dir($file) && file_exists($file.'/'.$name)) // We found another one of these in a sub-directory - go after it!
				$this->deleteCache($file.'/'.$name,true,true); // Recurse down, and delete the file itself, too
		}

		if (!$i) // current directory is (now) empty, delete it
		{
			if (rmdir($dir))
				if ($this->getDebug())
					$this->addMsg("info","Deleted empty cache directory $dir");
		}

		return true;
	}

	// Change the 'cachedUntil' value in XML, in response to a request to do so by CCP's API response
	// $path - path to the cache file, including file name
	// $newuntil - new value to write, in text form
	// $recursive - whether to change the value in subdirectories containing the same filename, as well
	private function changeCachedUntil($path, $newuntil, $recursive=true)
	{
		$doc = new DOMDocument;
		$doc->Load($path);

		$xpath = new DOMXPath($doc);
		$query = '//eveapi/cachedUntil';
		$query = $xpath->query($query);
		$until = $query->item(0);
		$until->nodeValue = $newuntil;
		
		if (!file_put_contents($path, $doc->saveXML()))
			throw new Exception ("changeCachedUntil: Failed to write cache file: ".$path);

		unset ($doc,$xpath,$query); // Manual garbage collection

		if ($this->getDebug())
			$this->addMsg("info","changeCachedUntil: Change cachedUntil for ".$path." to ".$newuntil);
		
		if ($recursive)
		{
			$name = basename($path);
			$dir = dirname($path);

			$entries = scandir($dir);

			foreach ($entries as $entry)
			{
				if ($entry == '.' || $entry == '..') // skip over this dir and higher dir entries - I want to recurse down, not up
					continue;

				$file = $dir.'/'.$entry; // Full path to this directory entry
				if (is_dir($file) && file_exists($file.'/'.$name)) // We found another one of these in a sub-directory - go after it!
					$this->changeCachedUntil($file.'/'.$name,$newuntil); // Recurse down
			}
		}

		return true;
	}


	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Functions to retrieve data
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function getAccountBalance($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getAccountBalance: Non-numeric value of timeout param, not supported');

		if (!is_bool($corp))
			throw new Exception('getAccountBalance: Non-bool value of corp param, not supported');

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
			throw new Exception('getSkillInTraining: Non-numeric value of timeout param, not supported');

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/char/SkillInTraining.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}

public function getSkillQueue($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getSkillQueue: Non-numeric value of timeout param, not supported');

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/char/SkillQueue.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}

	
	public function getCharacterSheet($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getCharacterSheet: Non-numeric value of timeout param, not supported');

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
	
		$contents = $this->retrieveXml("/char/CharacterSheet.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getCharacters($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getAccountBalance: Non-numeric value of timeout param, not supported');

		$cachePath = array();
		$cachePath[0] = 'userID';
	
		$contents = $this->retrieveXml("/account/Characters.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getServerStatus($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getServerStatus: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/Server/ServerStatus.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getSkillTree($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getAccountBalance: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/eve/SkillTree.xml.aspx", $timeout);
		
		return $contents;
	}
	
	public function getCertificateTree($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getCertificateTree: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/eve/CertificateTree.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getRefTypes($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getRefTypes: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/eve/RefTypes.xml.aspx", $timeout);
		
		return $contents;
	}
	
	public function getMemberTracking($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getMemberTracking: Non-numeric value of timeout param, not supported');

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/MemberTracking.xml.aspx", $timeout, $cachePath);

		return $contents;
	}
	
	public function getStarbaseList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getStarbaseList: Non-numeric value of timeout param, not supported');

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/StarbaseList.xml.aspx", $timeout, $cachePath);
		
		return $contents;
	}
	
	public function getStarbaseDetail($id, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getStarbaseDetail: Non-numeric value of timeout param, not supported');

		if (!is_numeric($id))
			throw new Exception('getStarbaseDetail: Non-numeric value of id param, not supported');

		$params = array();
		$params['itemID'] = $id;

		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';
		$cachePath[2] = 'itemID';
		
		$contents = $this->retrieveXml("/corp/StarbaseDetail.xml.aspx", $timeout, $cachePath, $params);
		
		return $contents;
	}
	
	public function getWalletTransactions($transid = null, $corp = false, $accountkey = 1000, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getWalletTransactions: Non-numeric value of timeout param, not supported');

		if (!is_bool($corp))
			throw new Exception('getWalletTransactions: Non-bool value of corp param, not supported');
		
		if ($transid != null && !is_numeric($transid))
			throw new Exception('getWalletTransactions: Non-numeric value of transid param, not supported');

		if (!is_numeric($accountkey))
			throw new Exception('getWalletTransactions: Non-numeric value of accountkey param, not supported');

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
			$path = "/corp/WalletTransactions.xml.aspx";
		else
			$path = "/char/WalletTransactions.xml.aspx";

		$contents = $this->retrieveXml($path, $timeout, $cachePath, $params);

		if (!$contents) // If this fails and doesn't return anything, don't run through the adjustment procedures
			return $contents;

		if ($timeout === NULL && $err = $this->getApiError()) // we are to follow CCP's cachedUntil hints - handle 101/103 "extension" errors
		{
			switch ($err)
			{
				case 101: // Wallet exhausted
				case 103: // Already returned one week of data
					if ($this->getCacheStatus()) // The cache file exists - not always a given -- works only because I know retrieveXML tries to fetch from cache on API error 
					{
						$text = $this->getApiErrorText();
						$newuntil = substr($text,stripos($text,"retry after ")+12,19); // Grab the date in "yyyy-mm-dd hh:mm:ss" format. 19 long, and comes right after "retry after"
						$file = $this->getCacheFile();
						$this->changeCachedUntil($file,$newuntil); // Use DOM to change the cachedUntil value in the cache file
						$contents = $this->loadCache($file); // So that we return the changed cache, and the cache times are set correctly
					}
					break;
				default:
				// Do nothing at all
			}
		}

		if ($this->getUseCache() && !$this->getCacheStatus() && !$transid) // we are using cache, and this did not come from cache, and it's the first fetch in a series
			$this->deleteCache($this->getCacheFile()); // Delete all cached copies in subdirectories, and the subdirs themselves if that leaves them empty. Don't touch main (transid "0") cached copy

		return $contents;
	}
	
	public function getWalletJournal($refid = null, $corp = false, $accountkey = 1000, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getWalletJournal: Non-numeric value of timeout param, not supported');

		if (!is_bool($corp))
			throw new Exception('getWalletJournal: Non-bool value of corp param, not supported');
		
		if ($refid != null && !is_numeric($refid))
			throw new Exception('getWalletJournal: Non-numeric value of refid param, not supported');

		if (!is_numeric($accountkey))
			throw new Exception('getWalletJournal: Non-numeric value of accountkey param, not supported');

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
			$path = "/corp/WalletJournal.xml.aspx";
		else
			$path = "/char/WalletJournal.xml.aspx";

		$contents = $this->retrieveXml($path, $timeout, $cachePath, $params);

		if (!$contents) // If this fails and doesn't return anything, don't run through the adjustment procedures
			return $contents;

		if ($timeout === NULL && $err = $this->getApiError()) // we are to follow CCP's cachedUntil hints - handle 101/103 "extension" errors
		{
			switch ($err)
			{
				case 101: // Wallet exhausted
				case 103: // Already returned one week of data
					if ($this->getCacheStatus()) // The cache file exists - not always a given -- works only because I know retrieveXML tries to fetch from cache on API error
					{
						$text = $this->getApiErrorText();
						$newuntil = substr($text,stripos($text,"retry after ")+12,19); // Grab the date in "yyyy-mm-dd hh:mm:ss" format. 19 long, and comes right after "retry after"
						$file = $this->getCacheFile();
						$this->changeCachedUntil($file,$newuntil); // Use DOM to change the cachedUntil value in the cache file
						$contents = $this->loadCache($file); // So that we return the changed cache, and the cache times are set correctly
					}
					break;
				default:
				// Do nothing at all
			}
		}		

		if ($this->getUseCache() && !$this->getCacheStatus() && !$refid) // we are using cache, and this did not come from cache, and it's the first fetch in a series
			$this->deleteCache($this->getCacheFile()); // Delete all cached copies in subdirectories, and the subdirs themselves if that leaves them empty. Don't touch main (refid "0") cached copy

		return $contents;
	}

	public function getCorporationSheet($corpid = null, $timeout = null) 
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getCorporationSheet: Non-numeric value of timeout param, not supported');
		
		if ($corpid != null && !is_numeric($corpid))
			throw new Exception('getCorporationSheet: Non-numeric value of corpid param, not supported');

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
			throw new Exception('getAllianceList: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/eve/AllianceList.xml.aspx", $timeout);

 		return $contents;
	}
	
	public function getAssetList($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getAssetList: Non-numeric value of timeout param, not supported');

		if (!is_bool($corp))
			throw new Exception('getAssetList: Non-bool value of corp param, not supported');
	   
		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corp == true)
			$contents = $this->retrieveXml("/corp/AssetList.xml.aspx", $timeout, $cachePath);
		else
			$contents = $this->retrieveXml("/char/AssetList.xml.aspx", $timeout, $cachePath);
	
		if (!$contents) // If this fails and doesn't return anything, don't run through the adjustment procedures
			return $contents;
	
		if ($timeout === NULL && $err = $this->getApiError()) // we are to follow CCP's cachedUntil hints - handle 115 "extension" error
		{
			switch ($err)
			{
				case 115: // Assets already downloaded
					if ($this->getCacheStatus()) // The cache file exists - not always a given -- works only because I know retrieveXML tries to fetch from cache on API error
					{
						$text = $this->getApiErrorText();
						$newuntil = substr($text,stripos($text,"retry after ")+12,19); // Grab the date in "yyyy-mm-dd hh:mm:ss" format. 19 long, and comes right after "retry after"
						$file = $this->getCacheFile();
						$this->changeCachedUntil($file,$newuntil,false); // Use DOM to change the cachedUntil value in the cache file, but not recursively
						$contents = $this->loadCache($file); // So that we return the changed cache, and the cache times are set correctly
					}
					break;
				default:
				// Do nothing at all
			}
		}

		return $contents;
	}
	
	public function getIndustryJobs($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getIndustryJobs: Non-numeric value of timeout param, not supported');

		if (!is_bool($corp))
			throw new Exception('getIndustryJobs: Non-bool value of corp param, not supported');
		
		$cachePath = array();
		$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if ($corp == true)
			$contents = $this->retrieveXml("/corp/IndustryJobs.xml.aspx", $timeout, $cachePath);
		else
			$contents = $this->retrieveXml("/char/IndustryJobs.xml.aspx", $timeout, $cachePath);

		if (!$contents) // If this fails and doesn't return anything, don't run through the adjustment procedures
			return $contents;

		if ($timeout === NULL && $err = $this->getApiError()) // we are to follow CCP's cachedUntil hints - handle 116 "extension" error
		{
			switch ($err)
			{
				case 116: // Industry jobs already downloaded
					if ($this->getCacheStatus()) // The cache file exists - not always a given -- works only because I know retrieveXML tries to fetch from cache on API error
					{
						$text = $this->getApiErrorText();
						$newuntil = substr($text,stripos($text,"retry after ")+12,19); // Grab the date in "yyyy-mm-dd hh:mm:ss" format. 19 long, and comes right after "retry after"
						$file = $this->getCacheFile();
						$this->changeCachedUntil($file,$newuntil,false); // Use DOM to change the cachedUntil value in the cache file, but not recursively
						$contents = $this->loadCache($file); // So that we return the changed cache, and the cache times are set correctly
					}
					break;
				default:
				// Do nothing at all
			}
		}

		return $contents;
	}

	public function getFacWarSystems($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getFacWarSystems: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/map/FacWarSystems.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getFacWarStats($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getFacWarStats: Non-numeric value of timeout param, not supported');

		if (!is_bool($corp))
			throw new Exception('getFacWarStats: Non-bool value of corp param, not supported');

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
			throw new Exception('getFacWarTopStats: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/eve/FacWarTopStats.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getJumps($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getJumps: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/map/Jumps.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getSovereignty($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getMapSovereignty: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/map/Sovereignty.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getKills($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getKills: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/map/Kills.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getKillLog($killid = null, $corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getKillLog: Non-numeric value of timeout param, not supported');
	 
		if (!is_bool($corp))
			throw new Exception('getKillLog: Non-bool value of corp param, not supported');
		
		if ($killid != null && !is_numeric($killid))
			throw new Exception('getKillLog: Non-numeric value of killid param, not supported');
		
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

		if (!$contents) // If this fails and doesn't return anything, don't run through the adjustment procedures
			return $contents;

		if ($timeout === NULL && $err = $this->getApiError()) // we are to follow CCP's cachedUntil hints - handle 119 "extension" error
		{
			switch ($err)
			{
				case 119: // Kills exhausted
					if ($this->getCacheStatus()) // The cache file exists - not always a given -- works only because I know retrieveXML tries to fetch from cache on API error
					{
						$text = $this->getApiErrorText();
						$newuntil = substr($text,stripos($text,"retry after ")+12,19); // Grab the date in "yyyy-mm-dd hh:mm:ss" format. 19 long, and comes right after "retry after"
						$file = $this->getCacheFile();
						$this->changeCachedUntil($file,$newuntil); // Use DOM to change the cachedUntil value in the cache file
						$contents = $this->loadCache($file); // So that we return the changed cache, and the cache times are set correctly
					}
					break;
				default:
				// Do nothing at all
			}
		}

		if ($this->getUseCache() && !$this->getCacheStatus() && !$killid) // we are using cache, and this did not come from cache, and it's the first fetch in a series
			$this->deleteCache($this->getCacheFile()); // Delete all cached copies in subdirectories, and the subdirs themselves if that leaves them empty. Don't touch main (killid "0") cached copy

		return $contents;
	}

	public function getMemberMedals($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getMemberMedals: Non-numeric value of timeout param, not supported');

		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/MemberMedals.xml.aspx", $timeout, $cachePath);

		return $contents;
	}

	public function getMedals($corp = false, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getMedals: Non-numeric value of timeout param, not supported');

		if (!is_bool($corp))
			throw new Exception('getMedals: Non-bool value of corp param, not supported');

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
			throw new Exception('getMarketOrders: Non-numeric value of timeout param, not supported');

		if (!is_bool($corp))
			throw new Exception('getAccountBalance: Non-bool value of corp param, not supported');

		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		if($corp == true)
			$contents = $this->retrieveXml("/corp/MarketOrders.xml.aspx", $timeout, $cachePath);
		else
			$contents = $this->retrieveXml("/char/MarketOrders.xml.aspx", $timeout, $cachePath);

		if ($timeout === NULL && $err = $this->getApiError()) // we are to follow CCP's cachedUntil hints - handle 117 "extension" error
		{
			switch ($err)
			{
				case 117: // Market orders already downloaded
					if ($this->getCacheStatus()) // The cache file exists - not always a given -- works only because I know retrieveXML tries to fetch from cache on API error
					{
						$text = $this->getApiErrorText();
						$newuntil = substr($text,stripos($text,"retry after ")+12,19); // Grab the date in "yyyy-mm-dd hh:mm:ss" format. 19 long, and comes right after "retry after"
						$file = $this->getCacheFile();
						$this->changeCachedUntil($file,$newuntil,false); // Use DOM to change the cachedUntil value in the cache file, but not recursively
						$contents = $this->loadCache($file); // So that we return the changed cache, and the cache times are set correctly
					}
					break;
				default:
				// Do nothing at all
			}
		}

		return $contents;
	}

	public function getConquerableStationList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getConquerableStationList: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/eve/ConquerableStationList.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getStandings($corp = false,$timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getStandings: Non-numeric value of timeout param, not supported');

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
			throw new Exception('getContainerLog: Non-numeric value of timeout param, not supported');

		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/ContainerLog.xml.aspx", $timeout, $cachePath);

		return $contents;
	}

	public function getShareHolders($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getShareHolders: Non-numeric value of timeout param, not supported');

		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/ShareHolders.xml.aspx", $timeout, $cachePath);

		return $contents;
	}
	
	public function getMemberSecurity($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getMemberSecurity: Non-numeric value of timeout param, not supported');

		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/MemberSecurity.xml.aspx", $timeout, $cachePath);

		return $contents;
	}

	public function getMemberSecurityLog($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getMemberSecurityLog: Non-numeric value of timeout param, not supported');

		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/MemberSecurityLog.xml.aspx", $timeout, $cachePath);

		return $contents;
	}

	public function getTitles($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getTitles: Non-numeric value of timeout param, not supported');

		$cachePath = array();
	 	$cachePath[0] = 'userID';
		$cachePath[1] = 'characterID';

		$contents = $this->retrieveXml("/corp/Titles.xml.aspx", $timeout, $cachePath);

		return $contents;
	}

	public function getErrorList($timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getErrorList: Non-numeric value of timeout param, not supported');

		$contents = $this->retrieveXml("/eve/ErrorList.xml.aspx", $timeout);
		
		return $contents;
	}

	public function getCharacterName($ids, $timeout = null )
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getCharacterName: Non-numeric value of timeout param, not supported');

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
			throw new Exception('getCharacterName: Non-numeric/non-array or empty value of ids param, not supported');
		}
	}
	
	public function getCharacterID($names, $timeout = null)
	{
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getCharacterID: Non-numeric value of timeout param, not supported');		

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
			throw new Exception('getCharacterID: Non-string/non-array or empty value of names param, not supported');
		}
	}
	
	// getCharacterPortrait works quite differently from anything else. It returns a path to a JPEG file in the cache dir, not the actual data. There is no XML parsing, since there's no XML

	public function getCharacterPortrait($id, $size = 64, $timeout = 1440)
	{
	
		if ($timeout && !is_numeric($timeout))
			throw new Exception('getCharacterPortrait: Non-numeric value of timeout param, not supported');		

		if (!is_numeric($size)) // possible values are 64 and 256, but that's not checked, as CCP may change their mind
			throw new Exception('getCharacterPortrait: Non-numeric value of size param, not supported');		

		if (!is_numeric($id))
			throw new Exception('getCharacterPortrait: Non-integer or empty value of id param, not supported');		

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

		$result = $this->getCacheFile();
		
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
			throw new Exception('getMinerals: Non-numeric value of timeout param, not supported');	
		
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
			throw new Exception('getQuickLook: Non-numeric value of timeout param, not supported');	

		if (!is_numeric($typeid))
			throw new Exception('getQuickLook: Non-numeric or empty value of typeid param, not supported');	
		
		if ($sethours && !is_numeric($sethours))
			throw new Exception('getQuickLook: Non-numeric value of sethours param, not supported');

		if ($usesystem && !is_numeric($usesystem))
			throw new Exception('getQuickLook: Non-numeric value of usesystem param, not supported');

		if ($setminQ && !is_numeric($setminQ))
			throw new Exception('getQuickLook: Non-numeric value of setminQ param, not supported');

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
			throw new Exception('getMarketStat: Non-numeric value of timeout param, not supported');	
		
		if ($sethours && !is_numeric($sethours))
			throw new Exception('getMarketStat: Non-numeric value of sethours param, not supported');

		if ($setminQ && !is_numeric($setminQ))
			throw new Exception('getMarketStat: Non-numeric value of setminQ param, not supported');

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
<?php

class img
{
	private $apisite = "img.eve.is";
	private $cachedir = './imgcache';
	public $debug = false;
	private $msg = array();
	private $usecache = true;
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
	public function retrieveimg($size = null,$id = null)
	{
		if (!is_int($size) or !($size == 64 or $size == 256))
		{			
			if ($this->debug)
			{
				$this->addMsg("Error","retrieveimg: Non-intger value or incorrect vlaue for size , reverting to default value");
			}
			$size = 64;
			
		}
		$cachePath = $size;
		$path = $id .".jpg";
		if (!empty($id))
		{
						
			// continue when not cached
			if (!$this->usecache || !$this->isCached($path, $cachePath, $timeout))
			{
			 $uri = "/serv.asp?s=" . $size . "&c=" . $id;

				print "uri genertated : $uri \n";
				// open connection to the api
				// Note some free PHP5 servers block fsockopen() - in that case, find a different hosting provider, please
				$fp = fsockopen($this->apisite, 80, $errno, $errstr, 30);

				if (!$fp)
				{
					if ($this->debug)
						$this->addMsg("Error", "retrieveImg: Could not connect to API URL at $this->apisite, error $errstr ($errno)");
				}
				else
				{
					// request the xml
					
					fputs ($fp, "GET " . $uri . " HTTP/1.0\r\n");
					fputs ($fp, "Host: " . $this->apisite . "\r\n");
					fputs ($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
					fputs ($fp, "User-Agent: PHPApi\r\n");
					fputs ($fp, "Content-Length: " . strlen($poststring) . "\r\n");
					fputs ($fp, "Connection: close\r\n\r\n");
					
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
						if (!$this->isCached($path, $cachePath))
						{
							$this->store($contents, $path, $cachePath);
						}
						
						return $cachePath . "/" . $path;
					}
					
				}
			}
			else
			{
				return $this->loadCache($path, $cachePath);
			}
		}
		elseif ($this->debug)
		{
			$this->addMsg("Error", "retrieveImg: path is empty");
		}
		
		return null;
	}
	
	private function getCacheFile($path, $cachePath)
	{
		$realpath = $this->cachedir;
		
		$realpath .= '/' . $cachePath . '/' . $path;
				
		return $realpath;
	}
	
	private function store($contents, $path, $cachePath)
	{
		$file = $this->getCacheFile($path, $cachePath);

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
	
	private function loadCache($path, $cachePath)
	{
		// its cached, open it and use it
		$file = $this->getCacheFile($path, $cachePath);
		
		$fp = fopen($file, "r");
		if ($fp and filesize($file) > 0)
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
				$this->addMsg("Error", "loadCache: is not readble file: " . $file);
			}
		}

		return $file;
	}
	
	// checking if the cache expired or not based on TQ time
	private function isCached($path, $cachePath)
		{
		$file = $this->getCacheFile($path, $cachePath);

		if (file_exists($file)&& filesize($file) > 0) // Added filesize to catch error on 0 length files. 
		{
			$fp = fopen($file, "r");
			
			if ($fp)
			{
				$contents = fread($fp, filesize($file));
				fclose($fp);
								
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


}
?>

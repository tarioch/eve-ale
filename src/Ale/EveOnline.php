<?php
/**
 * @version $Id: eveonline.php 212 2009-10-17 22:29:38Z kovalikp $
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

use Ale\Cache\Cache;
use Ale\Request\Request;
use \SimpleXMLElement;

define('ALE_AUTH_DEFAULT', 0);
define('ALE_AUTH_NONE', 1);
define('ALE_AUTH_USER', 2);
define('ALE_AUTH_CHARACTER', 3);
define('ALE_AUTH_AVAILABLE', 4);
define('ALE_AUTH_API', 5);

class EveOnline extends Base
{

    private $keyID;

    private $vCode;

    /**
     * @deprecated
     */
    private $userID;

    /**
     * @deprecated
     */
    private $apiKey;

    private $characterID;
    
    // private $xml;
    private $cachedUntil;

    protected $default = array(
        'host' => 'https://api.eveonline.com/',
        'suffix' => '.xml.aspx',
        'parserClass' => 'Ale\Parser\XmlElement',
        'serverError' => 'throwException',
        'requestError' => 'throwException'
    );

    public function __construct(Request $request, Cache $cache = null, array $config = array())
    {
        parent::__construct($request, $cache, $config);
    }

    /**
     * Extract cached until time
     *
     * @param string $content            
     * @return string
     */
    protected function getCachedUntil($content)
    {
        return (string) $this->cachedUntil;
    }

    /**
     * Scans conntent for cachedUntil and error
     *
     * @param string $content            
     * @param int $errorCode            
     * @param string $errorText            
     */
    protected function scanContent($content, &$errorCode, &$errorText)
    {
        $this->cachedUntil = null;
        $reader = new XMLReader();
        $reader->xml($content);
        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == "error") {
                // got an error
                $errorText = $reader->readString();
                $errorCode = intval($reader->getAttribute('code'));
                if ($reader->next("cachedUntil")) {
                    $this->cachedUntil = $reader->readString();
                }
            } else 
                if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == "result") {
                    // no errors, we need to read the cache time though
                    if ($reader->next("cachedUntil")) {
                        $this->cachedUntil = $reader->readString();
                    }
                }
        }
        $reader->close();
    }

    /**
     * Check for server error.
     * Return null, string or object, based on configuration
     *
     * @param string $content            
     * @param bool $useCache            
     * @return mixed
     */
    protected function handleContent($content, &$useCache = true)
    {
        if (is_null($content)) {
            return null;
        }
        $errorCode = 0;
        $errorText = '';
        $this->scanContent($content, $errorCode, $errorText);
        
        // if we found an error
        if ($errorCode || $errorText) {
            // we want to update cached until
            $this->cache->updateCachedUntil($this->cachedUntil);
            // but we do not want to cache error, right?
            $useCache = false;
            switch ($this->config['serverError']) {
                case 'returnParsed':
                    break;
                case 'returnNull':
                    return null;
                    break;
                case 'throwException':
                default:
                    if (100 <= $errorCode && $errorCode < 200) {
                        throw new EveUserInputException($errorText, $errorCode, (string) $this->cachedUntil);
                    } elseif (200 <= $errorCode && $errorCode < 300) {
                        throw new EveAuthenticationException($errorText, $errorCode, (string) $this->cachedUntil);
                    } elseif (500 <= $errorCode && $errorCode < 600) {
                        throw new EveServerErrorException($errorText, $errorCode, (string) $this->cachedUntil);
                    } else {
                        throw new EveMiscellaneousException($errorText, $errorCode, (string) $this->cachedUntil);
                    }
            }
        }
        
        return parent::handleContent($content, $useCache);
    }

    /**
     * Resolve required authentication details based on context
     *
     * @param string $context            
     * @return int
     */
    protected function getAuth($context)
    {
        switch ($context) {
            case 'eve':
            case 'map':
                $auth = ALE_AUTH_NONE;
                break;
            case 'account':
                $auth = $this->userID && ! $this->keyID ? ALE_AUTH_USER : ALE_AUTH_API;
                break;
            case 'char':
            case 'corp':
                $auth = $this->userID && ! $this->keyID ? ALE_AUTH_CHARACTER : ALE_AUTH_API;
                break;
            default:
                $auth = ALE_AUTH_AVAILABLE;
        }
        return $auth;
    }

    /**
     * Add Credentials to parameters
     *
     * @param array $params            
     * @param int $auth
     *            Credentials level
     */
    protected function addCredentials(array &$params, $auth)
    {
        switch ($auth) {
            case ALE_AUTH_API:
                if ($this->keyID && $this->vCode) {
                    $params['keyID'] = $this->keyID;
                    $params['vCode'] = $this->vCode;
                    if ($this->characterID) {
                        $params['characterID'] = $this->characterID;
                    }
                } else {
                    throw new LogicException('Api call requires API key');
                }
                break;
            case ALE_AUTH_CHARACTER:
                if ($this->characterID) {
                    $params['characterID'] = $this->characterID;
                } else {
                    throw new LogicException('Api call requires characterID');
                }
            case ALE_AUTH_USER:
                if ($this->userID && $this->apiKey) {
                    $params['userID'] = $this->userID;
                    $params['apiKey'] = $this->apiKey;
                } else {
                    throw new LogicException('Api call requires user credentials');
                }
            case ALE_AUTH_NONE:
                break;
            case ALE_AUTH_AVAILABLE:
                if ($this->keyID && $this->vCode) {
                    $params['keyID'] = $this->keyID;
                    $params['vCode'] = $this->vCode;
                } elseif ($this->userID && $this->apiKey) {
                    $params['userID'] = $this->userID;
                    $params['apiKey'] = $this->apiKey;
                }
                if ($this->characterID) {
                    $params['characterID'] = $this->characterID;
                }
                break;
            default:
                throw new InvalidArgumentException('Unknown credentials level');
        }
    }

    public function _retrieveXml(array $context, array $arguments)
    {
        $params = isset($arguments[0]) && is_array($arguments[0]) ? $arguments[0] : array();
        $auth = isset($arguments[1]) ? $arguments[1] : ALE_AUTH_DEFAULT;
        
        if ($auth == ALE_AUTH_DEFAULT) {
            if (! $this->handleAuthSpecialCases($context, $params, $auth)) {
                $auth = $this->getAuth(reset($context), $auth);
            }
        }
        // let's add credentials first, remember kids: ALE_AUTH_DEFAULT is invalid
        $this->addCredentials($params, $auth);
        $arguments[0] = $params;
        
        return parent::_retrieveXml($context, $arguments);
    }

    protected function handleAuthSpecialCases(array $context, array &$params, &$auth)
    {
        if (count($context) != 2) {
            return false;
        }
        $path = strtolower($context[0]) . '/' . strtolower($context[1]);
        switch ($path) {
            case 'eve/characterinfo':
                if ($this->keyID) {
                    $auth = ALE_AUTH_API;
                } else {
                    if ($this->characterID) {
                        $params['characterID'] = $this->characterID;
                        $auth = ALE_AUTH_NONE;
                    } else {
                        throw new LogicException('Api call requires API key or characterID');
                    }
                }
                return true;
            case 'corp/corporationsheet':
                if (isset($params['corporationID'])) {
                    $auth = ALE_AUTH_NONE;
                    return true;
                }
                return false;
            default:
                return false;
        }
    }

    /**
     * Set API key info
     *
     * @param int $keyID            
     * @param string $vCode            
     * @param int $characterID            
     */
    public function setKey($keyID, $vCode, $characterID = null)
    {
        $this->setKeyID($keyID);
        $this->setVerificationCode($vCode);
        $this->setCharacterID($characterID);
    }

    /**
     * Set Key ID
     *
     * @param int $keyID            
     */
    public function setKeyID($keyID)
    {
        $this->keyID = $keyID;
    }

    /**
     * Set verification code
     *
     * @param string $vCode            
     */
    public function setVerificationCode($vCode)
    {
        $this->vCode = $vCode;
    }

    /**
     * Set userID
     *
     * @param int $userID            
     * @deprecated
     *
     */
    public function setUserID($userID)
    {
        // The user ID must be a numeric value.
        if (! is_numeric($userID)) {
            // ERROR: User ID is not numeric.
            throw new UnexpectedValueException("setUserID: userID must be a numeric value.");
        }
        
        // Validation checks out, set the User ID
        $this->userID = $userID;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey            
     * @deprecated
     *
     */
    public function setApiKey($apiKey)
    {
        // The API Key must be a string.
        if (! is_string($apiKey)) {
            // ERROR: Api Key is not a string!!
            throw new UnexpectedValueException("setApiKey: apiKey must be a string value. It is " . getType($apiKey));
        }
        
        // Validation checks out, set the Api Key
        $this->apiKey = $apiKey;
    }

    /**
     * Set CharacterID
     *
     * @param int $characterID            
     */
    public function setCharacterID($characterID = null)
    {
        // The char ID must be a numeric value.
        if (! empty($characterID) && ! is_numeric($characterID)) {
            // ERROR: User ID is not numeric.
            throw new UnexpectedValueException("setCharacterID: characterID must be a numeric value.");
        }
        
        // Validation checks out, set the User ID, if it's empty, set to null.
        if (! empty($characterID)) {
            $this->characterID = $characterID;
        } else {
            $this->characterID = null;
        }
    }

    /**
     * Set API credentials
     *
     * @param int $userID            
     * @param string $apiKey            
     * @param int $characterID            
     * @deprecated
     *
     */
    public function setCredentials($userID, $apiKey, $characterID = null)
    {
        $this->setUserID($userID);
        $this->setApiKey($apiKey);
        $this->setCharacterID($characterID);
    }
}

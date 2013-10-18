<?php
namespace Ale\Test;

use \PHPUnit_Framework_TestCase;
use Ale\EveOnline;
use Ale\Exception\EveMiscellaneousException;

class EveOnlineTest extends PHPUnit_Framework_TestCase
{
	protected function mockCache()
	{
		return $this->getMock("Ale\Cache\Cache", array('__construct', 'setHost', 'setCall', 'store', 'updateCachedUntil', 'retrieve', 'isCached', 'purge'));//, $arguments)
	}
	
	protected function mockCacheEmpty($path, $params, $content, $cachedUntil)
	{
		$cache = $this->mockCache();
		
		$cache->expects($this->at(0))
			->method('setHost')
			->with($this->equalTo('https://api.eveonline.com/'));
		
		$cache->expects($this->at(1))
			->method('setCall')
			->with($this->equalTo($path), $this->equalTo($params));
		
		$cache->expects($this->at(2))
			->method('isCached')
			->with()
			->will($this->returnValue(ALE_CACHE_MISSING));
		
		$cache->expects($this->at(3))
			->method('store')
			->with($this->equalTo($content), $this->equalTo($cachedUntil));
		
		return $cache;
	}
	
	protected function mockRequest() 
	{
		return $this->getMock("Ale\Request\Request", array('__construct', 'query'));//, $arguments)
	}
	
	public function testConstructorConfig()
	{
		$request = $this->mockRequest();
		$cache = $this->mockCache();
		
		$expected  = array(
			'host' => 'https://different-url.com/',
			'suffix' => '.xml',
			'parserClass' => 'SimpleXMLElement' ,
			'serverError' => 'ignore',
			'requestError' => 'ignore',
		);
		
		$config = array(
			'host' => 'https://different-url.com/',
			'suffix' => '.xml',
			'parserClass' => 'SimpleXMLElement' ,
			'serverError' => 'ignore',
			'requestError' => 'ignore',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		
		$this->assertAttributeEquals($expected, 'config', $eveonline);
	}
	
	
	/**
	 * Example of query with no parameters
	 */
	public function testServerStatus()
	{
		$expected = file_get_contents('tests/data/ServerStatus.xml.aspx');
		
		$request = $this->mockRequest();
		$cache = $this->mockCacheEmpty('server/ServerStatus', array(), $expected, '2011-09-30 14:57:38');
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/server/ServerStatus.xml.aspx'))
			->will($this->returnValue($expected));
		
		$actual = $eveonline->server->ServerStatus();
		
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * Query with additional parameters
	 * 
	 */
	public function testCharacterName()
	{
		$expected = file_get_contents('tests/data/CharacterName.xml.aspx');
		
		$request = $this->mockRequest();
		$cache = $this->mockCacheEmpty('eve/CharacterName', array('ids' => '797400947,1188435724'), 
			$expected, '2011-10-30 16:00:06');
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/eve/CharacterName.xml.aspx'),
				$this->equalTo(array('ids' => '797400947,1188435724')))
			->will($this->returnValue($expected));
		
		$actual = $eveonline->eve->CharacterName(array('ids' => '797400947,1188435724'));
		
		$this->assertEquals($expected, $actual);
	}
	
	public function testCorporationSheetPublic()
	{
		$expected = file_get_contents('tests/data/CorporationSheetPublic.xml.aspx');
		
		$request = $this->mockRequest();
		$cache = $this->mockCacheEmpty('corp/CorporationSheet', array('corporationID' => 895898591), 
			$expected, '2011-09-30 22:15:35');
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/corp/CorporationSheet.xml.aspx'),
				$this->equalTo(array('corporationID' => 895898591)))
			->will($this->returnValue($expected));
		
		$actual = $eveonline->corp->CorporationSheet(array('corporationID' => 895898591));
		
		$this->assertEquals($expected, $actual);
	}
	
	public function testCorporationSheetPrivate()
	{
		$expected = file_get_contents('tests/data/CorporationSheetPrivate.xml.aspx');
		
		$request = $this->mockRequest();
		$cache = $this->mockCacheEmpty('corp/CorporationSheet', 
			array('keyID' => 123456, 'vCode' => '0234567890123456789022345678903234567890423456789052345678906234'), 
			$expected, '2011-09-30 22:15:35');
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		$eveonline->setKey(123456, '0234567890123456789022345678903234567890423456789052345678906234');
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/corp/CorporationSheet.xml.aspx'),
				$this->equalTo(array('keyID' => 123456, 'vCode' => '0234567890123456789022345678903234567890423456789052345678906234')))
			->will($this->returnValue($expected));
		
		$actual = $eveonline->corp->CorporationSheet();
		
		$this->assertEquals($expected, $actual);
	}
	
	public function testAPIKeyInfo()
	{
		$expected = file_get_contents('tests/data/APIKeyInfo.xml.aspx');
		
		$request = $this->mockRequest();
		$cache = $this->mockCacheEmpty('account/APIKeyInfo', 
			array('keyID' => 654321, 'vCode' => '0234567890123456789022345678903234567890423456789052345678906234'), 
			$expected, '2011-09-30 16:52:46');
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		$eveonline->setKey(654321, '0234567890123456789022345678903234567890423456789052345678906234');
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/account/APIKeyInfo.xml.aspx'),
				$this->equalTo(array('keyID' => 654321, 'vCode' => '0234567890123456789022345678903234567890423456789052345678906234')))
			->will($this->returnValue($expected));
		
		$actual = $eveonline->account->APIKeyInfo();
		
		$this->assertEquals($expected, $actual);
	}
		
	public function testCharacterSheet()
	{
		$expected = file_get_contents('tests/data/CharacterSheet.xml.aspx');
		
		$request = $this->mockRequest();
		$cache = $this->mockCacheEmpty('char/CharacterSheet', 
			array(
				'keyID' => 654321, 
				'vCode' => '0234567890123456789022345678903234567890423456789052345678906234',
				'characterID' => 1172430985,
				), 
			$expected, '2011-09-30 17:13:38');
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		$eveonline->setKey(654321, '0234567890123456789022345678903234567890423456789052345678906234', 1172430985);
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/char/CharacterSheet.xml.aspx'),
				$this->equalTo(array(
					'keyID' => 654321, 
					'vCode' => '0234567890123456789022345678903234567890423456789052345678906234',
					'characterID' => 1172430985,
				)))
			->will($this->returnValue($expected));
		
		$actual = $eveonline->char->CharacterSheet();
		
		$this->assertEquals($expected, $actual);
	}
	
	public function testCharacterInfoWithApiKey()
	{
		$expected = file_get_contents('tests/data/TestResponse.xml.aspx');
		$request = $this->mockRequest();
		$params = array(
			'keyID' => 654321, 
			'vCode' => '0234567890123456789022345678903234567890423456789052345678906234',
			'characterID' => 1172430985,
		);
		 
		$cache = $this->mockCacheEmpty('eve/CharacterInfo', $params, $expected, '2100-01-01 00:00:00');
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		$eveonline->setKey(654321, '0234567890123456789022345678903234567890423456789052345678906234', 1172430985);
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/eve/CharacterInfo.xml.aspx'),
				$this->equalTo($params))
			->will($this->returnValue($expected));
		
		$actual = $eveonline->eve->CharacterInfo();
		
		$this->assertEquals($expected, $actual);
	}
		
	public function testCharacterInfoWithApiKeyAndAuthSpecified()
	{
		$expected = file_get_contents('tests/data/TestResponse.xml.aspx');
		$request = $this->mockRequest();
		$params = array(
			'keyID' => 654321, 
			'vCode' => '0234567890123456789022345678903234567890423456789052345678906234',
			'characterID' => 1172430985,
		);
		 
		$cache = $this->mockCacheEmpty('eve/CharacterInfo', $params, $expected, '2100-01-01 00:00:00');
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		$eveonline->setKey(654321, '0234567890123456789022345678903234567890423456789052345678906234', 1172430985);
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/eve/CharacterInfo.xml.aspx'),
				$this->equalTo($params))
			->will($this->returnValue($expected));
		
		$actual = $eveonline->eve->CharacterInfo(array(), ALE_AUTH_AVAILABLE);
		
		$this->assertEquals($expected, $actual);
	}
		
	public function testCharacterInfoWithoutApiKey()
	{
		$expected = file_get_contents('tests/data/TestResponse.xml.aspx');
		$request = $this->mockRequest();
		$params = array(
			'characterID' => 1172430985,
		);
		 
		$cache = $this->mockCacheEmpty('eve/CharacterInfo', $params, $expected, '2100-01-01 00:00:00');
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		$eveonline->setCharacterID(1172430985);
	
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/eve/CharacterInfo.xml.aspx'),
				$this->equalTo($params))
			->will($this->returnValue($expected));
		
		$actual = $eveonline->eve->CharacterInfo();
		
		$this->assertEquals($expected, $actual);
	}
	
	/**
	 * @expectedException Ale\Exception\EveMiscellaneousException
	 */
	public function testErrorResponse()
	{
		$expected = file_get_contents('tests/data/ErrorResponse.xml.aspx');
		$request = $this->mockRequest();
		$params = array(
			'keyID' => 123456, 
			'vCode' => '0234567890123456789022345678903234567890423456789052345678906234'
		);
		 
		$cache = $this->mockCache();
		
		$cache->expects($this->at(3))
			->method('updateCachedUntil')
			->with($this->equalTo('2011-10-06 12:34:15'));
		$config  = array(
			'parserClass' => 'string',
		);
		
		$eveonline = new EveOnline($request, $cache, $config);
		$eveonline->setKey(123456, '0234567890123456789022345678903234567890423456789052345678906234');
	
		
		$request->expects($this->once())
			->method('query')
			->with($this->equalTo('https://api.eveonline.com/char/CharacterSheet.xml.aspx'),
				$this->equalTo($params))
			->will($this->returnValue($expected));
		
		try {
			$actual = $eveonline->char->CharacterSheet();
		}
		catch (EveMiscellaneousException $e) {
			$this->assertEquals(901, $e->getCode());
			$this->assertEquals('Web site database temporarily disabled.', $e->getMessage());
			$this->assertEquals('2011-10-06 12:34:15', $e->getCachedUntil());
			
			$this->assertAttributeEquals('2011-10-06 12:34:15', 'cachedUntil', $eveonline);
			throw $e;
		}
	}
}

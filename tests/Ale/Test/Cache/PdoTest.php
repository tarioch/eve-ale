<?php

/*

INSERT INTO alecache (host, path, params, content, cachedUntil) VALUES
('setCall', 'fooBar', '2fb8f40115dd1e695cbe23d4f97ce5b1fb697eee', 'Array ( [foo] => bar )', '2000-01-01 00:00:00');
INSERT INTO alecache (host, path, params, content, cachedUntil) VALUES
('isCached', 'cacheExpired', 'da39a3ee5e6b4b0d3255bfef95601890afd80709', '', '2000-01-01 00:00:00');
INSERT INTO alecache (host, path, params, content, cachedUntil) VALUES
('isCached', 'cachedForever', 'da39a3ee5e6b4b0d3255bfef95601890afd80709', '', '2100-01-01 00:00:00');
INSERT INTO alecache (host, path, params, content, cachedUntil) VALUES
('purge', 'purgeExpired', 'da39a3ee5e6b4b0d3255bfef95601890afd80709', '', '2000-01-01 00:00:00');
INSERT INTO alecache (host, path, params, content, cachedUntil) VALUES
('purge', 'purgeAll', 'da39a3ee5e6b4b0d3255bfef95601890afd80709', '', '2100-01-01 00:00:00');
INSERT INTO alecache (host, path, params, content, cachedUntil) VALUES
('retrieve', 'retrieveContent', 'da39a3ee5e6b4b0d3255bfef95601890afd80709', 'content', '2100-01-01 00:00:00');
INSERT INTO alecache (host, path, params, content, cachedUntil) VALUES
('updateCachedUntil', 'cacheExpired', 'da39a3ee5e6b4b0d3255bfef95601890afd80709', 'content', '2000-01-01 00:00:00');
INSERT INTO alecache (host, path, params, content, cachedUntil) VALUES
('store', 'update', 'da39a3ee5e6b4b0d3255bfef95601890afd80709', 'old content', '2000-01-01 00:00:00');

 */

namespace Ale\Test\Cache;

use \PHPUnit_Framework_TestCase;
use \PDO;
use \stdClass;
use \DateTimeZone;

class PdoTest extends PHPUnit_Framework_TestCase
{
	protected $pdo;
	
	/** AleCachePdo */
	protected $cache;
	
	public function setUp()
	{
		if (file_exists('tests/data/pdo_test.db')) {
			unlink('tests/data/pdo_test.db');
		}
		copy('tests/data/pdo.db', 'tests/data/pdo_test.db');
		$this->pdo = new PDO('sqlite:tests/data/pdo_test.db');
		$config = array();
		$config['db'] = $this->pdo;
		$this->cache = new \Ale\Cache\Pdo($config); 
		
	}
	
	public function testSetCall()
	{
		$this->cache->setHost('setCall');
		$this->cache->setCall('fooBar', array('foo' => 'bar'));
		$expected = new \stdClass();
		$expected->host = 'setCall';
		$expected->path = 'fooBar';
		$expected->params = '2fb8f40115dd1e695cbe23d4f97ce5b1fb697eee';
		$expected->content = 'Array ( [foo] => bar )';
		$expected->cachedUntil = '2000-01-01 00:00:00';
		$this->assertAttributeEquals($expected, 'row', $this->cache);
	}
	
	public function testIsCached()
	{
		$this->cache->setHost('isCached');
		$this->cache->setCall('cacheMissing', array());
		$isCached = $this->cache->isCached();
		$this->assertEquals(ALE_CACHE_MISSING, $isCached);
		
		$this->cache->setCall('cacheExpired', array());
		$isCached = $this->cache->isCached();
		$this->assertEquals(ALE_CACHE_EXPIRED, $isCached);
		
		$this->cache->setCall('cachedForever', array());
		$isCached = $this->cache->isCached();
		$this->assertEquals(ALE_CACHE_CACHED, $isCached);
	}
	
	public function testPurge()
	{
		$this->cache->setHost('purge');
		
		$this->cache->purge();
		$this->cache->setCall('purgeExpired', array());
		$isCached = $this->cache->isCached();
		$this->assertEquals(ALE_CACHE_MISSING, $isCached);
		
		$this->cache->setCall('purgeAll', array());
		$isCached = $this->cache->isCached();
		$this->assertNotEquals(ALE_CACHE_MISSING, $isCached);
		
		$this->cache->purge(true);
		$this->cache->setCall('purgeAll', array());
		$isCached = $this->cache->isCached();
		$this->assertEquals(ALE_CACHE_MISSING, $isCached);
	}
	
	public function testRetrieve()
	{
		$this->cache->setHost('retrieve');
		$this->cache->setCall('retrieveMissing', array());
		$content = $this->cache->retrieve();
		$this->assertNull($content);
		
		$this->cache->setCall('retrieveContent', array());
		$content = $this->cache->retrieve();
		$this->assertEquals('content', $content);
	}
	
	public function testUpdateCachedUntil()
	{
		$this->cache->setHost('updateCachedUntil');
		$this->cache->setCall('cacheExpired', array());
		$this->cache->updateCachedUntil('2100-01-01 00:00:00');
		$isCached = $this->cache->isCached();
		$this->assertEquals(ALE_CACHE_CACHED, $isCached);
		$this->cache->setCall('cacheExpired', array());
		$isCached = $this->cache->isCached();
		$this->assertEquals(ALE_CACHE_CACHED, $isCached);
	}
	
	public function testStore()
	{
		$this->cache->setHost('store');
		$this->cache->setCall('insert', array());
		$this->assertAttributeEmpty('row', $this->cache);
		$this->cache->store('content', '2000-01-01 00:00:00');
		$this->cache->setCall('insert', array());
		$this->assertAttributeNotEmpty('row', $this->cache);
		
		$this->cache->setCall('update', array());
		$this->assertAttributeNotEmpty('row', $this->cache);
		$this->cache->store('new content', '2100-01-01 00:00:00');
		$this->cache->setCall('update', array());

		$expected = new stdClass();
		$expected->host = 'store';
		$expected->path = 'update';
		$expected->params = 'da39a3ee5e6b4b0d3255bfef95601890afd80709';
		$expected->content = 'new content';
		$expected->cachedUntil = '2100-01-01 00:00:00';
		$this->assertAttributeEquals($expected, 'row', $this->cache);
	}
	
	public function tearDown()
	{
		unset($this->cache);
		unset($this->pdo);
		unlink('tests/data/pdo_test.db');
	}
}

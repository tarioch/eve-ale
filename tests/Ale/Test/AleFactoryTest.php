<?php
namespace Ale\Test;

use \PHPUnit_Framework_TestCase;
use Ale\AleFactory;

class AleFactoryTest extends PHPUnit_Framework_TestCase
{
	public function testGetEVEOnlineDotConfig()
	{
		$config = array(
			'main.host' => 'https://api.eveonline.com/',
			'main.suffix' => '.xml.aspx',
			'main.parserClass' => 'string' ,
			'main.serverError' => 'ignore',
			'main.requestError' => 'throwException',
		 
			'cache.class' => 'File',
			'cache.rootdir' => '/root/dir',
		
			'request.class' => 'Fsock'
		);
		
		$expectedConfig = array(
			'host' => 'https://api.eveonline.com/',
			'suffix' => '.xml.aspx',
			'parserClass' => 'string' ,
			'serverError' => 'ignore',
			'requestError' => 'throwException',
		);
		
		$actual = AleFactory::getEveOnline($config, true);
		$this->assertInstanceOf('Ale\EveOnline', $actual);
		$this->assertAttributeInstanceOf('Ale\Cache\File', 'cache', $actual);
		$this->assertAttributeInstanceOf('Ale\Request\Fsock', 'request', $actual);
		$this->assertAttributeEquals($expectedConfig, 'config', $actual);
	}

	public function testGetEVEOnlineConfigIni()
	{
		$config = array(
			'cache.class' => 'Dummy',
		);
		$actual = AleFactory::getEveOnline($config, true);
		$this->assertInstanceOf('Ale\EveOnline', $actual);
		$this->assertAttributeInstanceOf('Ale\Cache\Dummy', 'cache', $actual);
		$this->assertAttributeInstanceOf('Ale\Request\Curl', 'request', $actual);
		
	}

}

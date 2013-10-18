<?php
namespace Ale\Test\Request;

use \PHPUnit_Framework_TestCase;
use Ale\Request\Curl;

class CurlTest extends PHPUnit_Framework_TestCase
{
	public function testQueryApiEveonlineCom()
	{
		return;
		$config = array(
			'certificate' => 'eveonline.crt',
		);
		$curl = new Curl($config);
		$xml = $curl->query('https://api.eveonline.com/api/CallList.xml.aspx');
		$this->assertNotSame(false, strpos($xml, '<eveapi'));
	}
}

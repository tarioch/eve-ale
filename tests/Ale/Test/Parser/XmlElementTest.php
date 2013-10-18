<?php
namespace Ale\Test\Parser;

use \PHPUnit_Framework_TestCase;
use Ale\Parser\XmlElement;

class XmlElementTest extends PHPUnit_Framework_TestCase
{
	public function testCalendarEventAttendees()
	{
		$data = file_get_contents('tests/data/CalendarEventAttendees.xml.aspx');
		
		$parser = new XmlElement($data);
		
		$actual = $parser->toArray();
		
		$expected = Array(
			'version' => '2',
			'currentTime' => '2011-10-01 18:03:43',
			'result' => Array(
				'eventAttendees' => Array(
					'21' => Array(
						'14' => Array(
							'eventID' => '21',
							'characterID' => '14',
							'characterName' => 'Char 14',
							'response' => 'Accepted',
						),
						'67' => Array(
							'eventID' => '21',
							'characterID' => '67',
							'characterName' => 'Char 67',
							'response' => 'Tentative',
						),
					),
					'35' => Array(
						'15' => Array(
							'eventID' => '35',
							'characterID' => '15',
							'characterName' => 'Char 35',
							'response' => 'Declined',
						),
						'78' => Array(
							'eventID' => '35',
							'characterID' => '78',
							'characterName' => 'Char 78',
							 'response' => 'Undecided',
						),
					),
				),
			),
			'cachedUntil' => '2011-10-01 18:03:43',
		);
		$this->assertEquals($expected, $actual);

		$attendeeCount = 0;
		$before = $this->getNumAssertions();
		foreach ($parser->result->eventAttendees as $evenID => $attendees) {
			foreach ($attendees as $characterID => $attendee) {
				$this->assertEquals($expected['result']['eventAttendees'][$evenID][$characterID], 
					$attendee->toArray());
				$attendeeCount++;
			}
		}
		$after = $this->getNumAssertions();
		$this->assertEquals(4, $attendeeCount);
	}
	
	public function testKillLog()
	{
		$data = file_get_contents('tests/data/KillLog.xml.aspx');
		
		$parser = new XmlElement($data);
		
		$actual = $parser->toArray();
		$expected = array(
			'version' => '2',
			'currentTime' => '2011-10-01 17:49:48',
			'result' => array(
				'kills' => array(
					'20284618' => array(
						'killID' => '20284618',
						'solarSystemID' => '30003729',
						'killTime' => '2011-09-25 14:11:00',
						'moonID' => '0',
						'victim' => array(
							'characterID' => '1795582464',
							'characterName' => 'Steve1596',
							'corporationID' => '661581003',
							'corporationName' => 'The Dude\'s Interstellar Enterprizes',
							'allianceID' => '99000331',
							'allianceName' => 'Quixotic Hegemony',
							'factionID' => '0',
							'factionName' => '',
							'damageTaken' => '1041',
							'shipTypeID' => '11377',
						),
						'attackers' => array(
							0 => array(
								'characterID' => '765371737',
								'characterName' => 'Lucious Desire',
								'corporationID' => '1022730827',
								'corporationName' => 'N.F.H.P.',
								'allianceID' => '99000678',
								'allianceName' => 'Eternal Evocations',
								'factionID' => '0',
								'factionName' => '',
								'securityStatus' => '0.148505265335588',
								'damageDone' => '1041',
								'finalBlow' => '1',
								'weaponTypeID' => '2913',
								'shipTypeID' => '24702',
							),
						),
						'items' => array(
							0 => array(
								'typeID' => '1952',
								'flag' => '0',
								'qtyDropped' => '1',
								'qtyDestroyed' => '0',
							),
							1 => array(
								'typeID' => '2510',
								'flag' => '5',
								'qtyDropped' => '0',
								'qtyDestroyed' => '2140'
							),
							2 => array(
								'typeID' => '8117',
								'flag' => '0',
								'qtyDropped' => '0',
								'qtyDestroyed' => '3',
							),
							3 => array(
								'typeID' => '3244',
								'flag' => '0',
								'qtyDropped' => '1',
								'qtyDestroyed' => '0',
							),
							4 => array(
								'typeID' => '11578',
								'flag' => '0',
								'qtyDropped' => '0',
								'qtyDestroyed' => '1',
							),
						),
					),
				),
			),
			'cachedUntil' => '2011-10-01 18:46:48',
		);
		$this->assertEquals($expected, $actual);
		$this->assertEquals('20284618', $parser->result->kills['20284618']->killID);
		$this->assertEquals('1795582464', $parser->result->kills['20284618']->victim->characterID);
		$this->assertEquals('765371737', $parser->result->kills['20284618']->attackers[0]->characterID);
		$this->assertEquals('1952', $parser->result->kills['20284618']->items[0]->typeID);
		$this->assertEquals('11578', $parser->result->kills['20284618']->items[4]->typeID);
		
		$victimAttributes = $parser->result->kills['20284618']->victim->attributes();
		(string) $victimAttributes['characterID'];
		$this->assertEquals('1795582464', (string) $victimAttributes['characterID']);
	}
	
	public function testAssetList()
	{
		$data = file_get_contents('tests/data/AssetList.xml.aspx');
		
		$parser = new XmlElement($data);
		$expect = array(
			'1510902232', '1510902214', '1510902220', 
			'1742231450', '1742231291', '1742231419', 
			'1983357903', '1983357904'
		);
		$i = 0;
		foreach($parser->result->assets as $asset) {
			$this->assertEquals($expect[$i], $asset->itemID);
			$i += 1;
			if (isset($asset->contents)) {
				foreach($asset->contents as $content) {
					$this->assertEquals($expect[$i], $content->itemID);
					$i += 1;
				}
			}
		}
		$this->assertEquals('1510902232', $parser->result->assets['1510902232']->itemID);		
		$this->assertEquals('1510902214', $parser->result->assets['1510902232']->contents['1510902214']->itemID);
		$this->assertEquals('1510902220', $parser->result->assets['1510902232']->contents['1510902220']->itemID);
		$this->assertEquals('1742231450', $parser->result->assets['1742231450']->itemID);		
		$this->assertEquals('1742231291', $parser->result->assets['1742231450']->contents['1742231291']->itemID);		
		$this->assertEquals('1742231419', $parser->result->assets['1742231450']->contents['1742231419']->itemID);		
		$this->assertEquals('1983357903', $parser->result->assets['1983357903']->itemID);		
		$this->assertEquals('1983357904', $parser->result->assets['1983357904']->itemID);		
	}
}

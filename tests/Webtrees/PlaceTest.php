<?php

namespace MyArtJaub\Tests\Webtrees;

use \Mockery as m;
use \MyArtJaub\Webtrees\Place;

/**
 * Test harness for the class \MyArtJaub\Webtrees\Place
 */
class PlaceTest extends \PHPUnit_Framework_TestCase
{	
	private static $tree_mock;
	
	private static $place1;
	private static $place1_string = 'place1';	
	
	private static $place2;
	private static $place2_string = 'place1, place2';
	
	private static $place3;
	private static $place3_string = 'place1,place3';	

	private static $place4;
	private static $place4_string = 'place1,place3, place4, place5';
	
	public static function setUpBeforeClass()
	{
		self::$tree_mock = m::mock('\Fisharebest\Webtrees\Tree');
		self::$tree_mock->shouldReceive('getNameHtml')->andReturn('MOCK TREE');
		
		self::$place1 = new \Fisharebest\Webtrees\Place(self::$place1_string, self::$tree_mock);
		self::$place2 = new \Fisharebest\Webtrees\Place(self::$place2_string, self::$tree_mock);
		self::$place3 = new \Fisharebest\Webtrees\Place(self::$place3_string, self::$tree_mock);
		self::$place4 = new \Fisharebest\Webtrees\Place(self::$place4_string, self::$tree_mock);
	}
		
	public function testClassExists() {
		$this->assertTrue(class_exists('\MyArtJaub\Webtrees\Place'));
	}
	
	/**
	 * @expectedException			PHPUnit_Framework_Error
	 * @expectedExceptionMessage 	Argument 1 passed to
	 */
	public function testConstructorWithNullPlace() {
		new Place(null);
	}
		
	/**
	 * @expectedException			PHPUnit_Framework_Error
	 * @expectedExceptionMessage 	Argument 2 passed to
	 */
	public function testGetInstanceWithNullTreeArgument() {
		$this->assertNull(Place::getIntance(null, null));
	}
	
	public function testGetInstanceWithInvalidArguments() {
		$this->assertNull(Place::getIntance(null, self::$tree_mock));
		
		$this->assertNull(Place::getIntance('', self::$tree_mock));
		
		$this->assertNull(Place::getIntance(new \stdClass(), self::$tree_mock));
	}
	
	public function testGetInstanceWithValidArguments() {
		$this->assertInstanceOf('\MyArtJaub\Webtrees\Place', Place::getIntance('place1', self::$tree_mock));		
	}
	
	public function testGetDerivedPlace() {
		$dplace1 = new \MyArtJaub\Webtrees\Place(self::$place1);
		
		$this->assertInstanceOf('\Fisharebest\Webtrees\Place', $dplace1->getDerivedPlace());
		$this->assertEquals(self::$place1_string, $dplace1->getDerivedPlace()->getGedcomName());	
		
		$dplace2 = new \MyArtJaub\Webtrees\Place(self::$place2);
		
		$this->assertInstanceOf('\Fisharebest\Webtrees\Place', $dplace2->getDerivedPlace());
		$this->assertEquals(self::$place2_string, $dplace2->getDerivedPlace()->getGedcomName());
		
		$dplace3 = new \MyArtJaub\Webtrees\Place(self::$place3);
		
		$this->assertInstanceOf('\Fisharebest\Webtrees\Place', $dplace3->getDerivedPlace());
		$this->assertEquals(self::$place3_string, $dplace3->getDerivedPlace()->getGedcomName());
	}

	public function testHtmlFormattedName() {
		$dplace1 = new \MyArtJaub\Webtrees\Place(self::$place1);		
		$this->assertEquals('place1', $dplace1->htmlFormattedName('%1'));
		$this->assertEquals('place1 ', $dplace1->htmlFormattedName('%1 %2'));
		$this->assertEquals('place1 ', $dplace1->htmlFormattedName('%1 %5'));
		$this->assertEquals('<a href="'.self::$place1->getURL().'">place1</a>', $dplace1->htmlFormattedName('%1', true));
		
		$dplace2 = new \MyArtJaub\Webtrees\Place(self::$place2);		
		$this->assertEquals('place1', $dplace2->htmlFormattedName('%1'));
		$this->assertEquals('place1 place2', $dplace2->htmlFormattedName('%1 %2'));
		$this->assertEquals('place1 place2 ', $dplace2->htmlFormattedName('%1 %2 %3'));
		$this->assertEquals('place1 (place2)', $dplace2->htmlFormattedName('%1 (%2)'));
		
		$dplace3 = new \MyArtJaub\Webtrees\Place(self::$place3);		
		$this->assertEquals('place1', $dplace3->htmlFormattedName('%1'));
		$this->assertEquals('place1 place3', $dplace3->htmlFormattedName('%1 %2'));
		
		$dplace4 = new \MyArtJaub\Webtrees\Place(self::$place4);
		$this->assertEquals('place1', $dplace4->htmlFormattedName('%1'));
		$this->assertEquals('place1 place3', $dplace4->htmlFormattedName('%1 %2'));
		$this->assertEquals('place1, [place3] place5', $dplace4->htmlFormattedName('%1, [%2] %4'));
		$this->assertEquals('<a href="'.self::$place4->getURL().'">place1, </a>', $dplace4->htmlFormattedName('%1, %5', true));
	}
	
	
}
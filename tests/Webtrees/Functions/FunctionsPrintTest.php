<?php

namespace MyArtJaub\Tests\Webtrees\Functions;

use \Mockery as m;

use \Fisharebest\Webtrees\I18N;
use \MyArtJaub\Webtrees\Functions\FunctionsPrint;
use Fisharebest\Webtrees\Date;

/**
 * Test harness for the class \MyArtJaub\Webtrees\Functions\FunctionsPrint
 */
class FunctionsPrintTest extends \PHPUnit_Framework_TestCase {
		
	private static $tree_mock;
	
	/** @var \Fisharebest\Webtrees\Place */
	private static $place1;
	private static $place1_string = 'place1';
	
	/** @var \Fisharebest\Webtrees\Place */
	private static $place2;
	private static $place2_string = 'place1, place2';
	
	/** @var \Fisharebest\Webtrees\Place */
	private static $place3;
	private static $place3_string = 'place1,place3';
	
	/** @var \Fisharebest\Webtrees\Place */
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
	
	public function testGetListFromArray () {
		$this->assertEquals('', FunctionsPrint::getListFromArray(array()));
		$this->assertEquals('one', FunctionsPrint::getListFromArray(array('one')));
		$this->assertEquals('one and two', FunctionsPrint::getListFromArray(array('one', 'two')));
		$this->assertEquals('one, two and three', FunctionsPrint::getListFromArray(array('one', 'two', 'three')));		
	}
	
	public function testHtmlFactPlaceIcon() {	
		$this->markTestIncomplete('testHtmlFactPlaceIcon not implemented');
	}
	
	public function testHtmlListCloud() {
		$html = '<div class="tag_cloud">'.
				'<a style="font-size:200%" href="item1.htm">Item1</a>'.				
				'<a style="font-size:75%" href="item2.htm">Item2</a>'.
				'<a style="font-size:75%" href="item3.htm">Item3</a>'.
				'</div>';
		
		$html2 = '<div class="tag_cloud">'.
				'<a style="font-size:200%" href="item1.htm"><span dir="auto">Item1</span> (10)</a>'.
				'<a style="font-size:75%" href="item2.htm"><span dir="auto">Item2</span> (5)</a>'.
				'<a style="font-size:75%" href="item3.htm"><span dir="auto">Item3</span> (5)</a>'.
				'</div>';
		
		$items = array(
			array('text' => 'Item1', 'count' => 10, 'url' => 'item1.htm'),
			array('text' => 'Item2', 'count' => 5, 'url' => 'item2.htm'),
			array('text' => 'Item3', 'count' => 5, 'url' => 'item3.htm')
		);
		
		$this->assertEquals($html, FunctionsPrint::htmlListCloud($items, false));
		$this->assertEquals($html2, FunctionsPrint::htmlListCloud($items, true));
		
		$html3 = '<div class="tag_cloud">'.
				'<a style="font-size:150%" href="item1.htm">Item1</a>'.
				'<a style="font-size:150%" href="item2.htm">Item2</a>'.
				'<a style="font-size:150%" href="item3.htm">Item3</a>'.
				'</div>';
		
		$items3 = array(
				array('text' => 'Item1', 'count' => 10, 'url' => 'item1.htm'),
				array('text' => 'Item2', 'count' => 10, 'url' => 'item2.htm'),
				array('text' => 'Item3', 'count' => 10, 'url' => 'item3.htm')
		);
		$this->assertEquals($html3, FunctionsPrint::htmlListCloud($items3, false));
	}
	
	public function testHtmlPlacesCloud() {
		$html2 = '<div class="tag_cloud">'.
				'<a style="font-size:200%" href="'.self::$place1->getURL().'"><span dir="auto">place1 ()</span> (10)</a>'.
				'<a style="font-size:75%" href="'.self::$place2->getURL().'"><span dir="auto">place1 (place2)</span> (5)</a>'.
				'<a style="font-size:75%" href="'.self::$place4->getURL().'"><span dir="auto">place1 (place3)</span> (5)</a>'.
				'</div>';
	
		$items = array(
			self::$place1_string => 10,
			self::$place2_string => 5,
			self::$place4_string => 5
		);
	
		$this->assertEquals($html2, FunctionsPrint::htmlPlacesCloud($items, true, self::$tree_mock));
	}
	
	public function testHtmlIndividualForList() {
		$this->markTestIncomplete('testHtmlIndividualForList not implemented');
	}
	
	public function testFormatFactDateShort() {
		$this->markTestIncomplete('testFormatFactDateShort not implemented');
		
	}
	
	public function testFormatFactPlaceShort() {
		$this->markTestIncomplete('testFormatFactPlaceShort not implemented');		
	}
	
	/**
	 * @expectedException			PHPUnit_Framework_Error
	 * @expectedExceptionMessage 	Argument 1 passed to
	 */
	public function testFormatSosaNumberWithNullParameter() {
		FunctionsPrint::formatSosaNumbers(null);
	}
	
	public function testFormatSosaNumbers() {
		$this->assertEquals('', FunctionsPrint::formatSosaNumbers(array()));
		$this->assertEquals('', FunctionsPrint::formatSosaNumbers(array(), 1));
		$this->assertEquals('', FunctionsPrint::formatSosaNumbers(array(), 1, 'small'));
		$this->assertEquals('', FunctionsPrint::formatSosaNumbers(array(), 2));
		$this->assertEquals('', FunctionsPrint::formatSosaNumbers(array(), 2, 'small'));
		$this->assertEquals('', FunctionsPrint::formatSosaNumbers(array(), 3));
		
		$sosa_array = array(15 => 4, 250 => 8, 1583 => 11);
		
		$html = '<i class="icon-maj-sosa_{size}" title="'.I18N::translate('Sosa').'"></i>';		
		$this->assertEquals(str_replace('{size}', 'small', $html), FunctionsPrint::formatSosaNumbers($sosa_array));
		$this->assertEquals(str_replace('{size}', 'small', $html), FunctionsPrint::formatSosaNumbers($sosa_array, 1));
		$this->assertEquals(str_replace('{size}', 'small', $html), FunctionsPrint::formatSosaNumbers($sosa_array, 1, 'small'));
		$this->assertEquals(str_replace('{size}', 'large', $html), FunctionsPrint::formatSosaNumbers($sosa_array, 1, 'large'));
		$this->assertEquals(str_replace('{size}', 'random', $html), FunctionsPrint::formatSosaNumbers($sosa_array, 1, 'random'));
		
		$html2 = '<i class="icon-maj-sosa_{size}" title="'.I18N::translate('Sosa').'"></i>&nbsp;<strong>15&nbsp;'.I18N::translate('(G%s)', 4).'</strong> - '.
			'<i class="icon-maj-sosa_{size}" title="'.I18N::translate('Sosa').'"></i>&nbsp;<strong>250&nbsp;'.I18N::translate('(G%s)', 8).'</strong> - '.
			'<i class="icon-maj-sosa_{size}" title="'.I18N::translate('Sosa').'"></i>&nbsp;<strong>1583&nbsp;'.I18N::translate('(G%s)', 11).'</strong>';
		$this->assertEquals(str_replace('{size}', 'small', $html2), FunctionsPrint::formatSosaNumbers($sosa_array, 2));
		$this->assertEquals(str_replace('{size}', 'large', $html2), FunctionsPrint::formatSosaNumbers($sosa_array, 2, 'large'));
		$this->assertEquals(str_replace('{size}', 'random', $html2), FunctionsPrint::formatSosaNumbers($sosa_array, 2, 'random'));
	}
	
	public function testFormatIsSourcedIcon() {
		$this->markTestIncomplete('testFormatIsSourcedIcon not implemented');		
	}	

	public function testIsDateWithinChartRange() {	    
	    $this->assertTrue(FunctionsPrint::isDateWithinChartsRange(new Date('01 JUN 1835')));
	    $this->assertFalse(FunctionsPrint::isDateWithinChartsRange(new Date('01 JUN 1335')));
	    $this->assertFalse(FunctionsPrint::isDateWithinChartsRange(new Date('01 JUN 3535')));
	}
	
}

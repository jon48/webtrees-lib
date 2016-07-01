<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Functions
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Functions;

use Fisharebest\Webtrees\GedcomTag;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Individual;
use MyArtJaub\Webtrees\Place;
use Fisharebest\Webtrees\Date;


/**
 * Additional functions to display information
 * 
 * @todo snake_case
 */
class FunctionsPrint {

	/**
	 * Get an array converted to a list. For example
	 * array("red", "green", "yellow", "blue") => "red, green, yellow and blue"
	 *
	 * @param array $array Array to convert
	 * @return string List of elements
	 */
	static public function getListFromArray(array $array) {
		$n=count($array);
		switch ($n) {
			case 0:
				return '';
			case 1:
				return $array[0];
			default:
				return implode(
						/* I18N: list separator */ I18N::translate(', '), 
						array_slice($array, 0, $n-1)
					) .
					/* I18N: last list separator, " and " in English, " et " in French  */ I18N::translate(' and ') . 
					$array[$n-1];
		}
	}

	/**
	 * Return HTML code to include a flag icon in facts description
	 *
	 * @param \Fisharebest\Webtrees\Fact $fact Fact record
	 * @param \MyArtJaub\Webtrees\Map\MapProviderInterface $mapProvider Map Provider
	 * @return string HTML code of the inserted flag
	 */
	public static function htmlFactPlaceIcon(
			\Fisharebest\Webtrees\Fact $fact,
			\MyArtJaub\Webtrees\Map\MapProviderInterface $mapProvider
	) {
		$html='';
		if($place = $fact->getPlace()) {
			$iconPlace= $mapProvider->getPlaceIcon($place);	
			if($iconPlace && strlen($iconPlace) > 0){
				$html.=	'<div class="fact_flag">'. self::htmlPlaceIcon($place, $iconPlace, 50). '</div>';
			}
		}
		return $html;
	}
	
	/**
	 * Return HTML code to include a flag icon
	 * 
	 * @param \Fisharebest\Webtrees\Place $place
	 * @param string $icon_path
	 * @param number $size
	 * @return string HTML code of the inserted flag
	 */
	public static function htmlPlaceIcon(\Fisharebest\Webtrees\Place $place, $icon_path , $size = 50) {
	    return '<img class="flag_gm_h'. $size . '" src="' . $icon_path . '" title="' . $place->getGedcomName() . '" alt="' . $place->getGedcomName() . '" />';
	}
	
	/**
	 * Returns HTML code to display a tag cloud from a list.
	 * List must be a list of arrays (one for each item to display) containing the following parameters:
	 * 		- "text" : text to display
	 * 		- "count" : count of items
	 * 		- "url"	: url to the item
	 *
	 * @param array $list Array of items to display in the cloud
	 * @param bool $totals Display totals for an items
	 * @return string Tag Cloud HTML Code
	 */
	public static function htmlListCloud($list, $totals) {
		$minimum = PHP_INT_MAX;
		$maximum = 1;
		foreach ($list as $item => $params) {
			if(array_key_exists('count', $params)) {
				$maximum = max($maximum, $params['count']);
				$minimum = min($minimum, $params['count']);
			}
		}
		$html = '';
		foreach ($list as $item => $params) {
			$text = array_key_exists('text', $params) ? $params['text'] : '';
			$count = array_key_exists('count', $params) ? $params['count'] : 0;
			$url = array_key_exists('url', $params) ? $params['url'] : '';
			
			if ($maximum === $minimum) {			
				// All items occur the same number of times
					$size = 150.0;
			} else {
				$size = 75.0 + 125.0 * ($count - $minimum) / ($maximum - $minimum);
			}
			
			$html .= '<a style="font-size:' . $size . '%" href="' . $url . '">';
			if ($totals) {
				$html .= I18N::translate('%1$s (%2$s)', '<span dir="auto">' . $text . '</span>', I18N::number($count));
			} else {
				$html .= $text;
			}
			$html .= '</a>';
		}
		return '<div class="tag_cloud">' . $html . '</div>';
	}
	

	/**
	 * Returns HTML code to include a place cloud
	 *
	 * @param array $places Array of places to display in the cloud
	 * @param bool $totals Display totals for a place
	 * @param \Fisharebest\Webtrees\Tree $tree Tree
	 * @return string Place Cloud HTML Code
	 */
	public static function htmlPlacesCloud($places, $totals, Tree $tree) {
		$items = array();
		
		foreach ($places as $place => $count) {
			/** var \MyArtJaub\Webtrees\Place */
			$dplace = Place::getIntance($place, $tree);
			$items[] = array(
				'text' => $dplace->htmlFormattedName('%1 (%2)'),
				'count' => $count,
				'url' => $dplace->getDerivedPlace()->getURL()
			);
		}
		
		return self::htmlListCloud($items, $totals);
	}

	/**
	 * Return HTML Code to display individual in non structured list (e.g. Patronymic Lineages)
	 *
	 * @param \Fisharebest\Webtrees\Individual $individual Individual to print
	 * @param bool $isStrong Bolden the name ?
	 * @return string HTML Code for individual item
	 */
	public static function htmlIndividualForList(\Fisharebest\Webtrees\Individual $individual, $isStrong = true){
		$html = '';
		$tag = 'em';
		if($isStrong) $tag = 'strong';
		if($individual && $individual->canShow()){
			$dindi = new Individual($individual);
			$html = $individual->getSexImage();
			$html .= '<a class="list_item" href="'.
			$individual->getHtmlUrl().
				'" title="'.
			I18N::translate('Informations for individual %s', $individual->getXref()).
				'">';
			$html .= '<'.$tag.'>'.$individual->getFullName().'</'.$tag.'>&nbsp;('.$individual->getXref().')&nbsp;';
			$html .= FunctionsPrint::formatSosaNumbers($dindi->getSosaNumbers(), 1, 'small');
			$html .= '&nbsp;<span><small><em>'.$dindi->formatFirstMajorFact(WT_EVENTS_BIRT, 10).'</em></small></span>';
			$html .= '&nbsp;<span><small><em>'.$dindi->formatFirstMajorFact(WT_EVENTS_DEAT, 10).'</em></small></span>';
			$html .= '</a>';
		}
		else {
			$html .= '<span class=\"list_item\"><'.$tag.'>' . I18N::translate('Private') . '</'.$tag.'></span>';
		}
		return $html;
	}

	/**
	 * Format date to display short (just years)
	 *
	 * @param \Fisharebest\Webtrees\Fact $eventObj Fact to display date
	 * @param boolean $anchor option to print a link to calendar
	 * @return string HTML code for short date
	 */
	public static function formatFactDateShort(\Fisharebest\Webtrees\Fact $fact, $anchor=false) {
		global $SEARCH_SPIDER;

		$html='';
		$date = $fact->getDate();
		if($date->isOK()){
			$html.=' '.$date->Display($anchor && !$SEARCH_SPIDER, '%Y');
		}
		else{
			// 1 DEAT Y with no DATE => print YES
			// 1 BIRT 2 SOUR @S1@ => print YES
			// 1 DEAT N is not allowed
			// It is not proper GEDCOM form to use a N(o) value with an event tag to infer that it did not happen.
			$factdetail = explode(' ', trim($fact->getGedcom()));
			if (isset($factdetail) && (count($factdetail) == 3 && strtoupper($factdetail[2]) == 'Y') || (count($factdetail) == 4 && $factdetail[2] == 'SOUR')) {
				$html.=I18N::translate('yes');
			}
		}
		return $html;
	}

	/**
	 * Format fact place to display short
	 *
	 * @param \Fisharebest\Webtrees\Fact $eventObj Fact to display date
	 * @param string $format Format of the place
	 * @param boolean $anchor option to print a link to placelist
	 * @return string HTML code for short place
	 */
	public static function formatFactPlaceShort(\Fisharebest\Webtrees\Fact $fact, $format, $anchor=false){
		$html='';
		
		if ($fact === null) return $html;
		$place = $fact->getPlace();
		if($place){
			$dplace = new Place($place);
			$html .= $dplace->htmlFormattedName($format, $anchor);
		}
		return $html;
	}

	/**
	 * Format Sosa number to display next to individual details
	 * Possible format are:
	 * 	- 1 (default) : display an image if the individual is a Sosa, independently of the number of times he is
	 * 	- 2 : display a list of Sosa numbers, with an image, separated by an hyphen.
	 *
	 * @param array $sosatab List of Sosa numbers
	 * @param int $format Format to apply to the Sosa numbers
	 * @param string $size CSS size for the icon. A CSS style css_$size is required
	 * @return string HTML code for the formatted Sosa numbers
	 */
	public static function formatSosaNumbers(array $sosatab, $format = 1, $size = 'small'){
		$html = '';
		switch($format){
			case 1:
				if(count($sosatab)>0){
					$html = '<i class="icon-maj-sosa_'.$size.'" title="'.I18N::translate('Sosa').'"></i>';
				}
				break;
			case 2:
				if(count($sosatab)>0){
					ksort($sosatab);
					$tmp_html = array();
					foreach ($sosatab as $sosa => $gen) {
						$tmp_html[] = sprintf(
								'<i class="icon-maj-sosa_%1$s" title="'.I18N::translate('Sosa').'"></i>&nbsp;<strong>%2$d&nbsp;'.I18N::translate('(G%s)', $gen) .'</strong>',
								$size,
								$sosa
							);
					}
					$html = implode(' - ', $tmp_html);
				}
				break;
			default:
				break;
		}
		return $html;
	}

	/**
	 * Format IsSourced icons for display
	 * Possible format are:
	 * 	- 1 (default) : display an icon depending on the level of sources
	 *
	 * @param string $sourceType Type of the record : 'E', 'R'
	 * @param int $isSourced Level to display
	 * @param string $tag Fact to display status
	 * @param int $format Format to apply to the IsSourced parameter
	 * @param string $size CSS size for the icon. A CSS style css_$size is required
	 * @return string HTML code for IsSourced icon
	 */
	public static function formatIsSourcedIcon($sourceType, $isSourced, $tag='EVEN', $format = 1, $size='normal'){
		$html='';
		$image=null;
		$title=null;
		switch($format){
			case 1:
				switch($sourceType){
					case 'E':
						switch($isSourced){
							case 0:
								$image = 'event_unknown';
								$title = I18N::translate('%s not found', GedcomTag::getLabel($tag));
								break;
							case -1:
								$image = 'event_notprecise';
								$title = I18N::translate('%s not precise', GedcomTag::getLabel($tag));
								break;
							case -2:
								$image = 'event_notsourced';
								$title = I18N::translate('%s not sourced', GedcomTag::getLabel($tag));
								break;
							case 1:
								$image = 'event_sourced';
								$title = I18N::translate('%s sourced', GedcomTag::getLabel($tag));
								break;
							case 2:
								$image = 'event_sourcedcertif';
								$title = I18N::translate('%s sourced with a certificate', GedcomTag::getLabel($tag));
								break;
							case 3:
								$image = 'event_sourcedcertifdate';
								$title = I18N::translate('%s sourced with exact certificate', GedcomTag::getLabel($tag));
								break;
							default:
								break;
						}
						break;
					case 'R':
						switch($isSourced){
							case -1:
								$image = 'record_notsourced';
								$title = I18N::translate('%s not sourced', GedcomTag::getLabel($tag));
								break;
							case 1:
								$image = 'record_sourced';
								$title = I18N::translate('%s sourced', GedcomTag::getLabel($tag));
								break;
							case 2:
								$image = 'record_sourcedcertif';
								$title = I18N::translate('%s sourced with a certificate', GedcomTag::getLabel($tag));
								break;
							default:
								break;
						}
						break;
						break;
					default:
						break;
				}
				if($image && $title) $html = '<i class="icon-maj-sourced-'.$size.'_'.$image.'" title="'.$title.'"></i>';
				break;
			default:
				break;
		}
		return $html;
	}
	
	/**
	 * Check whether the date is compatible with the Google Chart range (between 1550 and 2030).
	 * 
	 * @param Date $date
	 * @return boolean
	 */
	public static function isDateWithinChartsRange(Date $date) {
	    return $date->gregorianYear() >= 1550 && $date->gregorianYear() < 2030;
	}

}

<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Controller
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
 namespace MyArtJaub\Webtrees\Controller;

use \Fisharebest\Webtrees as fw;
use \MyArtJaub\Webtrees as mw;
use MyArtJaub\Webtrees\Hook\Hook;

/**
 * Decorator class to extend native webtrees IndividualController class.
 * 
 * @see \Fisharebest\Webtrees\Controller\IndividualController
 * @todo snake_case
 */
class IndividualController {

	/** @var \Fisharebest\Webtrees\Controller\IndividualController $ctrlIndividual Underlying base controller */
	protected $ctrlIndividual;
	
	/** @var \MyArtJaub\Webtrees\Individual $dindi MyArtJaub Individual attached to the controller*/
	protected $dindi;

	/**
	 * Constructor for the decorator
	 *
	 * @param \Fisharebest\Webtrees\Controller\IndividualController $ctrlIndividual_in The Individual Controller to extend
	 */
	public function __construct(fw\Controller\IndividualController $ctrlIndividual_in){
		$this->ctrlIndividual = $ctrlIndividual_in;
		$this->dindi = new mw\Individual($this->ctrlIndividual->getSignificantIndividual());
	}
	
	/**
	 * Print individual header extensions.
	 * Use hooks hExtendIndiHeaderLeft and hExtendIndiHeaderRight
	 * 
	 * @uses \MyArtJaub\Webtrees\Hook\Hook
	 */
	public function printHeaderExtensions(){
		$hook_extend_indi_header_left = new mw\Hook\Hook('hExtendIndiHeaderLeft');
		$hook_extend_indi_header_right = new mw\Hook\Hook('hExtendIndiHeaderRight');
		$hook_extend_indi_header_left = $hook_extend_indi_header_left->execute($this->ctrlIndividual);
		$hook_extend_indi_header_right = $hook_extend_indi_header_right->execute($this->ctrlIndividual);
		
		echo '<div id="indi_perso_header">',
			'<div id="indi_perso_header_left">';
		foreach ($hook_extend_indi_header_left as $div) {
			if(count($div)==2){
				echo '<div id="', $div[0], '" class="indi_perso_header_left_div">',
					$div[1], '</div>';
			}
		}
		echo '</div>',
			'<div id="indi_perso_header_right">';
		foreach ($hook_extend_indi_header_right as $div) {
			if(count($div)==2){
				echo '<div id="', $div[0], '" class="indi_perso_header_right_div">',
					$div[1], '</div>';
			}
		}
		echo '</div>',
		'</div>';
	}
	
	/**
	 * Print individual header extra icons.
	 * Use hook hExtendIndiHeaderIcons
	 *
	 * @uses \MyArtJaub\Webtrees\Hook\Hook
	 */
	public function printHeaderExtraIcons(){
		$hook_extend_indi_header_icons = new Hook('hExtendIndiHeaderIcons');
		$hook_extend_indi_header_icons = $hook_extend_indi_header_icons->execute($this->ctrlIndividual);
		
		echo '<span id="indi_perso_icons">&nbsp;',
			implode('&nbsp;', $hook_extend_indi_header_icons),
			'</span>';
	}

}

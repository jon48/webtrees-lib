<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\PatronymicLineage\Model;

use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Tree;
/**
 * Build the patronymic lineage for a surname 
 */
class LineageBuilder {
	
	/**
	 * @var Fisharebest\Webtrees\Tree $tree Reference tree
	 */ 
	protected $tree;
	
	/**
	 * @var string $surname Reference surname
	 */
	protected $surname;
	
	/**
	 * @var array $used_indis Individuals already processed
	 */
	protected $used_indis;
	
	/**
	 * Constructor for Lineage Builder
	 * 
	 * @param string $surname Reference surname
	 * @param Tree $tree Gedcom tree
	 */
	public function __construct($surname, Tree $tree) {
		$this->tree = $tree;
		$this->surname = $surname;
		$this->used_indis = array();
	}
	
	/**
	 * Build all patronymic lineages for the reference surname.
	 * 
	 * @return array List of root patronymic lineages
	 */
	public function buildLineages() {				
		$indis = \Fisharebest\Webtrees\Query\QueryName::individuals($this->tree, $this->surname, null, null, false, false);
		
		if(count($indis) == 0) return null;
		
		$root_lineages = array();
		
		foreach($indis as $indi) {
			$pid = $indi->getXref();
			if(!isset($this->used_indis[$pid])){
				//Find the root of the lineage
				/** @var Fisharebest\Webtrees\Individual $indiFirst  */
				$indiFirst= $this->getLineageRootIndividual($indi);
				if($indiFirst){
					$_usedIndis[$indiFirst->getXref()] = true;
					if($indiFirst->canShow()){
						//Check if the root individual has brothers and sisters, without parents
						$indiChildFamily = $indiFirst->getPrimaryChildFamily();
						if($indiChildFamily != null){
							$root_node = new LineageRootNode(null); 
							$root_node->addFamily($indiChildFamily);
						}
						else{
							$root_node = new LineageRootNode($indiFirst);
						}
						$root_node = $this->buildLineage($root_node);		
						if($root_node) $root_lineages[] = $root_node;
					}
				}
			}
		}
		
		return $root_lineages;		
	}
	
	/**
	 * Retrieve the root individual, from any individual.
	 * The Root individual is the individual without a father, or without a mother holding the same name.
	 * 
	 * @param Individual $indi
	 * @return (Individual|null) Root individual
	 */
	protected function getLineageRootIndividual(Individual $indi) {
		$is_first=false;
		$dindi = new \MyArtJaub\Webtrees\Individual($indi);
		$indi_surname=$dindi->getUnprotectedPrimarySurname();
		$resIndi = $indi;
		while(!$is_first){
			//Get the individual parents family
			$fam=$resIndi->getPrimaryChildFamily();
			if($fam){
				$husb=$fam->getHusband();
				$wife=$fam->getWife();
				//If the father exists, take him
				if($husb){
					$dhusb = new \MyArtJaub\Webtrees\Individual($husb);
					$dhusb->isNewAddition() ? $is_first = true : $resIndi=$husb;
				}
				//If only a mother exists
				else if($wife){
					$dwife = new \MyArtJaub\Webtrees\Individual($wife);
					$wife_surname=$dwife->getUnprotectedPrimarySurname();
					//Check if the child is a natural child of the mother (based on the surname - Warning : surname must be identical)
					if(!$dwife->isNewAddition() && I18N::strcasecmp($wife_surname, $indi_surname) == 0){
						$resIndi=$wife;
					}
					else{
						$is_first=true;
					}
				}
				else{
					$is_first=true;
				}
			}
			else{
				$is_first=true;
			}
		}
		if(isset($_usedIndis[$resIndi->getXref()])){
			return null;
		}
		else{
			return $resIndi;
		}
	}
	
	/**
	 * Computes descendent Lineage from a node.
	 * Uses recursion to build the lineage tree
	 * 
	 * @param LineageNode $node
	 * @return LineageNode Computed lineage
	 */
	protected function buildLineage(LineageNode $node) {
		if($node == null) return;
		
		$indi_surname = null;
		
		$indi_node = $node->getIndividual();			
		if($indi_node) {
			if(count($node->getFamiliesNodes()) == 0) {
				$indiSpouseFamilies = $indi_node->getSpouseFamilies();
				foreach($indiSpouseFamilies as $indiSpouseFamily) {
					$node->addFamily($indiSpouseFamily);
				}
			}
			
			$dindi_node = new \MyArtJaub\Webtrees\Individual($indi_node);
			$indi_surname = $dindi_node->getUnprotectedPrimarySurname();
			
			//Get the estimated birth place and put it in the place table
			$place=$dindi_node->getEstimatedBirthPlace(false);
			if($place && strlen($place) > 0){
				$place=trim($place);
				$node->getRootNode()->addPlace(new Place($place, $this->tree));
			}
				
			//Tag the individual as used
			$this->used_indis[$indi_node->getXref()]=true;
		}
		
		foreach($node->getFamiliesNodes() as $family) {
			$spouse_surname = null;
			if($indi_node && $spouse = $family->getSpouse($indi_node)) {
				$dspouse = new \MyArtJaub\Webtrees\Individual($spouse);
				$spouse_surname=$dspouse->getUnprotectedPrimarySurname();
			}
			
			$children = $family->getChildren();

			$nbChildren=0;
			$nbNatural=0;
			
			foreach($children as $child){
				$dchild = new \MyArtJaub\Webtrees\Individual($child);
				$child_surname=$dchild->getUnprotectedPrimarySurname();
				
				if(!$dchild->isNewAddition()) {
					$nbChildren++;
					//If the root individual is the mother
					if($indi_node && I18N::strcasecmp($indi_node->getSex(), 'F') == 0) {
						//Print only lineages of children with the same surname as their mother (supposing they are natural children)
						if(!$spouse || ($spouse_surname && I18N::strcasecmp($child_surname, $spouse_surname) != 0)){
							if(I18N::strcasecmp($child_surname, $indi_surname) == 0){
								$nbNatural++;
								$node_child = new LineageNode($child, $node->getRootNode());							
								$node_child = $this->buildLineage($node_child);
								if($node_child) $node->addChild($family, $node_child);
							}
						}
					}
					//If the root individual is the father
					else {
						//Print if the children does not bear the same name as his mother (and different from his father)
						if( strlen($child_surname) == 0 || strlen($indi_surname) == 0 || strlen($spouse_surname) == 0 ||
							I18N::strcasecmp($child_surname, $indi_surname) == 0 ||
							I18N::strcasecmp($child_surname, $spouse_surname) != 0 )
						{
							$nbNatural++;
							$node_child = new LineageNode($child, $node->getRootNode());							
							$node_child = $this->buildLineage($node_child);
							if($node_child) $node->addChild($family, $node_child);
						}
						else {
							$nbNatural++;
							$node_child = new LineageNode($child, $node->getRootNode(), $child_surname);
							if($node_child) $node->addChild($family, $node_child);
						}
					}
				}
			}


			//Do not print other children
			if(($nbChildren-$nbNatural)>0){
				$node->addChild($family, null);
			}
		}
		
		return $node;
		
	}
	
	
}
 
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

use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;
/**
 * Node of the lineage tree, holding data about individuals forming it, and their descendency.
 */
class LineageNode {
	
	/**
	 * @var SplObjectStorage $linked_fams Spouse families linked to the node
	 */
	protected $linked_fams;
	
	/**
	 * @var Fisharebest\Webtrees\Individual $node_indi Reference individual for the node
	 */
	protected $node_indi;
	
	/**
	 * @var LineageRootNode $root_node Root node of the lineage
	 */
	protected $root_node;
	
	/**
	 * @var string $alt_surname Linked surname, used to link to another lineage
	 */
	protected $alt_surname;
	
	/**
	 * Constructor for Lineage node
	 * 
	 * @param Fisharebest\Webtrees\Individual $node_indi Main individual
	 * @param LineageRootNode $root_node Node of the lineage root
	 * @param unknown $alt_surname Follow-up surname
	 */
	public function __construct(Individual $node_indi = null, LineageRootNode $root_node = null, $alt_surname = null) {
		$this->node_indi = $node_indi;
		$this->root_node = $root_node;
		$this->alt_surname = $alt_surname;
		$this->linked_fams = new \SplObjectStorage();
	}
	
	/**
	 * Add a spouse family to the node
	 * 
	 * @param Fisharebest\Webtrees\Family $fams
	 */
	public function addFamily(Family $fams) {
		if($fams && !isset($this->linked_fams[$fams])) {
			$this->linked_fams[$fams] = array();
		}
	}
	
	/**
	 * Add a child LineageNode to the node
	 * 
	 * @param Fisharebest\Webtrees\Family $fams
	 * @param LineageNode $child
	 */
	public function addChild(Family $fams, LineageNode $child = null) {
		if($fams) {
			$this->addFamily($fams);
			$tmp = $this->linked_fams[$fams];
			$tmp[] = $child;
			$this->linked_fams[$fams] = $tmp;
		}
	}
	
	/**
	 * Returns the node individual
	 * 
	 * @return Fisharebest\Webtrees\Individual
	 */
	public function getIndividual() {
		return $this->node_indi;
	}
	
	/**
	 * Returns the lineage root node individual
	 * 
	 * @return LineageRootNode
	 */
	public function getRootNode() {
		return $this->root_node;
	}
	
	/**
	 * Returns the spouse families linked to the node
	 * 
	 * @return Fisharebest\Webtrees\Family
	 */
	public function getFamiliesNodes() {
		return $this->linked_fams;
	}
	
	/**
	 * Returns the follow-up surname
	 * 
	 * @return string
	 */
	public function getFollowUpSurname() {
		return $this->alt_surname;
	}
	
	/**
	 * Indicates whether the node has a follow up surname 
	 * 
	 * @return boolean
	 */
	public function hasFollowUpSurname() {
		return !is_null($this->alt_surname) && strlen($this->alt_surname) > 0 ;
	}
	
}
 
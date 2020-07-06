<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\PatronymicLineage\Model;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Factory;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Services\IndividualListService;
use Illuminate\Support\Collection;

/**
 * Build the patronymic lineage for a surname 
 */
class LineageBuilder 
{
    
    /**
     * @var string $surname Reference surname
     */
    private $surname;
    
	/**
	 * @var Tree $tree Reference tree
	 */ 
	private $tree;
	
	/**
	 * @var IndividualListService $indilist_service
	 */
	private $indilist_service;
	
	/**
	 * @var Collection $used_indis Individuals already processed
	 */
	private $used_indis;
	
	/**
	 * Constructor for Lineage Builder
	 * 
	 * @param string $surname Reference surname
	 * @param Tree $tree Gedcom tree
	 */
	public function __construct($surname, Tree $tree, IndividualListService $indilist_service)
	{
	    $this->surname = $surname;
		$this->tree = $tree;
		$this->indilist_service = $indilist_service;
		$this->used_indis = new Collection();
	}
	
	/**
	 * Build all patronymic lineages for the reference surname.
	 * 
	 * @return array List of root patronymic lineages
	 */
	public function buildLineages() : Collection
	{
	    $indis = $this->indilist_service->individuals($this->surname, '', '', false, false, I18N::locale());
	    // Warning - the IndividualListService returns a clone of individuals objects. Cannot be used for object equality
		if(count($indis) == 0) return null;
		
		$root_lineages = new Collection();
		
		foreach($indis as $indi) {
		    /** @var Individual $indi */
		    if(!$this->used_indis->get($indi->xref(), false)){
				$indi_first = $this->getLineageRootIndividual($indi);
				if($indi_first !== null){
				    // The root lineage needs to be recreated from the Factory, to retrieve the proper object
				    $indi_first = Factory::individual()->make($indi_first->xref(), $this->tree);
				    $this->used_indis->put($indi_first->xref(), true);
				    if($indi_first->canShow()){
						//Check if the root individual has brothers and sisters, without parents
				        $indi_first_child_family = $indi_first->childFamilies()->first();
				        if($indi_first_child_family !== null){
							$root_node = new LineageRootNode(null); 
							$root_node->addFamily($indi_first_child_family);
						}
						else{
							$root_node = new LineageRootNode($indi_first);
						}
						$root_node = $this->buildLineage($root_node);		
						if($root_node !== null) $root_lineages->add($root_node);
					}
				}
			}
		}
		
		return $root_lineages->sort(function(LineageRootNode $a, LineageRootNode $b) {
		    if($a->numberChildNodes() == $b->numberChildNodes()) return 0;
		    return ($a->numberChildNodes() > $b->numberChildNodes()) ? -1 : 1;
		});		
	}
	
	/**
	 * Retrieve the root individual, from any individual, by recursion.
	 * The Root individual is the individual without a father, or without a mother holding the same name.
	 *
	 * @param Individual $indi
	 * @return Individual|NULL Root individual
	 */
	private function getLineageRootIndividual(Individual $indi) : ?Individual
	{
	    $child_families = $indi->childFamilies();
	    if($this->used_indis->get($indi->xref(), false)) {
	        return null;
	    }
	    
		foreach($child_families as $child_family) {
		    /** @var Family $child_family */
		    $child_family->husband();
		    if($husb = $child_family->husband()) {
		        if($husb->isPendingAddition() && $husb->privatizeGedcom(Auth::PRIV_HIDE) == '') {
		            return $indi;
		        }
		        return $this->getLineageRootIndividual($husb);
		    }
		    else if ($wife = $child_family->wife()){
		        if(!($wife->isPendingAddition() && $wife->privatizeGedcom(Auth::PRIV_HIDE) == '')) {
		            $indi_surname = $indi->getAllNames()[$indi->getPrimaryName()]['surname'];
		            $wife_surname = $wife->getAllNames()[$wife->getPrimaryName()]['surname'];
		            if($indi->canShowName() && $wife->canShowName() && I18N::strcasecmp($indi_surname, $wife_surname) == 0) {
		                return $this->getLineageRootIndividual($wife);
		            }
		        }
		        return $indi;
		    }
		}
		return $indi;
	}
	
	/**
	 * Computes descendent Lineage from a node.
	 * Uses recursion to build the lineage tree
	 * 
	 * @param LineageNode $node
	 * @return LineageNode Computed lineage
	 */
	private function buildLineage(LineageNode $node) : LineageNode
	{
		$indi_surname = '';
		
		$indi_node = $node->individual();
		if($indi_node !== null) {
		    if($node->families()->count() == 0) {
				foreach($indi_node->spouseFamilies() as $spouse_family) {
				    $node->addFamily($spouse_family);
				}
			}
			
			$indi_surname = $indi_node->getAllNames()[$indi_node->getPrimaryName()]['surname'] ?? '';
			$node->rootNode()->addPlace($indi_node->getBirthPlace());
				
			//Tag the individual as used
			$this->used_indis->put($indi_node->xref(), true);
		}
		
		foreach($node->families() as $family_node) {
		    /** @var Family $spouse_family */
		    $spouse_family = $family_node->family;
			$spouse_surname = '';
			if($indi_node !== null && ($spouse = $spouse_family->spouse($indi_node)) && $spouse->canShowName()) {
			    $spouse_surname = $spouse->getAllNames()[$spouse->getPrimaryName()]['surname'] ?? '';
			}
			
			$nb_children = $nb_natural = 0;
			
			foreach($spouse_family->children() as $child){				
			    if(!($child->isPendingAddition() && $child->privatizeGedcom(Auth::PRIV_HIDE) == '')) {
			        $child_surname = $child->getAllNames()[$child->getPrimaryName()]['surname'] ?? '';
			        
					$nb_children++;
					//If the root individual is the mother
					if($indi_node !== null && $indi_node->sex() == 'F') {
						//Print only lineages of children with the same surname as their mother (supposing they are natural children)
						if(!$spouse || ($spouse_surname && I18N::strcasecmp($child_surname, $spouse_surname) != 0)){
							if(I18N::strcasecmp($child_surname, $indi_surname) == 0){
							    $nb_natural++;
								$node_child = new LineageNode($child, $node->rootNode());							
								$node_child = $this->buildLineage($node_child);
								if($node_child) $node->addChild($spouse_family, $node_child);
							}
						}
					}
					//If the root individual is the father
					else {
					    $nb_natural++;
						//Print if the children does not bear the same name as his mother (and different from his father)
					    if(mb_strlen($child_surname) == 0 || mb_strlen($indi_surname) == 0 || mb_strlen($spouse_surname) == 0 ||
							I18N::strcasecmp($child_surname, $indi_surname) == 0 ||
							I18N::strcasecmp($child_surname, $spouse_surname) != 0 )
						{
							$node_child = new LineageNode($child, $node->rootNode());							
							$node_child = $this->buildLineage($node_child);
						}
						else {
							$node_child = new LineageNode($child, $node->rootNode(), $child_surname);
						}
						if($node_child) $node->addChild($spouse_family, $node_child);
					}
				}
			}
			
			//Do not print other children
			if(($nb_children - $nb_natural)>0){
			    $node->addChild($spouse_family, null);
			}
		}
		
		return $node;
	}
}
 
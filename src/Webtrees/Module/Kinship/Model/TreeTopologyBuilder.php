<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Kinship
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Kinship\Model;

use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;

/**
 * Build the Topology for a tree
 *  
 *  Based on an algorithm from Didier Rémy (INRIA).
 *  @copyright Copyright (c) 1998-2007 INRIA 
 */
class TreeTopologyBuilder {
    
    /**
     * Reference tree
     * @var Fisharebest\Webtrees\Tree $tree
     */
    private $tree;
    
    /**
     * Tree topology
     * @var \MyArtJaub\Webtrees\Module\Kinship\Model\TreeTopology $topology
     */
    private $topology;

    /**
     * Provider for the Tree Topology
     * @var \MyArtJaub\Webtrees\Module\Kinship\Model\TreeTopologyProvider $provider
     */
    private $provider;
    
    /**
     * Constructor for the Tree Topology Calculator
     * @param Tree $tree
     */
    public function __construct(Tree $tree, TreeTopology $topology, TreeTopologyProvider $provider) {        
        $this->tree = $tree;        
        $this->topology = $topology;        
        $this->provider = $provider;  
    }
    
    public function build() {
        $nb_indis = $this->populate();  
        $nb_sorted = $this->sort(0, $this->topology->getIndiWithOrder(0)); 
        
        if($nb_indis != $nb_sorted)
            throw new \Exception('TopologicalSortError', null, null); //Geneweb is doing an additional check, to check for loops.
    }    

    private function incrementOrder(Individual $indi = null, $increment = 0) {
        if($indi == null) return false;
        if(\MyArtJaub\Webtrees\Individual::decorate($indi)->isNewAddition()) return false; // Do not take into account new additions non validated
        $this->topology->setOrder($indi, $this->topology->getOrder($indi) + $increment);
        return $this->topology->getOrder($indi) == 0;
    }
    
    private function populate()
    {
        $all_indis = $this->provider->getAllIndividuals();
        foreach($all_indis as $xref) {
            $indi = Individual::getInstance($xref, $this->tree);
            $this->topology->setOrder($indi, $this->topology->getOrder($indi));
            if($fam = $indi->getPrimaryChildFamily()) {
                $this->incrementOrder($fam->getHusband(), 1);
                $this->incrementOrder($fam->getWife(), 1);
            }
        }
        return count($all_indis);
    }
        
    private function sort($order, $to_process, $count = 0) {
        if(empty($to_process)) return $count;
        $next_to_process = array();
        foreach($to_process as $xref) {
            $indi = Individual::getInstance($xref, $this->tree);
            $count++;
            
            $this->topology->setOrder($indi, $order);                      
            if($fam = $indi->getPrimaryChildFamily()) {
                if($this->incrementOrder($fam->getHusband(), -1))
                    $next_to_process[] = $fam->getHusband()->getXref();
                if($this->incrementOrder($fam->getWife(), -1))
                    $next_to_process[] = $fam->getWife()->getXref();
            }
        }
        return $this->sort($order + 1, $next_to_process, $count);
    }
}
 
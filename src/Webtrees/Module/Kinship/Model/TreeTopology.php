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
 * Topology for the tree, defining an order for individual, with the following properties:
 *  - only ancestors need to be loaded
 *  - if an individual is an ancestor of an another, then it has an higher order
 *  - the value of the order is minimum
 *  
 *  Based on an algorithm from Didier Rémy (INRIA).
 *  @copyright Copyright (c) 1998-2007 INRIA 
 */
class TreeTopology {
    

    /**
     * Reference tree
     * @var Fisharebest\Webtrees\Tree $tree
     */
    private $tree;

    /**
     * Array of individuals
     * @var array $topology
     */
    private $topology;  
    
    private $topology_by_order;
    
    private $is_dirty_order;
    
    
    /**
     * Constructor for the Tree Topology Calculator
     * @param Tree $tree
     */
    public function __construct(Tree $tree, array $topology = null) {        
        $this->tree = $tree;  
        
       $this->topology = $topology;
       if($this->topology == null) $this->topology = array();
       $is_dirty_order = true;
    }
    
    public function getTopology() {
        return $this->topology;
    }
    
    public function getTopologyByOrder() {
        if($this->topology_by_order == null || $this->is_dirty_order) {
            $this->topology_by_order = array();
            foreach($this->topology as $xref => $order) {
                if(!array_key_exists($order, $this->topology_by_order)) 
                    $this->topology_by_order[$order] = array();
                $this->topology_by_order[$order][] = $xref;
            }
            $this->is_dirty_order = false;
        }
        return $this->topology_by_order;
    }
    
    public function getOrder(Individual $indi) {
        return $this->getOrderByXref($indi->getXref());
    }
    
    public function getOrderByXref($xref) {
        if(array_key_exists($xref, $this->topology)) {
            return $this->topology[$xref];
        }
        return 0;
    }
    
    public function setOrder(Individual $indi, $order) {
        $this->topology[$indi->getXref()] = $order;
        $this->is_dirty_order = true;
    }
        
    public function getIndiWithOrder($order) {
        $topo_by_order = $this->getTopologyByOrder();
        if(array_key_exists($order, $topo_by_order))
            return $topo_by_order[$order];
        return array();
    }
    
}
 
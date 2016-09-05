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
 *  Kinship between two individuals
 *  
 *  Based on an algorithm from Didier Rémy (INRIA).
 *  @copyright Copyright (c) 1998-2007 INRIA 
 */
class Kinship {
    
    /**
     * Reference tree
     * @var Fisharebest\Webtrees\Tree $tree
     */
    private $tree;
    
    private $indi1;
    private $indi2;
    
    private $topology;
    
    private $kinship_coeff;
    private $kinship_roots;
    
    private $queue;    
    
    /**
     * Kinship Infor table
     * @var KinshipInfoTable $kinship_info_table
     */
    private $kinship_info_table;
    
    private $order_cursor;
    private $order_max;
    
    private $nb_ancestors1;
    private $nb_ancestors2;
    
    /**
     * Constructor for the Tree Topology Calculator
     * @param Tree $tree
     */
    public function __construct(Individual $indi1, Individual $indi2, TreeTopology $topology) {    
        if($indi1->getTree()->getTreeId() != $indi2->getTree()->getTreeId())
            throw new \Exception('Kinship is valid only within the same tree.');
        
        $this->tree = $indi1->getTree();  
        $this->indi1 = $indi1;
        $this->indi2 = $indi2;
        $this->topology = $topology;
        
        $this->kinship_coeff = 0;
        $this->kinship_roots = array();      
        
        $this->queue = array();
        $this->kinship_info_table = new KinshipInfoTable();
        
        $this->nb_ancestors1 = 1;
        $this->nb_ancestors2 = 1; 
    }
    
    public function compute($compute_links = false) {        
        if($this->indi1 == $this->indi2) {
            $this->kinship_coeff = 1;
            return array('kinship_coef' => 1, 'roots' => array(), 'kinship_info' => array());
        }
                
        $this->order_cursor = min($this->topology->getOrder($this->indi1), $this->topology->getOrder($this->indi2));
        $this->order_max = -1;    
        $this->prepareQueue($this->indi1);
        $this->prepareQueue($this->indi2);
        
        $this->kinship_info_table->getInfo($this->indi1)->getFirstBranch()
            ->setWeight(1)
            ->setAncestor(true)
            ->addPath(new KinshipInfoPath(0, 1, array()));
        
        $this->kinship_info_table->getInfo($this->indi2)->getSecondBranch()
            ->setWeight(1)
            ->setAncestor(true)
            ->addPath(new KinshipInfoPath(0, 1, array()));
        	
        while($this->order_cursor <= $this->order_max && $this->nb_ancestors1 > 0 && $this->nb_ancestors2 > 0) {
            foreach($this->queue[$this->order_cursor] as $indi) {
                $this->processAncestors($indi, $compute_links);
            }
            $this->order_cursor++;
        }
        
        return array(
            'kinship_coef' => $this->kinship_coeff / 2, 
            'roots' => $this->kinship_roots, 
            'kinship_info' => $this->kinship_info_table           
        );
    }
    
    private function prepareQueue(Individual $indi) {
        $order = $this->topology->getOrder($indi);
        $this->kinship_info_table->initKinshipInfo($indi);
                       
        if($order >= count($this->queue)) {
            $this->queue = array_merge(
                $this->queue, 
                array_fill(0, $order + 1 - count($this->queue), array())
            );
        }
        
        if($this->order_max < 0 ) {
            for($i = $this->order_cursor; $i < $order; $i++) {
                $this->queue[$i] = array();
            }
            $this->order_max = $order;
            $this->queue[$order] = array($indi);
        }
        else {
            if($order > $this->order_max) {
                for($i = $this->order_max + 1; $i < $order + 1; $i++) {
                    $this->queue[$i] = array();
                }
                $this->order_max = $order;
            }
            $this->queue[$order][] = $indi;
        }
    }
    
    private function processAncestors(Individual $indi, $compute_links = false) {
        $indi_kininfo = $this->kinship_info_table->getInfo($indi);
        
        $contribution = $indi_kininfo->getFirstBranch()->getWeight() * $indi_kininfo->getSecondBranch()->getWeight() - $indi_kininfo->getCoefficient() * ( 1 + $this->consangOf($indi) );
        $this->kinship_coeff += $contribution;

        if($indi_kininfo->getFirstBranch()->isAncestor()) $this->nb_ancestors1--;
        if($indi_kininfo->getSecondBranch()->isAncestor()) $this->nb_ancestors2--;
        
        if($compute_links && $contribution != 0 && !$indi_kininfo->isRemoveAncestors()) {
            $this->kinship_roots[] = $indi;
        }
        
        if($fam = $indi->getPrimaryChildFamily()) {
            $this->processParent($indi, $fam->getHusband(), $compute_links);
            $this->processParent($indi, $fam->getWife(), $compute_links);
        }        
    }   
    
    private function processParent(Individual $indi, Individual $parent = null, $compute_links = false) {
        if($parent == null) return;
        if(\MyArtJaub\Webtrees\Individual::decorate($parent)->isNewAddition()) return;
        
        $indi_kininfo = $this->kinship_info_table->getInfo($indi);
        $parent_kininfo  = $this->kinship_info_table->getInfo($parent);
        if($parent_kininfo == null) {
            $this->prepareQueue($parent);
            $parent_kininfo  = $this->kinship_info_table->getInfo($parent);
        }
        $contrib1 = $indi_kininfo->getFirstBranch()->getWeight() / 2;
        $contrib2 = $indi_kininfo->getSecondBranch()->getWeight() / 2;
        
        if($indi_kininfo->getFirstBranch()->isAncestor() && !$parent_kininfo->getFirstBranch()->isAncestor()) {
            $parent_kininfo->getFirstBranch()->setAncestor(true);
            $this->nb_ancestors1++;
        }
        if($indi_kininfo->getSecondBranch()->isAncestor() && !$parent_kininfo->getSecondBranch()->isAncestor()) {
            $parent_kininfo->getSecondBranch()->setAncestor(true);
            $this->nb_ancestors2++;
        }
        
        $parent_kininfo->getFirstBranch()->setWeight($parent_kininfo->getFirstBranch()->getWeight() + $contrib1);
        $parent_kininfo->getSecondBranch()->setWeight($parent_kininfo->getSecondBranch()->getWeight() + $contrib2);
        $parent_kininfo->setCoefficient($parent_kininfo->getCoefficient() + $contrib1 * $contrib2);
        if($indi_kininfo->isRemoveAncestors()) $parent_kininfo->setRemoveAncestors(true);
        
        if($compute_links && !$parent_kininfo->isRemoveAncestors()) {
            $temp1 = $parent_kininfo->getFirstBranch()->getPaths();
            foreach($indi_kininfo->getFirstBranch()->getPaths() as $path1) {
                $temp1 = $this->processPaths($indi, $path1->getDepth() + 1, $path1->getNumberOfPaths(), $temp1);
            }
            $parent_kininfo->getFirstBranch()->setPaths($temp1);
            
            $temp2 = $parent_kininfo->getSecondBranch()->getPaths();
            foreach($indi_kininfo->getSecondBranch()->getPaths() as $path2) {
                $temp2 = $this->processPaths($indi, $path2->getDepth() + 1, $path2->getNumberOfPaths(), $temp2);
            }
            $parent_kininfo->getSecondBranch()->setPaths($temp2);
        }        
    }
        
    private function processPaths($indi, $depth, $nb_paths, array $paths) {
        if(empty($paths))
            return array(new KinshipInfoPath($depth, $nb_paths, array($indi)));
        
        $path = array_shift($paths);       
        if(!($path instanceof KinshipInfoPath)) throw new \Exception('The array `paths` does not contains elements of type KinshipInfoPath', null, null);
        
        if($depth == $path->getDepth()) {
            $new_nb_paths = $nb_paths + $path->getNumberOfPaths();
            $new_nb_paths = ( $nb_paths < 0 || $path->getNumberOfPaths() < 0 || $new_nb_paths < 0 ) ? -1 : $new_nb_paths;
            array_unshift($paths, new KinshipInfoPath($path->getDepth(), $new_nb_paths, array_merge(array($indi), $path->getLinkingIndividuals())));
            return $paths;
        }
        else {
            $tmp_paths = $this->processPaths($indi, $depth, $nb_paths, $paths);
            array_unshift($tmp_paths, $path);
            return $tmp_paths;
        }
    }
    
    private function consangOf(Individual $indi) {
        return 0;
    }
        
}
 
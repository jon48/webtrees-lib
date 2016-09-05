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

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Tree;

/**
 * Provider for Tree Topology data access 
 */
class TreeTopologyProvider {
    
    /**
     * Reference tree
     * @var Fisharebest\Webtrees\Tree $tree
     */
    private $tree;
    
    /**
     * Constructor for the Tree Topology Provider
     * @param Tree $tree
     */
    public function __construct(Tree $tree) {        
        $this->tree = $tree;
    }
    
    public function getTreeTopology($force_rebuild = false) {
        if(!$force_rebuild) {
            $topo_array = Database::prepare(
                'SELECT majk_i_id, majk_topo_order'.
                ' FROM `##maj_kinship`'.
                ' WHERE majk_gedcom_id = :tree_id'
                )->execute(array(
                    'tree_id' => $this->tree->getTreeId()
                ))->fetchAssoc();
            if(count($topo_array) > 0) 
                return new TreeTopology($this->tree, $topo_array);
        }
        
        $topology = new TreeTopology($this->tree);
        $builder = new TreeTopologyBuilder($this->tree, $topology, $this);
        $builder->build();
        
        $this->save($topology);        
        return $topology;
    }
    
    public function save(TreeTopology $topology) {        
        $this->deleteAll();
        
        $i = 0;
        $questionmarks_table = array();
        $values_table = array();
        $treeid = $this->tree->getTreeId();
        foreach  ($topology->getTopology() as $xref => $order) {
            $questionmarks_table[] =
                '(:tree_id'.$i.', :indi_id'.$i.', :order'.$i.')';
            $values_table = array_merge(
                $values_table,
                array(
                    'tree_id'.$i => $treeid,
                    'indi_id'.$i => $xref,
                    'order'.$i => $order
                )
            );
            $i++;
        }
            
        $sql = 'INSERT INTO `##maj_kinship`' .
            ' (majk_gedcom_id, majk_i_id, majk_topo_order)' .
            ' VALUES '. implode(',', $questionmarks_table);
        Database::prepare($sql)->execute($values_table);
    }

    public function deleteAll() {
        Database::prepare(
            'DELETE FROM `##maj_kinship`'.
            ' WHERE majk_gedcom_id= :tree_id')
            ->execute(array(
                'tree_id' => $this->tree->getTreeId()
            ));
    }
    
    /**
     * Return the list of all individuals
     *
     * @return array Associative array of Sosa ancestors, with their generation, comma separated
     */
    public function getAllIndividuals(){
        return Database::prepare(
            'SELECT i_id'.
            ' FROM `##individuals`'.
            ' WHERE i_file = :tree_id'
            )->execute(array(
                'tree_id' => $this->tree->getTreeId()
            ))->fetchOneColumn();
    }
    
}
 
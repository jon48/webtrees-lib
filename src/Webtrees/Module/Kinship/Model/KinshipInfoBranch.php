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

/**
 *  Information about kinship
 */
class KinshipInfoBranch {
    
    private $weight;
    private $is_ancestor;
    private $paths;
    
    public function __construct($weight, $is_ancestor, array $paths) {
        $this->weight = $weight;
        $this->is_ancestor = $is_ancestor;
        $this->paths = $paths;
    }
    
    public function getWeight() {
        return $this->weight;
    }
    
    public function setWeight($weight) {
        $this->weight = $weight;
        return $this;
    }
        
    public function isAncestor() {
        return $this->is_ancestor;
    }

    public function setAncestor($is_ancestor) {
        $this->is_ancestor = $is_ancestor;
        return $this;
    }
    
    public function getPaths() {
        return $this->paths;
    }
    
    public function setPaths(array $paths) {
        $this->paths = $paths;
        return $this;
    }
    
    public function addPath(KinshipInfoPath $path) {
        $this->paths[] = $path;   
        return $this;
    }
}
 
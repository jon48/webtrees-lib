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

use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Individual;

/**
 *  Information about kinship
 */
class KinshipInfo {
    
    private $kinship_coeff;
    private $branch1;
    private $branch2;
    private $remove_ancestors;
    
    public function __construct($coeff, KinshipInfoBranch $branch1, KinshipInfoBranch $branch2, $remove_ancestors){
        $this->kinship_coeff = $coeff;
        $this->remove_ancestors = $remove_ancestors;
        $this->branch1 = $branch1;
        $this->branch2 = $branch2;
    }
    
    public function getCoefficient() {
        return $this->kinship_coeff;
    }
    
    public function setCoefficient($kinship_coeff) {
        $this->kinship_coeff = $kinship_coeff;
        return $this;
    }
    
    public function getFirstBranch() {
        return $this->branch1;
    }
    
    public function getSecondBranch() {
        return $this->branch2;
    }
    
    public function isRemoveAncestors() {
        return $this->remove_ancestors;
    }

    public function setRemoveAncestors($remove_ancestors) {
        $this->remove_ancestors = $remove_ancestors;
        return $this;
    }
    
    
}
 
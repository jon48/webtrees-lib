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
class KinshipInfoTable {
    
    private $kinship_info_table;
    
    public function __construct() {
        $this->kinship_info_table = array();
    }
    
    public function initKinshipInfo(Individual $indi) {
        if(!array_key_exists($indi->getXref(), $this->kinship_info_table)) {
            $this->kinship_info_table[$indi->getXref()] = new KinshipInfo(
                0,
                new KinshipInfoBranch(0, false, array()),
                new KinshipInfoBranch(0, false, array()),
                false);
        }
    }
    
    /**
     * 
     * @param Individual $indi
     * @return KinshipInfo
     */
    public function getInfo(Individual $indi) {
        if(array_key_exists($indi->getXref(), $this->kinship_info_table)) {
            return $this->kinship_info_table[$indi->getXref()];
        }
        return null;
    }    
    
}
 
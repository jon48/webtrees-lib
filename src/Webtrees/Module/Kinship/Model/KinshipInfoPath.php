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
class KinshipInfoPath {
    
    private $depth;
    private $nb_paths;
    private $indi_list;
    
    public function __construct($depth, $nb_path, $indi_list) {
        $this->depth = $depth;
        $this->nb_paths = $nb_path;
        $this->indi_list = $indi_list;        
    }    
    
    public function getDepth() {
        return $this->depth;
    }
    
    public function setDepth($depth) {
        $this->depth = $depth;
        return $this;
    }
    
    public function getNumberOfPaths() {
        return $this->nb_paths;
    }
    
    public function setNumberOfPaths($nb_paths) {
        $this->nb_paths = $nb_paths;
        return $this;
    }
    
    public function getLinkingIndividuals() {
        return $this->indi_list;
    }
    
    public function setLinkingIndividuals($indi_list) {
        $this->indi_list = $indi_list;
        return $this;
    }
}
 
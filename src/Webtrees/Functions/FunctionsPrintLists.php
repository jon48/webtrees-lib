<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
*
* @package MyArtJaub\Webtrees
* @subpackage Functions
* @author Jonathan Jaubart <dev@jaubart.com>
* @copyright Copyright (c) 2016, Jonathan Jaubart
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
*/
namespace MyArtJaub\Webtrees\Functions;

use Fisharebest\Webtrees\Individual;

/**
 * Class FunctionsPrintLists - create sortable lists using datatables.net
 */
class FunctionsPrintLists {
    
    /**
     * Copy of core function, which is not public.
     *
     * @param Individual $individual
     *
     * @return string[]
     * @see \Fisharebest\Webtrees\Functions\FunctionsPrintLists
     */
    public static function sortableNames(Individual $individual) {
        $names   = $individual->getAllNames();
        $primary = $individual->getPrimaryName();

        list($surn, $givn) = explode(',', $names[$primary]['sort']);

        $givn = str_replace('@P.N.', 'AAAA', $givn);
        $surn = str_replace('@N.N.', 'AAAA', $surn);

        return array(
            $surn . 'AAAA' . $givn,
            $givn . 'AAAA' . $surn,
        );
    }

}
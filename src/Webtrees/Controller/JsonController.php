<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Controller
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
 namespace MyArtJaub\Webtrees\Controller;

use Fisharebest\Webtrees\Controller\BaseController;

/**
 * Base controller for all Json pages
 */
class JsonController extends BaseController {
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Controller\BaseController::pageHeader()
     */
    public function pageHeader() {        
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        // We've displayed the header - display the footer automatically
        register_shutdown_function(array($this, 'pageFooter'));
        
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Controller\BaseController::pageFooter()
     */
    public function pageFooter() {
        return $this;
    }
    
    /**
     * Restrict access.
     *
     * @param bool $condition
     *
     * @return $this
     */
    public function restrictAccess($condition) {
        if ($condition !== true) {
            http_response_code(403);
            exit;
        }
    
        return $this;
    }
}

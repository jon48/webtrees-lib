<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Controller
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
 namespace MyArtJaub\Webtrees\Controller;

use Fisharebest\Webtrees\Controller\BaseController;
use MyArtJaub\Webtrees\Mvc\MvcException;

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
            throw new MvcException(403);
        }
    
        return $this;
    }
    
    /**
     * Encode the data to JSON format.
     * 
     * @param array $data Data to encode
     * @param number $options JSON options mask. See http://php.net/manual/fr/json.constants.php
     */
    public function encode(array $data, $options = 0) {
        echo json_encode($data, $options);
    }
}

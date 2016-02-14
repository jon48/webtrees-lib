<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Mvc
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2015-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Mvc;

/**
 * Interface to handle incoming requests for compatible modules.
 */
interface DispatcherInterface {
    
	/**
	 * Handle routing of requests for the Module.
	 * 
	 * @param \Fisharebest\Webtrees\Module\AbstractModule $module
	 * @param string $request
	 */
    public function handle(\Fisharebest\Webtrees\Module\AbstractModule $module, $request);
    
}
 
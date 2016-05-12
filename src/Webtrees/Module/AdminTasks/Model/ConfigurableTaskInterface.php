<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\AdminTasks\Model;

/**
 * Interface for AsbtractTasks that needs to implement a specific configuration
 */
interface ConfigurableTaskInterface {
    
    /**
     * Returns the HTML code to display the specific task configuration.
     * 
     * @return string HTML code
     */
	function htmlConfigForm();
	
	/**
	 * Save the specific configuration after editing the task.
	 * 
	 * @return bool Result of the save
	 */
	function saveConfig();
    
}
 
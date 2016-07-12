<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hook
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Hook;

/**
 * Interface to be implemented by hooks providers.
 * This interface is to be used to provide access to the list of hooks (whether active, installed...)
 */
interface HookProviderInterface {

    /**
     * Return an instance of the hook linked to the specifed function / context
     *
     * @param string $hook_function
     * @param string $hook_context
     * @return Hook
     */
    public function get($hook_function, $hook_context = null);
    
    /**
     * Return whether the Hook module is active and the table has been created.
     *
     * @uses \MyArtJaub\Webtrees\Module\ModuleManager to check if the module is operational
     * @return bool True if module active and table created, false otherwise
     */
    public function isModuleOperational();
    
    /**
     * Get the list of possible hooks in the list of modules files.
     * A hook will be registered:
     * 		- for all modules already registered in Webtrees
     * 		- if the module implements HookSubscriberInterface
     * 		- if the method exist within the module
     *
     * @return Array List of possible hooks, with the priority
     */
    public function getPossibleHooks();
    
    /**
     * Get the list of hooks intalled in webtrees, with their id, status and priority.
     *
     * @return array List of installed hooks
     */
    public function getRawInstalledHooks();
    
    /**
     * Get the list of hooks intalled in webtrees, with their id, status and priority.
     *
     * @return Array List of installed hooks, with id, status and priority
     */
    public function getInstalledHooks();
    
    /**
     * Update the list of hooks, identifying missing ones and removed ones.
     */
    public function updateHooks();
    
}
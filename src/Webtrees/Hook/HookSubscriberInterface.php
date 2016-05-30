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
 * Interface to be implemented for module subscribing to hooks
 */
interface HookSubscriberInterface {
	
    /**
	 * Return the list of functions implementented in the class which needs to be registered as hooks.
	 * The format is either { function1, function 2,...} in which case the priority is the default one
	 * or { function1 => priority1, function2 => priority2, ...}
	 * 
	 * @return array Array of hooks
	 */
    public function getSubscribedHooks();
    
}
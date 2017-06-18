<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2015-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees;

/**
 * Class to access global variables defined by webtrees.
 * This is bad practice to use global, but the core application uses it,
 * so this provide a way to access them.
 */
class Globals {
    
    /**
     * Get global $WT_TREE variable.
     * 
     * @return \Fisharebest\Webtrees\Tree
     */
    public static function getTree() {
        global $WT_TREE;
        
        return $WT_TREE;
    }
    
    /**
     * Check whether the visitor is a bot.
     * 
     * @return boolean
     */
    public static function isSearchSpider() {
        global $SEARCH_SPIDER;
        
        return $SEARCH_SPIDER;
    }
    
    /**
     * Get the current controller.
     * 
     * @return \Fisharebest\Webtrees\BaseController
     */
    public static function getController() {
        global $controller;
        
        return $controller;
    }
    
    /**
     * Get the global facts
     * 
     * @return array
     */
    public static function getGlobalFacts() {
        global $global_facts;
        
        return $global_facts;
    }
    
}

<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Database;

/**
 * Hooks Module.
 */
class HooksModule extends AbstractModule implements ModuleConfigInterface, DependentInterface {
    // How to update the database schema for this module
    const SCHEMA_TARGET_VERSION   = 1;
    const SCHEMA_SETTING_NAME     = 'MAJ_HOOKS_SCHEMA_VERSION';
    const SCHEMA_MIGRATION_PREFIX = '\MyArtJaub\Webtrees\Module\Hooks\Schema';
    
    /** @var string For custom modules - link for support, upgrades, etc. */
    const CUSTOM_WEBSITE = 'https://github.com/jon48/webtrees-lib';
    
    /**
     * {@inhericDoc}
     */
    public function getTitle() {
        return /* I18N: Name of the “Hooks” module */ I18N::translate('Hooks');
    }
    
    /**
     * {@inhericDoc}
     */
    public function getDescription() {
        return /* I18N: Description of the “Hooks” module */ I18N::translate('Implements hooks management.');
    }
    
    /**
     * {@inhericDoc}
     */
    public function modAction($mod_action) {
        Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);
        
        \MyArtJaub\Webtrees\Mvc\Dispatcher::getInstance()->handle($this, $mod_action);
    }
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleConfigInterface::getConfigLink()
     */
    public function getConfigLink() {
        Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);
        
        return 'module.php?mod=' . $this->getName() . '&amp;mod_action=AdminConfig';
    }
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\DependentInterface::validatePrerequisites()
     */
    public function validatePrerequisites() {
        try {
            Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);
            return true;
        }
        catch (\Exception $ex) { }
        return false;
    }
    

}
 
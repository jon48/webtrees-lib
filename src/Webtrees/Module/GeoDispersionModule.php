<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisProvider;

/**
 * Geograpgical Dispersion Module.
 */
class GeoDispersionModule extends AbstractModule implements ModuleConfigInterface, DependentInterface {
    
	// How to update the database schema for this module
    const SCHEMA_TARGET_VERSION   = 1;
    const SCHEMA_SETTING_NAME     = 'MAJ_GEODISP_SCHEMA_VERSION';
    const SCHEMA_MIGRATION_PREFIX = '\MyArtJaub\Webtrees\Module\GeoDispersion\Schema';
    
    /** @var string For custom modules - link for support, upgrades, etc. */
    const CUSTOM_WEBSITE = 'https://github.com/jon48/webtrees-lib';
        
    /**
     * GeoDispersion analysis provider
     * @var \MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisProvider $provider
     */
    protected $provider;
    
    /**
     * {@inhericDoc}
     */
    public function getTitle() {
        return /* I18N: Name of the “Hooks” module */ I18N::translate('Geographical Dispersion');
    }
    
    /**
     * {@inhericDoc}
     */
    public function getDescription() {
        return /* I18N: Description of the “Hooks” module */ I18N::translate('Display the geographical dispersion of the root person’s Sosa ancestors.');
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
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Module\DependentInterface::validatePrerequisites()
     */
    public function validatePrerequisites() {
        return !is_null(Module::getModuleByName(Constants::MODULE_MAJ_SOSA_NAME));
    }
    
	/**
	 * Get the GeoAnalysis Provider (initialise it if not done yet).
	 *
	 * @return \MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisProvider
	 */
    public function getProvider() {
        global $WT_TREE;
        
        if(!$this->provider) {
            $this->provider = new GeoAnalysisProvider($WT_TREE);
        }
        return $this->provider;
    }
	
	/**
	 * Set the GeoAnalysis Provider.
	 *
	 * @return \MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisProvider
	 */
    public function setProvider(GeoAnalysisProvider $provider) {
        $this->provider = $provider;
    }
    

}
 
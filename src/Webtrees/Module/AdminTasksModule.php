<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleBlockInterface;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProvider;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface;
/**
 * MiscExtensionsModule.php Module
 */
class AdminTasksModule extends AbstractModule 
implements ModuleConfigInterface, ModuleBlockInterface
{
    // How to update the database schema for this module
    const SCHEMA_TARGET_VERSION   = 1;
    const SCHEMA_SETTING_NAME     = 'MAJ_ADMTASKS_SCHEMA_VERSION';
    const SCHEMA_MIGRATION_PREFIX = '\MyArtJaub\Webtrees\Module\AdminTasks\Schema';
    
    /** @var string For custom modules - link for support, upgrades, etc. */
    const CUSTOM_WEBSITE = 'https://github.com/jon48/webtrees-lib';
    
    /**
     * Admin Task provider
     * @var \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface $provider
     */
    protected $provider;
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::getTitle()
     */
    public function getTitle() {
        return I18N::translate('Administration Tasks');
    }
    
   /**
    * {@inheritDoc}
    * @see \Fisharebest\Webtrees\Module\AbstractModule::getDescription()
    */
    public function getDescription() {
        return I18N::translate('Manage and run nearly-scheduled administration tasks.');
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::modAction()
     */
    public function modAction($mod_action) {
        Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);
                
        \MyArtJaub\Webtrees\Mvc\Dispatcher::getInstance()->handle($this, $mod_action);
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleConfigInterface::getConfigLink()
     */
    public function getConfigLink() {
        Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);
        
        return 'module.php?mod=' . $this->getName() . '&amp;mod_action=AdminConfig';
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::getBlock()
     */
    public function getBlock($block_id, $template = true, $cfg = array()) {
        global $controller;
        
        $controller->addInlineJavascript('
			$(document).ready(function(){
				$.ajax({
					url: "module.php",
					data : {
						mod: "'.$this->getName().'",
						mod_action: "Task@trigger",
					},
				});
			});
		');
        return '';
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::loadAjax()
     */
    public function loadAjax() {
        return false;
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::isGedcomBlock()
     */
    public function isGedcomBlock() {
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::isUserBlock()
     */
    public function isUserBlock() {
        return false;
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::configureBlock()
     */
    public function configureBlock($block_id) {
        
    }
   
/**
	 * Get the Admin Tasks Provider (initialise it if not done yet).
	 *
	 * @return \MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskProviderInterface
	 */
    public function getProvider() {        
        if(!$this->provider) {
            $this->provider = new TaskProvider(WT_ROOT.WT_MODULES_DIR.$this->getName().'/tasks/');
        }
        return $this->provider;
    }
	
	/**
	 * Set the Admin Tasks Provider.
	 *
	 * @return \MyArtJaub\Webtrees\Module\GeoDispersion\Model\TaskProviderInterface
	 */
    public function setProvider(TaskProviderInterface $provider) {
        $this->provider = $provider;
    }
}
 
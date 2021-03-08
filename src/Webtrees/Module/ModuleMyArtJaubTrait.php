<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module;

use Aura\Router\RouterContainer;
use Fisharebest\Localization\Translation;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Webtrees;
use Fisharebest\Webtrees\Module\ModuleCustomTrait;
use Fisharebest\Webtrees\Module\ModuleThemeInterface;

/**
 * MyArtJaub Module
 */
trait ModuleMyArtJaubTrait
{    
    use ModuleCustomTrait;
    
    /**
     * @see \Fisharebest\Webtrees\Module\AbstractModule::boot()
     */
    public function boot() : void
    {
        View::registerNamespace($this->name(), $this->resourcesFolder() . 'views/');
        
        $this->loadRoutes(app(RouterContainer::class)->getMap());
    }
    
    /**
     * @see \Fisharebest\Webtrees\Module\AbstractModule::resourcesFolder()
     */
    public function resourcesFolder(): string
    {
        return Webtrees::MODULES_DIR . trim($this->name(), '_') . '/resources/';
    }
    
    /**
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleAuthorName()
     */
    public function customModuleAuthorName() : string
    {
        return 'Jonathan Jaubart';
    }
    
    /**
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleSupportUrl()
     */
    public function customModuleSupportUrl() : string
    {
        return 'https://github.com/jon48/webtrees-lib';
    }
    
    /**
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customTranslations()
     * 
     * @return array<string, string>
     */
    public function customTranslations(string $language) : array
    {
        $translation_file = $this->resourcesFolder() . 'lang/' . $language . '/messages.php';
        
        try {
            $translation  = new Translation($translation_file);
            return $translation->asArray();
        } catch (\Exception $e) { }
        
        return [];
    }
    
    /**
     * @see \MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface::moduleCssUrl
     */
    public function moduleCssUrl() : string
    {
        /** @var ModuleThemeInterface $theme */
        $theme = app(ModuleThemeInterface::class);
        $css_file = $this->resourcesFolder() . 'css/' . $theme->name() . '.min.css';
        
        if(file_exists($css_file)) {
            return $this->assetUrl('css/' . $theme->name() . '.min.css');
        }
        else {
            return $this->assetUrl('css/default.min.css');
        }
    }
    
}
 
<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage WelcomeBlock
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\WelcomeBlock;

use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Controller\AjaxController;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use Fisharebest\Webtrees\File;
use MyArtJaub\Webtrees\Cache;

/**
 * Controller for Piwik
 */
class PiwikController extends MvcController
{   
    /**
     * Retrieve the number of visitors from Piwik, for a given period.
     * 
     * @param string $block_id
     * @param string $period
     * @param (null|int) Number of visits
     */
    private function getNumberOfVisitsPiwik($block_id, $period='year'){
    
        $piwik_url = $this->module->getBlockSetting($block_id, 'piwik_url');
        $piwik_siteid = $this->module->getBlockSetting($block_id, 'piwik_siteid');
        $piwik_token = $this->module->getBlockSetting($block_id, 'piwik_token');
    
        if($piwik_url && strlen($piwik_url) > 0 &&
            $piwik_siteid  && strlen($piwik_siteid) > 0 &&
            $piwik_token && strlen($piwik_token)            
            ) 
        {        
            // calling Piwik REST API
            $url = $piwik_url;
            $url .= '?module=API&method=VisitsSummary.getVisits';
            $url .= '&idSite='.$piwik_siteid.'&period='.$period.'&date=today';
            $url .= '&format=PHP';
            $url .= '&token_auth='.$piwik_token;
        
            if($fetched = File::fetchUrl($url)) {
                $content = @unserialize($fetched);
                if(is_numeric($content)) return $content;
            }
        }
    
        return null;
    }
    
    /**
     * Pages
     */
        
    /**
     * Piwik@index
     */
    public function index() {  
        
        $ctrl = new AjaxController();
        
        $data = new ViewBag();
        $data->set('has_stats', false);
        
        $block_id = Filter::get('block_id');        
        if($block_id){
            if(Cache::isCached('piwikCountYear', $this->module)) {
                $visitCountYear = Cache::get('piwikCountYear', $this->module);
            }
            else{
                $visitCountYear = $this->getNumberOfVisitsPiwik($block_id);
                Cache::save('piwikCountYear', $visitCountYear, $this->module);
            }
            if($visitCountYear){
                $visitCountToday = max(0, $this->getNumberOfVisitsPiwik($block_id, 'day'));
                $visitCountYear = max( 0, $visitCountYear);
                
                $data->set('has_stats', true);
                $data->set('visits_today', $visitCountToday);
                $data->set('visits_year', $visitCountYear + $visitCountToday);                
            }
        }
        
        ViewFactory::make('PiwikStats', $this, $ctrl, $data)->render();        
    }
    
}
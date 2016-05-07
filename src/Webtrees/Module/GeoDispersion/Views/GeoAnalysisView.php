<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\GeoDispersion\Views;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysis;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for GeoAnalysis@index
 */
class GeoAnalysisView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {        
        ?>                
        <div id="maj-geodisp-list-page" class="center">
			<h2><?php echo $this->data->get('title'); ?></h2>
			
			<?php
			if($this->data->get('has_analysis', false)) { 
			    /** @var GeoAnalysis $ga  */
    		    $ga = $this->data->get('geoanalysis');
    		?>
    		
    		<div id="geodispersion-panel">
    			<h3><?php echo $ga->getTitle() ?></h3>
    			
    			<div id="geodispersion-tabs">
    				<ul>
    					<li>
    						<a href="#geodisp-general"><?php echo I18N::translate('General data'); ?></a>
    					</li>
    					<li>
    						<a href="#geodisp-generations"><?php echo I18N::translate('Data by Generations'); ?></a>
    					</li>
    				</ul>
    				
    				<div id="geodisp-general">
    					<div id="loading-general" class="loading-image">&nbsp;</div>
    					<div id="geodisp-data-general" class="center"></div>
    				</div>
    				
    				<div id="geodisp-generations">
    					<div id="loading-generations" class="loading-image">&nbsp;</div>
    					<div id="geodisp-data-generations"></div>
    				</div>
    			</div>
    		</div>    		
      		
      		<?php } else { ?>
      		<p class="warning"><?php echo I18N::translate('The required dispersion analysis does not exist.'); ?><p>
      		<?php } ?>	
    	</div>
    	
    	<?php 
    }
    
}
 
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
namespace MyArtJaub\Webtrees\Module\GeoDispersion\Views;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for GeoAnalysis@listAll
 */
class GeoAnalysisListView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {        
        ?>                
        <div id="maj-geodisp-list-page" class="center">
			<h2><?php echo $this->data->get('title'); ?></h2>
			
			<?php
			if($this->data->get('has_list', false)) { 
    		    $galist = $this->data->get('geoanalysislist');
    		?>
    		
    		<p class="center"><?php echo I18N::translate('Choose a geographical dispersion analysis:'); ?><p>
			
			<div class="maj-table">
				<?php foreach($galist as $ga) {?>
				<div class="maj-row">
					<div class="label">	
						<?php if($ga->hasMap()) { ?>
						<i class="icon-maj-map" title="<?php echo I18N::translate('Map'); ?>"></i>
						<?php } else { ?>
						<i class="icon-maj-table" title="<?php echo I18N::translate('Table'); ?>"></i>
						<?php } ?>
                	</div>
                	<div class="value">	
                		<a href="<?php echo $ga->getHtmlUrl(); ?>" title="<?php echo $ga->getTitle(); ?>" alt="<?php echo $ga->getTitle(); ?>"><?php echo $ga->getTitle(); ?></a>
                	</div>                	
                </div>
                <?php } ?>
      		</div>	
      		
      		<?php } else { ?>
      		<p class="warning"><?php echo I18N::translate('There is no geographical dispersion analysis to display.'); ?><p>
      		<?php } ?>	
    	</div>
    	
    	<?php 
    }
    
}
 
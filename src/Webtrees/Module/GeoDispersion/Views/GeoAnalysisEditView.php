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

use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysis;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for AdminConfig@edit and AdminConfig@add
 */
class GeoAnalysisEditView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        
        /** @var GeoAnalysis $ga */
        $ga = $this->data->get('geo_analysis');
        $is_new = is_null($ga);
        
        $places_hierarchy = $this->data->get('places_hierarchy');
        ?>        
        <ol class="breadcrumb small">
        	<li><a href="admin.php"><?php echo I18N::translate('Control panel'); ?></a></li>
			<li><a href="admin_modules.php"><?php echo I18N::translate('Module administration'); ?></a></li>
			<li><a href="<?php echo $this->data->get('admin_config_url'); ?>"><?php echo $this->data->get('module_title'); ?></a></li>
			<li class="active"><?php echo $this->data->get('title'); ?></li>
		</ol>
		
		<h1><?php echo $this->data->get('title'); ?></h1>
		
		<form class="form-horizontal" name="newform" method="post" role="form" action="<?php echo $this->data->get('save_url'); ?>" autocomplete="off">
    		<?php echo Filter::getCsrf(); ?>
    		<?php if(!$is_new) { ?>
    		<input type="hidden" name="ga_id" value="<?php echo $ga->getId(); ?>">
    		<?php } ?>
    
    		<!-- DESCRIPTION -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="description">
    				<?php echo I18N::translate('Description'); ?>
    			</label>
    			<div class="col-sm-9">
    				<input class="form-control" type="text" id="description" name="description" required maxlength="70" <?php if(!$is_new) echo 'value="' . Filter::escapeHtml($ga->getTitle()) .'"'; ?> dir="auto">
    				<p class="small text-muted">
    					<?php echo I18N::translate('Description to be given to the geographical dispersion analysis. It will be used as the page title for it.'); ?>
    				</p>
    			</div>
    		</div>
    		
    		<!-- ANALYSIS LEVEL -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="analysislevel">
    				<?php echo I18N::translate('Analysis level'); ?>
    			</label>
    			<div class="col-sm-9">
    				<?php echo FunctionsEdit::selectEditControl('analysislevel', $places_hierarchy['hierarchy'], null, $is_new ? '' : $ga->getAnalysisLevel() - 1, 'class="form-control"'); ?>
				    <p class="small text-muted">
    					<?php echo I18N::translate('Subdivision level used for the analysis.'); ?>
    				</p>
    			</div>
    		</div>
    		
    		<h3><?php echo I18N::translate('Display options'); ?></h3>
    		
    		<!-- USE MAP -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="use_map">
    				<?php echo I18N::translate('Use map'); ?>
    			</label>
    			<div class="col-sm-9">
    				<?php echo FunctionsEdit::editFieldYesNo('use_map', !$is_new && ($ga && $ga->hasMap()) ? 1 : 0, 'class="radio-inline"'); ?>
				    <p class="small text-muted">
    					<?php echo I18N::translate('Displays the results on a map.'); ?>
    				</p>
    			</div>
    		</div>
    		
    		<div id="map_options">
    		
    		    <!-- MAP -->
        		<div class="form-group">
        			<label class="control-label col-sm-3" for="map_file">
        				<?php echo I18N::translate('Map'); ?>
        			</label>
        			<div class="col-sm-9">
        				<?php echo FunctionsEdit::selectEditControl('map_file', $this->data->get('map_list') , null, $is_new || ! $ga->hasMap() ? '' : base64_encode($ga->getOptions()->getMap()->getFileName()), 'class="form-control"'); ?>
        				<p class="small text-muted">
        					<?php echo I18N::translate('Map outline to be used for the result display.'); ?>
        				</p>
        			</div>
        		</div>
        		
        		<!-- MAP TOP LEVEL -->
        		<div class="form-group">
        			<label class="control-label col-sm-3" for="map_top_level">
        				<?php echo I18N::translate('Map parent level'); ?>
        			</label>
        			<div class="col-sm-9">
        				<?php echo FunctionsEdit::selectEditControl('map_top_level', $places_hierarchy['hierarchy'], null, $is_new || ! $ga->hasMap() ? '' : $ga->getOptions()->getMapLevel() - 1, 'class="form-control"'); ?>
        				<p class="small text-muted">
        					<?php echo I18N::translate('Subdivision level of the parent subdivision(s) represented by the map.'); ?><br />
        					<?php echo I18N::translate('For instance, if the map is intended to represent a country by county, then the map parent level would be “Country”, and the analysis level would be “County”.'); ?>
        				</p>
        			</div>
        		</div>
    		</div>
    		
    		<!-- USE FLAGS -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="use_flags">
    				<?php echo I18N::translate('Use flags'); ?>
    			</label>
    			<div class="col-sm-9">
    				<?php echo FunctionsEdit::editFieldYesNo('use_flags', !$is_new && ($ga && $ga->getOptions()->isUsingFlags()) ? 1 : 0, 'class="radio-inline"'); ?>
				    <p class="small text-muted">
    					<?php echo I18N::translate('Display the place flags, instead of or in addition to the place name.'); ?>
    				</p>
    			</div>
    		</div>
    		
    		<!-- GENERATION DETAILS -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="gen_details">
    				<?php echo I18N::translate('Top places number'); ?>
    			</label>
    			<div class="col-sm-9">
    				<?php echo FunctionsEdit::selectEditControl('gen_details', $this->data->get('generation_details'), null, !$is_new && ($ga && $ga->getOptions()->getMaxDetailsInGen()), 'class="form-control"'); ?>
				    <p class="small text-muted">
    					<?php echo I18N::translate('Set the number of top places to display in the generation breakdown view.'); ?>
    				</p>
    			</div>
    		</div>
    		
    		<div class="form-group">
    			<div class="col-sm-offset-3 col-sm-9">
    				<button type="submit" class="btn btn-primary">
    					<?php echo $is_new ? I18N::translate('Add') : I18N::translate('save'); ?>
    				</button>
    			</div>
    		</div>
    	</form>
		
		<?php        
    }
    
}
 
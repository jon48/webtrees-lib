<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Sosa\Views;

use \MyArtJaub\Webtrees\Mvc\View\AbstractView;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module;

/**
 * View for SosaList@index
 */
class SosaListView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {      
        ?>                
        <div id="maj-sosa-list-page" class="center">
			<h2><?php echo $this->data->get('title'); ?></h2>
			
			<?php  if($this->data->get('is_setup')) { 
			    $selectedgen = $this->data->get('generation');
			    $this->renderSosaHeader();
			    if($this->data->get('has_sosa') ) {
			         if($selectedgen > 0) {
			        ?>
			<div id="sosalist-tabs">
				<ul>
					<li><a href="#sosalist-indi"><?php echo I18N::translate('Individuals'); ?></a></li>
					<li><a href="#sosalist-fam"><?php echo I18N::translate('Families'); ?></a></li>
				</ul>
				
				<div id="sosalist-indi">
					<div id="loading-indi" class="loading-image">&nbsp;</div>
					<div id="sosalist-indi-data" class="center"></div>
				</div>

				<div id="sosalist-fam">
					<div id="loading-fam" class="loading-image">&nbsp;</div>
					<div id="sosalist-fam-data"></div>
				</div>
			</div>
			<?php        } else { ?>
			<p class="warning"><?php echo I18N::translate('No ancestor has been found for generation %d', $selectedgen); ?></p>
			<?php    }
			    }
			} else { ?>
			<p class="warning"><?php echo I18N::translate('The list could not be displayed. Reasons might be:'); ?><br/>
				<ul>
					<li><?php echo I18N::translate('No Sosa root individual has been defined.'); ?></li>
					<li><?php echo I18N::translate('The Sosa ancestors have not been computed yet.'); ?></li>
				</ul>
			</p>
			<?php } ?>
		</div> 
		<?php 
    }
    
    /**
     * Render the common header to Sosa Lists, made of the generation selector, and the generation navigator
     */
    protected function renderSosaHeader() {
        $selectedgen = $this->data->get('generation');
        $max_gen = $this->data->get('max_gen');
        ?>
        
    	<form method="get" name="setgen" action="module.php">
			<input type="hidden" name="mod" value="<?php echo $this->data->get('url_module');?>">
			<input type="hidden" name="mod_action" value="<?php echo $this->data->get('url_action');?>">
			<input type="hidden" name="ged" value="<?php echo $this->data->get('url_ged');?>">
			<div class="maj-table">
				<div class="maj-row">
					<div class="label"><?php echo I18N::translate('Choose generation') ?></div>
				</div>
				<div class="maj-row">
					<div class="value">
						<select name="gen">							
						<?php for($i=$this->data->get('min_gen'); $i <= $max_gen;$i++) {?>
							<option value="<?php echo $i; ?>"
							<?php if($selectedgen && $selectedgen==$i) { ?> selected="true" <?php } ?>
                			><?php echo I18N::translate('Generation %d', $i); ?>
                			</option>
                		<?php } ?>
                		</select>
                	</div>
                </div>
      		</div>
      		<input type="submit" value="<?php echo I18N::translate('Show');?>" />
      		<br />
      	</form>
      	<?php if($selectedgen > 0) { ?>
		<h4>
			<?php if($selectedgen > $this->data->get('min_gen')) { ?>
			<a href="module.php?mod=<?php echo $this->data->get('url_module');?>&mod_action=<?php echo $this->data->get('url_action');?>&ged=<?php echo $this->data->get('url_ged');?>&gen=<?php echo $selectedgen-1; ?>">
				<i class="icon-ldarrow" title="<?php echo I18N::translate('Previous generation'); ?>" ></i>
			</a>
			&nbsp;&nbsp;
			<?php } ?>
			<?php echo I18N::translate('Generation %d', $selectedgen); ?>
			<?php if($selectedgen < $max_gen) { ?>
			&nbsp;&nbsp;
			<a href="module.php?mod=<?php echo $this->data->get('url_module');?>&mod_action=<?php echo $this->data->get('url_action');?>&ged=<?php echo $this->data->get('url_ged');?>&gen=<?php echo $selectedgen+1; ?>">
				<i class="icon-rdarrow" title="<?php echo I18N::translate('Next generation'); ?>" ></i>
			</a>
			<?php } ?>
		</h4>
		
		<?php 
		}
    }       
    
}
 
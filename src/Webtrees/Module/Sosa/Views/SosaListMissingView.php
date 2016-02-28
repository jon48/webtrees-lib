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
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\GedcomTag;
use MyArtJaub\Webtrees\Module\ModuleManager;
use MyArtJaub\Webtrees\Constants;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Date;

/**
 * View for SosaList@missing
 */
class SosaListMissingView extends SosaListView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        ?>
            <div id="maj-sosa-missing-page" class="center">
    			<h2><?php echo $this->data->get('title'); ?></h2>
    			
    			<?php  if($this->data->get('is_setup')) { 
    			    $selectedgen = $this->data->get('generation');
    			    $this->renderSosaHeader();
    			    if($this->data->get('has_missing', false)) {
    			        $missing_list = $this->data->get('missing_list');
    			        $table_id = $this->data->get('table_id');
    			        ?>
    			<div id="sosa-indi-missing" class="smissing-list">
                	<table id="<?php echo $table_id;?>">
        				<thead>     
            				<tr>
    							<th colspan="16">
    								<div class="btn-toolbar">
    									<div class="btn-group">
    										<button
    											class="ui-state-default"
    											data-filter-column="15"
    											data-filter-value="M"
    											title="<?php I18N::translate('Show only males.'); ?>"
    											type="button"
    										>
    										<?php echo Individual::sexImage('M', 'large'); ?>
    										</button>
    										<button
    											class="ui-state-default"
    											data-filter-column="15"
    											data-filter-value="F"
    											title="<?php I18N::translate('Show only females.'); ?>"
    											type="button"
    										>
    										<?php echo Individual::sexImage('F', 'large'); ?>
    										</button>
    										<button
    											class="ui-state-default"
    											data-filter-column="15"
    											data-filter-value="U"
    											title="<?php I18N::translate('Show only individuals for whom the gender is not known.'); ?>"
    											type="button"
    										>
    										<?php echo Individual::sexImage('U', 'large'); ?>
    										</button>
    									</div>
    								</div>
    							</th>
    						</tr>       				
        					<tr>
        						<th><?php echo I18N::translate('Sosa'); ?></th>
        						<th><?php echo GedcomTag::getLabel('INDI'); ?></th>
        						<th><?php echo GedcomTag::getLabel('GIVN'); ?></th>
        						<th><?php echo GedcomTag::getLabel('SURN'); ?></th>
        						<th>GIVN</th>
        						<th>SURN</th>
        						<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) { ?>
        						<th><i class="icon-source" title="<?php echo I18N::translate('Sourced individual'); ?>" border="0"></i></th>
        						<th>SORT_BIRTSC</th>
        						<?php } else { ?>
        						<th></th>
        						<th></th>
        						<?php } ?>
        						<th><?php echo Functions::getRelationshipNameFromPath('fat'); ?></th>
								<th><?php echo Functions::getRelationshipNameFromPath('mot'); ?></th>        						
        						<th><?php echo GedcomTag::getLabel('BIRT'); ?></th>
        						<th>SORT_BIRT</th>
        						<th><?php echo GedcomTag::getLabel('PLAC'); ?></th>
        						<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) { ?>
        						<th><i class="icon-source" title="<?php echo I18N::translate('Sourced birth'); ?>" border="0"></i></th>
        						<th>SORT_BIRTSC</th>
        						<?php } else { ?>
        						<th></th>
        						<th></th>
        						<?php } ?>
        						<th>SEX</th>
        					</tr>
        				</thead>
        			<tbody>
        			
        			<?php foreach($this->data->get('missing_list') as $missing_tab) {
        			    $person = $missing_tab['indi'];
        			    
        			    /** @var \Fisharebest\Webtrees\Individual $person */
        			    if ($person->isPendingAddtion()) {
        			        $class = ' class="new"';
        			    } elseif ($person->isPendingDeletion()) {
        			        $class = ' class="old"';
        			    } else {
        			        $class = '';
        			    }
        			    $dperson = new \MyArtJaub\Webtrees\Individual($person);
        			    ?>			
                		<tr <?php echo $class?>>
                			<td class="transparent"><?php echo $missing_tab['sosa']; ?></td>
                			<td class="transparent"><?php echo $person->getXref(); ?></td>
                			<td colspan="2">
                			<?php foreach ($person->getAllNames() as $num=>$name) {
                				if ($name['type']=='NAME') {
                					$title='';
                				} else {
                					$title='title="'.strip_tags(GedcomTag::getLabel($name['type'], $person)).'"';
                				}
                				if ($num==$person->getPrimaryName()) {
                					$class=' class="name2"';
                					$sex_image=$person->getSexImage();
                					list($surn, $givn)=explode(',', $name['sort']);
                				} else {
                					$class='';
                					$sex_image='';
                				} ?>
                				<a <?php echo $title.' '.$class; ?> href="<?php echo $person->getHtmlUrl(); ?>">
                					<?php echo \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($name['full']); ?>
                				</a>
                				<?php echo $sex_image.FunctionsPrint::formatSosaNumbers($dperson->getSosaNumbers(), 1, 'smaller'); ?>
                				<br/>
                    		<?php }
                    		echo $person->getPrimaryParentsNames('parents details1', 'none');
                    		?>
                    		</td>
                    		<td style="display:none;"></td>
                    		<td>
                    			<?php echo Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)) . 'AAAA' . Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)); ?>
                    		</td>
                    		<td>
                    			<?php echo Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)) . 'AAAA' . Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)); ?>
                    		</td>                    		
                			<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) {
        				        $isISourced = $dperson->isSourced(); ?>
        				   	<td><?php echo FunctionsPrint::formatIsSourcedIcon('R', $isISourced, 'INDI', 1, 'medium'); ?></td>
        					<td><?php echo $isISourced; ?></td>
        					<?php } else { ?>
        					<td>&nbsp;</td>
        					<td></td>
        					<?php } ?>
        					<td><?php echo $missing_tab['has_father'] ? '&nbsp;' : 'X';?></td>
        					<td><?php echo $missing_tab['has_mother'] ? '&nbsp;' : 'X';?></td>
        					<td>
                    		<?php 
                    		if ($birth_dates=$person->getAllBirthDates()) {
        			            foreach ($birth_dates as $num=>$birth_date) {
            					   if ($num) { ?><br/><?php } ?>
            						<?php  echo $birth_date->display(true);
        			            }
                    		} else {
                    		    $birth_date=$person->getEstimatedBirthDate();
                    		    if ($person->getTree()->getPreference('SHOW_EST_LIST_DATES')) {
                    		        $birth_date->display(true);
                    		    } else {
                    		        echo '&nbsp;';
                    		    }
                    		    $birth_dates[0] = new Date('');
                    		}
                    		?>
                    		</td>
                    		<td><?php echo $birth_date->julianDay();?></td>
                			<td>
                			<?php foreach ($person->getAllBirthPlaces() as $n => $birth_place) {
        				        $tmp = new \Fisharebest\Webtrees\Place($birth_place, $person->getTree());
                				if ($n) { ?><br><?php } ?>
                				<a href="'<?php echo $tmp->getURL(); ?>" title="<?php echo strip_tags($tmp->getFullName()); ?>">
                					<?php echo \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($tmp->getShortName()); ?>
                				</a>
                			<?php } ?>
                			</td>
        					<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) {
        				        $isBSourced = $dperson->isBirthSourced(); ?>
        				   	<td><?php echo FunctionsPrint::formatIsSourcedIcon('E', $isBSourced, 'BIRT', 1, 'medium'); ?></td>
        					<td><?php echo $isBSourced; ?></td>
        					<?php } else { ?>
        					<td>&nbsp;</td>
        					<td></td>
        					<?php } ?>
        					<td><?php  echo $person->getSex(); ?></td>
        				</tr>
                	<?php } ?>
                	</tbody>
                	<tfoot>
						<tr>
							<td class="ui-state-default" colspan="16">
								<div class="center">
									<?php 
									$missing_diff_count = $this->data->get('missing_diff_count');
									$missing_hidden = $this->data->get('missing_hidden');
									?>
									<?php echo I18N::translate('Number of different missing ancestors: %s', I18N::number($this->data->get('missing_diff_count'))); ?>
									<?php if($this->data->get('missing_hidden') > 0) echo ' ['. I18N::translate('%s hidden', I18N::number($this->data->get('missing_hidden'))).']'; ?>
									<?php echo ' - ' . I18N::translate('Generation complete at %s', I18N::percentage($this->data->get('perc_sosa'), 2)); ?>
									<?php echo ' [' . I18N::translate('Potential %s', I18N::percentage($this->data->get('perc_sosa_potential'),2)).']'; ?>
								</div>
							</td>
						</tr>
					</tfoot>
                </table>
    			 <?php } else { ?> 
    			<p><?php echo I18N::translate('No ancestors are missing for this generation. Generation complete at %s.', I18N::percentage($this->data->get('perc_sosa'), 2)); ?></p>
    			    <?php }   			    
    			} else { ?>
    			<p class="warning"><?php echo I18N::translate('The list could not be displayed. Reasons might be:'); ?><br/>
    				<ul>
    					<li><?php echo I18N::translate('No Sosa root individual has been defined.'); ?></li>
    					<li><?php echo I18N::translate('The Sosa ancestors have not been computed yet.'); ?></li>
    					<li><?php echo I18N::translate('No generation were found.'); ?></li>
    				</ul>
    			</p>
    			<?php } ?>
    		</div> 
    		<?php 
        }
}
 
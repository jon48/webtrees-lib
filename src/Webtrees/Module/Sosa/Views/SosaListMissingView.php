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

use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\GedcomTag;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Functions\FunctionsPrintLists;
use MyArtJaub\Webtrees\Module\ModuleManager;

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
    			<h2><?= $this->data->get('title') ?></h2>
    			
    			<?php  if($this->data->get('is_setup')) { 
    			    $this->renderSosaHeader();
    			    if($this->data->get('has_missing', false)) {
    			        $table_id = $this->data->get('table_id');
    			        ?>
    			<div id="sosa-indi-missing" class="smissing-list">
                	<table id="<?= $table_id ?>">
        				<thead>     
            				<tr>
    							<th colspan="11">
    								<div class="btn-toolbar">
    									<div class="btn-group">
    										<button
    											class="ui-state-default"
    											data-filter-column="10"
    											data-filter-value="M"
    											title="<?php I18N::translate('Show only males.'); ?>"
    											type="button"
    										>
    										<?= Individual::sexImage('M', 'large') ?>
    										</button>
    										<button
    											class="ui-state-default"
    											data-filter-column="10"
    											data-filter-value="F"
    											title="<?php I18N::translate('Show only females.'); ?>"
    											type="button"
    										>
    										<?= Individual::sexImage('F', 'large') ?>
    										</button>
    										<button
    											class="ui-state-default"
    											data-filter-column="10"
    											data-filter-value="U"
    											title="<?php I18N::translate('Show only individuals for whom the gender is not known.'); ?>"
    											type="button"
    										>
    										<?= Individual::sexImage('U', 'large') ?>
    										</button>
    									</div>
    								</div>
    							</th>
    						</tr>       				
        					<tr>
        						<th><?= I18N::translate('Sosa') ?></th>
        						<th><?= GedcomTag::getLabel('INDI') ?></th>
        						<th><?= GedcomTag::getLabel('GIVN') ?></th>
        						<th><?= GedcomTag::getLabel('SURN') ?></th>
        						<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) { ?>
        						<th><i class="icon-source" title="<?= I18N::translate('Sourced individual') ?>" border="0"></i></th>
        						<?php } else { ?>
        						<th></th>
        						<?php } ?>
        						<th><?= Functions::getRelationshipNameFromPath('fat') ?></th>
								<th><?= Functions::getRelationshipNameFromPath('mot') ?></th>        						
        						<th><?= GedcomTag::getLabel('BIRT') ?></th>
        						<th><?= GedcomTag::getLabel('PLAC') ?></th>
        						<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) { ?>
        						<th><i class="icon-source" title="<?= I18N::translate('Sourced birth') ?>" border="0"></i></th>
        						<?php } else { ?>
        						<th></th>
        						<?php } ?>
        						<th hidden>SEX</th>
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
        			    list($surn_givn, $givn_surn) = FunctionsPrintLists::sortableNames($person);
        			    ?>			
                		<tr <?= $class ?>>
                			<td class="transparent"><?= $missing_tab['sosa'] ?></td>
                			<td class="transparent"><?= $person->getXref() ?></td>
                			<td colspan="2" data-sort="<?= Filter::escapeHtml($givn_surn) ?>">
                			<?php foreach ($person->getAllNames() as $num=>$name) {
                				if ($name['type']=='NAME') {
                					$title='';
                				} else {
                					$title='title="'.strip_tags(GedcomTag::getLabel($name['type'], $person)).'"';
                				}
                				if ($num==$person->getPrimaryName()) {
                					$class=' class="name2"';
                					$sex_image=$person->getSexImage();
                				} else {
                					$class='';
                					$sex_image='';
                				} ?>
                				<a <?= $title.' '.$class ?> href="<?= $person->getHtmlUrl() ?>">
                					<?= \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($name['full']) ?>
                				</a>
                				<?= $sex_image;
                				echo implode('&nbsp;',
                				    \MyArtJaub\Webtrees\Hook\HookProvider::getInstance()
                				    ->get('hRecordNameAppend')
                				    ->executeOnlyFor(array(Constants::MODULE_MAJ_SOSA_NAME),  $person, 'smaller'));  ?>
                				<br/>
                    		<?php }
                    		echo $person->getPrimaryParentsNames('parents details1', 'none');
                    		?>
                    		</td>
							<td hidden data-sort="<?= Filter::escapeHtml($surn_givn) ?>"></td>             		
                			<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) {
        				        $isISourced = $dperson->isSourced(); ?>
        				   	<td data-sort="<?= $isISourced ?>"><?= FunctionsPrint::formatIsSourcedIcon('R', $isISourced, 'INDI', 1, 'medium') ?></td>
        					<?php } else { ?>
        					<td>&nbsp;</td>
        					<?php } ?>
        					<td><?= $missing_tab['has_father'] ? '&nbsp;' : 'X' ?></td>
        					<td><?= $missing_tab['has_mother'] ? '&nbsp;' : 'X' ?></td>
                    		<?php $birth_dates = $person->getAllBirthDates(); ?>
                    		<td data-sort="<?= $person->getEstimatedBirthDate()->julianDay() ?>">
                    		<?php                     		
                    		foreach ($birth_dates as $n => $birth_date) {
                    		    if ($n > 0) { ?> <br> <?php  } 
                    		    echo $birth_date->display(true);
                    		}
                    		?>
                    		</td>
                			<td>
                			<?php foreach ($person->getAllBirthPlaces() as $n => $birth_place) {
        				        $tmp = new \Fisharebest\Webtrees\Place($birth_place, $person->getTree());
                				if ($n > 0) { ?><br><?php } ?>
                				<a href="'<?= $tmp->getURL(); ?>" title="<?= strip_tags($tmp->getFullName()) ?>">
                					<?= \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($tmp->getShortName()) ?>
                				</a>
                			<?php } ?>
                			</td>
        					<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) {
        				        $isBSourced = $dperson->isBirthSourced(); ?>
        				   	<td data-sort="<?= $isBSourced ?>"><?= FunctionsPrint::formatIsSourcedIcon('E', $isBSourced, 'BIRT', 1, 'medium') ?></td>
        					<?php } else { ?>
        					<td>&nbsp;</td>
        					<?php } ?>
        					<td hidden><?= $person->getSex() ?></td>
        				</tr>
                	<?php } ?>
                	</tbody>
                	<tfoot>
						<tr>
							<td class="ui-state-default" colspan="11">
								<div class="center">
									<?= I18N::translate('Number of different missing ancestors: %s', I18N::number($this->data->get('missing_diff_count'))) ?>
									<?php if($this->data->get('missing_hidden') > 0) echo ' ['. I18N::translate('%s hidden', I18N::number($this->data->get('missing_hidden'))).']'; ?>
									<?= ' - ' . I18N::translate('Generation complete at %s', I18N::percentage($this->data->get('perc_sosa'), 2)) ?>
									<?= ' [' . I18N::translate('Potential %s', I18N::percentage($this->data->get('perc_sosa_potential'),2)).']' ?>
								</div>
							</td>
						</tr>
					</tfoot>
                </table>
    			 <?php } else if ($this->data->get('generation', 0) > 0) { ?> 
    			<p><?= I18N::translate('No ancestors are missing for this generation. Generation complete at %s.', I18N::percentage($this->data->get('perc_sosa'), 2)) ?></p>
    			    <?php }   			    
    			} else { ?>
    			<p class="warning"><?= I18N::translate('The list could not be displayed. Reasons might be:') ?><br/>
    				<ul>
    					<li><?= I18N::translate('No Sosa root individual has been defined.') ?></li>
    					<li><?= I18N::translate('The Sosa ancestors have not been computed yet.') ?></li>
    					<li><?= I18N::translate('No generation were found.') ?></li>
    				</ul>
    			</p>
    			<?php } ?>
    		</div> 
    		<?php 
        }
}
 
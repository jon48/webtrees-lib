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

use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\GedcomTag;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Place;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Functions\FunctionsPrintLists;
use MyArtJaub\Webtrees\Module\ModuleManager;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for SosaList@sosalist@indi
 */
class SosaListIndiView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() { 
        
        if($this->data->get('has_sosa', false)) {
            $table_id = $this->data->get('table_id');            
        ?>   
        
        <div id="sosa-indi-list" class="sosa-list">
        	<table id="<?= $table_id ?>">
				<thead>
    				<tr>
    					<th colspan="15">
    						<div class="btn-toolbar">
    							<div class="btn-group">
    								<button
    									class="ui-state-default"
    									data-filter-column="11"
    									data-filter-value="M"
    									title="<?= I18N::translate('Show only males.') ?>"
    									type="button"
    								><?= Individual::sexImage('M', 'large') ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="11"
    									data-filter-value="F"
    									title="<?= I18N::translate('Show only females.') ?>"
    									type="button"
    								>
    									<?= Individual::sexImage('F', 'large') ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="11"
    									data-filter-value="U"
    									title="<?= I18N::translate('Show only individuals for whom the gender is not known.') ?>"
    									type="button"
    								>
    									<?= Individual::sexImage('U', 'large') ?>
    								</button>
    							</div>
    							<div class="btn-group">
    								<button
    									class="ui-state-default"
    									data-filter-column="13"
    									data-filter-value="N"
    									title="<?= I18N::translate('Show individuals who are alive or couples where both partners are alive.') ?>"
    									type="button"
    								>
    									<?= I18N::translate('Alive') ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="13"
    									data-filter-value="Y"
    									title="<?= I18N::translate('Show individuals who are dead or couples where both partners are deceased.') ?>"
    									type="button"
    								>
    									<?= I18N::translate('Dead') ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="13"
    									data-filter-value="YES"
    									title="<?= I18N::translate('Show individuals who died more than 100 years ago.') ?>"
    									type="button"
    								><?= GedcomTag::getLabel('DEAT') ?>&gt;100
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="13"
    									data-filter-value="Y100"
    									title="<?= I18N::translate('Show individuals who died within the last 100 years.') ?>"
    									type="button"
    								><?= GedcomTag::getLabel('DEAT') ?>&lt;=100
    								</button>
    							</div>
    							<div class="btn-group">
    								<button
    									class="ui-state-default"
    									data-filter-column="12"
    									data-filter-value="YES"
    									title="<?= I18N::translate('Show individuals born more than 100 years ago.') ?>"
    									type="button"
    								><?= GedcomTag::getLabel('BIRT') ?>&gt;100
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="12"
    									data-filter-value="Y100"
    									title="<?= I18N::translate('Show individuals born within the last 100 years.') ?>"
    									type="button"
    								><?= GedcomTag::getLabel('BIRT') ?>&lt;=100
    								</button>
    							</div>
    							<div class="btn-group">
    								<button
    									class="ui-state-default"
    									data-filter-column="14"
    									data-filter-value="R"
    									title="<?= I18N::translate('Show “roots” couples or individuals.  These individuals may also be called “patriarchs”.  They are individuals who have no parents recorded in the database.') ?>"
    									type="button"
    								>
    									<?= I18N::translate('Roots') ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="14"
    									data-filter-value="L"
    									title="<?= I18N::translate('Show “leaves” couples or individuals.  These are individuals who are alive but have no children recorded in the database.') ?>"
    									type="button"
    								>
    									<?= I18N::translate('Leaves') ?>
    								</button>
    							</div>
    						</div>
    					</th>
    				</tr>
					<tr>
						<th><?= I18N::translate('Sosa') ?></th>
						<th hidden><?= GedcomTag::getLabel('INDI') ?></th>
						<th><?= GedcomTag::getLabel('GIVN') ?></th>
						<th><?= GedcomTag::getLabel('SURN') ?></th>
						<th><?= GedcomTag::getLabel('BIRT') ?></th>
						<th><?= GedcomTag::getLabel('PLAC') ?></th>
						<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) { ?>
						<th><i class="icon-source" title="<?= I18N::translate('Sourced birth') ?>" border="0"></i></th>
						<?php } else { ?>
						<th></th>
						<?php } ?>
						<th><?= GedcomTag::getLabel('DEAT') ?></th>
						<th><?= GedcomTag::getLabel('AGE') ?></th>
						<th><?= GedcomTag::getLabel('PLAC') ?></th>
						<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) { ?>
						<th><i class="icon-source" title="<?= I18N::translate('Sourced death') ?>" border="0"></i></th>
						<?php } else { ?>
						<th></th>
						<?php } ?>
						<th hidden>SEX</th>
						<th hidden>BIRT</th>
						<th hidden>DEAT</th>
						<th hidden>TREE</th>
					</tr>
				</thead>
			<tbody>
			
			<?php foreach($this->data->get('sosa_list') as $sosa => $person) {
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
        		<tr <?= $class ?>>
        			<td class="transparent"><?= $sosa ?></td>
        			<td hidden><?= $person->getXref() ?></td>
        			<?php list($surn_givn, $givn_surn) = FunctionsPrintLists::sortableNames($person); ?>
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
        				<a <?= $title.' '.$class; ?> href="<?= $person->getHtmlUrl() ?>">
        					<?= \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($name['full']) ?>
        				</a>
        				<?= $sex_image.FunctionsPrint::formatSosaNumbers($dperson->getSosaNumbers(), 1, 'smaller') ?>
        				<br/>
            		<?php }
            		echo $person->getPrimaryParentsNames('parents details1', 'none');
            		?>
            		</td>
            		<td hidden data-sort="<?= Filter::escapeHtml($surn_givn) ?>"></td>
            		<?php $birth_dates = $person->getAllBirthDates(); ?>
            		<td data-sort="<?= $person->getEstimatedBirthDate()->julianDay() ?>">
            		<?php foreach ($birth_dates as $n => $birth_date) {
    					   if ($n > 0) { ?><br/><?php } ?>
    						<?php  echo $birth_date->display(true);
			        } ?>
            		</td>
        			<td>
        			<?php foreach ($person->getAllBirthPlaces() as $n => $birth_place) {
				        $tmp = new \Fisharebest\Webtrees\Place($birth_place, $person->getTree());
        				if ($n > 0) { ?><br><?php } ?>
        				<a href="'<?= $tmp->getURL() ?>" title="<?= strip_tags($tmp->getFullName()) ?>">
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
					<?php $death_dates = $person->getAllDeathDates(); ?>
					<td data-sort="<?= $person->getEstimatedDeathDate()->julianDay() ?>">
					<?php foreach ($death_dates as $num => $death_date) {
					       if ($num) { ?><br/><?php } ?>
					 		<?php echo $death_date->display(true); 
				     } ?>
			         </td>
			         <?php if (isset($birth_dates[0]) && isset($death_dates[0])) {
			             $age_at_death = Date::getAge($birth_dates[0], $death_dates[0], 0);
			             $age_at_death_sort = Date::getAge($birth_dates[0], $death_dates[0], 2);
			         } else {
			             $age_at_death      = '';
			             $age_at_death_sort = PHP_INT_MAX;
			         } ?>
			         <td class="center" data-sort="<?= $age_at_death_sort ?>"><?= $age_at_death ?></td>
			         <td>
        			 <?php foreach ($person->getAllDeathPlaces() as $n => $death_place) {
        				$tmp = new Place($death_place, $person->getTree());
        				if ($n) { ?><br><?php } ?>
        				<a href="'<?= $tmp->getURL() ?>" title="<?= strip_tags($tmp->getFullName()) ?>">
        					<?= \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($tmp->getShortName()) ?>
        				</a>
        			<?php } ?>
        			</td>
        			<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) {
        			    if($person->isDead()) {
        			        $isDSourced = $dperson->isDeathSourced(); ?>
				   	<td data-sort=<?= $isDSourced ?>><?= FunctionsPrint::formatIsSourcedIcon('E', $isDSourced, 'DEAT', 1, 'medium') ?></td>
					<?php } else { ?>
					<td data-sort="-99">&nbsp;</td>
					<?php } 
        			} else { ?>
					<td>&nbsp;</td>
					<?php } ?>
					<td hidden><?= $person->getSex() ?></td>
					<td hidden>
					<?php if (!$person->canShow() || Date::compare($person->getEstimatedBirthDate(), new Date(date('Y') - 100)) > 0) {
					    echo 'Y100';
        			} else {
        				echo 'YES';
        			} ?>
        			</td>
        			<td hidden>
        			<?php if (isset($death_dates[0]) && Date::compare($death_dates[0], new Date(date('Y') - 100)) > 0) {
				        echo 'Y100';
        			} elseif ($person->isDead()) {
				        echo 'YES';
        			} else {
				        echo 'N';
			         } ?>
			         </td>
			         <td hidden>
					<?php if (!$person->getChildFamilies()) {
					    echo 'R';
					}  // roots
					elseif (!$person->isDead() && $person->getNumberOfChildren() < 1) {
            			echo 'L';
					} // leaves
					else {
					    echo '&nbsp;';
					} ?>
					</td>
				</tr>
        	<?php } ?>
        	</tbody>
        	<tfoot>
				<tr>
					<th class="ui-state-default" colspan="15">
						<div class="center">
							<?= I18N::translate('Number of Sosa ancestors: %1$s known / %2$s theoretical (%3$s)',
							    I18N::number($this->data->get('sosa_count')),
							    I18N::number($this->data->get('sosa_theo')), 
							    I18N::percentage($this->data->get('sosa_ratio'),2)
							    ) ?>
							<?php if($this->data->get('sosa_hidden') > 0) {
							    echo '['. I18N::translate('%s hidden', I18N::number($this->data->get('sosa_hidden'))).']';
							} ?>
						</div>
					</th>
				</tr>
				<tr>
					<th colspan="15">
						<div class="btn-toolbar">
							<div class="btn-group">
								<button type="button" class="ui-state-default btn-toggle-parents">
									<?= I18N::translate('Show parents') ?>
								</button>
								<button id="btn-toggle-statistics-<?php echo $table_id ;?>" type="button" class="ui-state-default btn-toggle-statistics">
									<?= I18N::translate('Show statistics charts') ?>
								</button>
							</div>
						</div>
					</th>
				</tr>
			</tfoot>
        	</table>
				<div id="indi_list_table-charts_<?php echo $table_id; ?>" style="display:none">
					<table class="list-charts">
						<tr>
							<td><?= $this->data->get('chart_births') ?></td>
							<td><?= $this->data->get('chart_deaths') ?></td>
						</tr>
						<tr>
							<td colspan="2"><? $this->data->get('chart_ages') ?></td>
						</tr>
					</table>
				</div>
			</div>
		<?php 
        }
    }
    
}
 
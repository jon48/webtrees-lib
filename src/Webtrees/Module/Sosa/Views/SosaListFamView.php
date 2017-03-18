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
use MyArtJaub\Webtrees\Family;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Functions\FunctionsPrintLists;
use MyArtJaub\Webtrees\Module\ModuleManager;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for SosaList@sosalist@fam
 */
class SosaListFamView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() { 
        
        if($this->data->get('has_sosa', false)) {
            $table_id = $this->data->get('table_id');            
        ?>   
        
		<div id="sosa-fam-list" class="sosa-list">
			<table id="<?php echo $table_id; ?>">
				<thead>
					<tr>
						<th colspan="14">
							<div class="btn-toolbar">
								<div class="btn-group">
									<button
										type="button"
										data-filter-column="12"
										data-filter-value="N"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show individuals who are alive or couples where both partners are alive.'); ?>"
									>
									<?php echo I18N::translate('Both alive');?>
									</button>
									<button
										type="button"
										data-filter-column="12"
										data-filter-value="W"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples where only the female partner is dead.'); ?>"
									>
									<?php echo I18N::translate('Widower');?>
									</button>
									<button
										type="button"
										data-filter-column="12"
										data-filter-value="H"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples where only the male partner is dead.'); ?>"
									>
									<?php echo I18N::translate('Widow'); ?>
									</button>
									<button
										type="button"
										data-filter-column="12"
										data-filter-value="Y"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show individuals who are dead or couples where both partners are dead.'); ?>"
									>
									<?php echo I18N::translate('Both dead'); ?>
									</button>
								</div>
								<div class="btn-group">
									<button
										type="button"
										data-filter-column="13"
										data-filter-value="R"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show “roots” couples or individuals. These individuals may also be called “patriarchs”. They are individuals who have no parents recorded in the database.'); ?>"
									>
									<?php echo I18N::translate('Roots'); ?>
									</button>
									<button
										type="button"
										data-filter-column="13"
										data-filter-value="L"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show “leaves” couples or individuals. These are individuals who are alive but have no children recorded in the database.'); ?>"
									>
									<?php echo I18N::translate('Leaves'); ?>
									</button>
								</div>
								<div class="btn-group">
									<button
										type="button"
										data-filter-column="11"
										data-filter-value="U"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples with an unknown marriage date.'); ?>"
									>
									<?php echo GedcomTag::getLabel('MARR'); ?>
									</button>
									<button
										type="button"
										data-filter-column="11"
										data-filter-value="YES"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples who married more than 100 years ago.'); ?>"
									>
									<?php echo GedcomTag::getLabel('MARR'); ?>&gt;100
									</button>
									<button
										type="button"
										data-filter-column="11"
										data-filter-value="Y100"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples who married within the last 100 years.'); ?>"
									>
									<?php echo GedcomTag::getLabel('MARR'); ?>&lt;=100
									</button>
									<button
										type="button"
										data-filter-column="11"
										data-filter-value="D"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show divorced couples.'); ?>"
									>
									<?php echo GedcomTag::getLabel('DIV'); ?>
									</button>
									<button
										type="button"
										data-filter-column="11"
										data-filter-value="M"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples where either partner married more than once.'); ?>"
									>
									<?php echo I18N::translate('Multiple marriages'); ?>
									</button>
								</div>
							</div>
						</th>
					</tr>
					<tr>
						<th><?php echo I18N::translate('Sosa'); ?></th>
						<th><?php echo GedcomTag::getLabel('GIVN'); ?></th>
						<th><?php echo GedcomTag::getLabel('SURN'); ?></th>
						<th><?php echo GedcomTag::getLabel('AGE'); ?></th>
						<th><?php echo GedcomTag::getLabel('GIVN'); ?></th>
						<th><?php echo GedcomTag::getLabel('SURN'); ?></th>
						<th><?php echo GedcomTag::getLabel('AGE'); ?></th>
						<th><?php echo GedcomTag::getLabel('MARR'); ?></th>
						<th><?php echo GedcomTag::getLabel('PLAC'); ?></th>';
						<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) { ?>
						<th><i class="icon-source" title="<?php echo I18N::translate('Sourced marriage'); ?>" border="0"></i></th>
						<?php } else { ?>
						<th>&nbsp;</th>
						<?php } ?>
						<th><i class="icon-children" title="<?php echo I18N::translate('Children'); ?>"></i></th>
						<th hidden>MARR</th>
						<th hidden>DEAT</th>
						<th hidden>TREE</th>
					</tr>
				</thead>
				<tbody>
			
			<?php foreach($this->data->get('sosa_list') as $sosa => $family) {
			    /** @var \Fisharebest\Webtrees\Family $person */

			    //PERSO Create decorator for Family
			    $dfamily = new Family($family);
			    
			    $husb = $family->getHusband();
			    if (is_null($husb)) {
			        $husb = new Individual('H', '0 @H@ INDI', null, $family->getTree());
			    }
			    $wife = $family->getWife();
			    if (is_null($wife)) {
			        $wife = new Individual('W', '0 @W@ INDI', null, $family->getTree());
			    }
			    
			    $mdate=$family->getMarriageDate();
			    
			    if ($family->isPendingAddtion()) {
			        $class = ' class="new"';
			    } elseif ($family->isPendingDeletion()) {
			        $class = ' class="old"';
			    } else {
			        $class = '';
			    }
			    ?>			
        		<tr <?= $class ?>>
        			<td class="transparent" data-sort="<?= $sosa ?>"><?= I18N::translate('%1$d/%2$d', $sosa, ($sosa + 1) % 10) ?></td>
        			<!--  HUSBAND -->
        			<?php list($surn_givn, $givn_surn) = FunctionsPrintLists::sortableNames($husb); ?>
        			<td colspan="2" data-sort="<?= Filter::escapeHtml($givn_surn) ?>">        			
        			<?php foreach ($husb->getAllNames() as $num=>$name) {
        				if ($name['type']=='NAME') {
        					$title='';
        				} else {
        					$title='title="'.strip_tags(GedcomTag::getLabel($name['type'], $husb)).'"';
        				}
        				if ($num==$husb->getPrimaryName()) {
        					$class=' class="name2"';
        					$sex_image=$husb->getSexImage();
        				} else {
        					$class='';
        					$sex_image='';
        				} ?>
        				<a <?php echo $title.' '.$class; ?> href="<?php echo $husb->getHtmlUrl(); ?>">
        					<?php echo \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($name['full']); ?>
        				</a>
        				<?php echo $sex_image;
        				echo implode('&nbsp;',
        				    \MyArtJaub\Webtrees\Hook\HookProvider::getInstance()
        				    ->get('hRecordNameAppend')
        				    ->executeOnlyFor(array(Constants::MODULE_MAJ_SOSA_NAME),  $husb, 'smaller')); 
        				?>
        				<br/>
            		<?php }
            		echo $husb->getPrimaryParentsNames('parents details1', 'none');
            		?>
            		</td>
            		<td hidden data-sort="<?= Filter::escapeHtml($surn_givn) ?>"></td>
            		<?php $hdate=$husb->getBirthDate(); ?>
            		<td class="center" data-sort="<?= Date::getAge($hdate, $mdate, 1) ?>"><?= Date::getAge($hdate, $mdate, 2) ?></td>
            		<!--  WIFE -->            		
        			<?php list($surn_givn, $givn_surn) = FunctionsPrintLists::sortableNames($wife); ?>
        			<td colspan="2">
        			<?php foreach ($wife->getAllNames() as $num=>$name) {
        				if ($name['type']=='NAME') {
        					$title='';
        				} else {
        					$title='title="'.strip_tags(GedcomTag::getLabel($name['type'], $wife)).'"';
        				}
        				if ($num==$wife->getPrimaryName()) {
        					$class=' class="name2"';
        					$sex_image=$wife->getSexImage();
        				} else {
        					$class='';
        					$sex_image='';
        				} ?>
        				<a <?= $title.' '.$class ?> href="<?= $wife->getHtmlUrl() ?>">
        					<?= \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($name['full']) ?>
        				</a>
        				<?= $sex_image;
        				echo implode('&nbsp;',
        				    \MyArtJaub\Webtrees\Hook\HookProvider::getInstance()
        				    ->get('hRecordNameAppend')
        				    ->executeOnlyFor(array(Constants::MODULE_MAJ_SOSA_NAME),  $wife, 'smaller'));
        				?>
        				<br/>
            		<?php }
            		echo $wife->getPrimaryParentsNames('parents details1', 'none');
            		?>
            		</td>
            		<td hidden data-sort="<?= Filter::escapeHtml($surn_givn) ?>"></td>
            		<?php $wdate=$wife->getBirthDate(); ?>
            		<td class="center" data-sort="<?= Date::getAge($wdate, $mdate, 1) ?>"><?= Date::getAge($wdate, $mdate, 2) ?></td>
            		<td data-sort="<?= $mdate->julianDay() ?>"><?php 
            		if ($marriage_dates = $family->getAllMarriageDates()) {
        				foreach ($marriage_dates as $n => $marriage_date) {
        					if ($n) { echo '<br>'; } ?>
        					<div><?= $marriage_date->display(true) ?></div>
        				<?php }
            		} elseif ($family->getFacts('_NMR')) {
            		    echo I18N::translate('no');
            		} elseif ($family->getFacts('MARR')) {
            		    echo I18N::translate('yes');
            		} else {
            		    echo '&nbsp;';
            		} ?>
            		</td>
            		<td><?php 
            		foreach ($family->getAllMarriagePlaces() as $n => $marriage_place) {
				        $tmp = new Place($marriage_place, $family->getTree());
        				if ($n) { ?><br><?php } ?>
        				<a href="'<?= $tmp->getURL() ?>" title="<?= strip_tags($tmp->getFullName()) ?>">
        					<?= \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($tmp->getShortName()) ?>
        				</a>
        			<?php  } ?>
        			</td>
        			<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) {
				        $isMSourced = $dfamily->isMarriageSourced(); ?>
				   	<td data-sort=<?= $isMSourced ?>><?= FunctionsPrint::formatIsSourcedIcon('E', $isMSourced, 'MARR', 1, 'medium') ?></td>
					<?php } else { ?>
					<td>&nbsp;</td>
					<?php } ?>
					<td class="center" data-sort="<?= $family->getNumberOfChildren() ?>">
						<?= I18N::number($family->getNumberOfChildren()) ?>
					</td>
					<td hidden><?php 
					if (!$mdate->isOK()) { echo 'U'; }
					else {
					    if (Date::compare($mdate, new Date(date('Y') - 100)) > 0) { echo 'Y100'; }
					    else { echo 'YES'; }
					}
					if ($family->getFacts(WT_EVENTS_DIV)) { echo 'D'; }
					if (count($husb->getSpouseFamilies()) > 1 || count($wife->getSpouseFamilies()) > 1) {
					    echo 'M';
					} ?>
					</td>
					<td hidden><?php 
			         if ($husb->isDead() && $wife->isDead()) { echo 'Y'; }
			         if ($husb->isDead() && !$wife->isDead()) {
        				if ($wife->getSex() == 'F') { echo 'H'; }
        				if ($wife->getSex() == 'M') { echo 'W'; } // male partners
        			}
        			if (!$husb->isDead() && $wife->isDead()) {
        				if ($husb->getSex() == 'M') { echo 'W'; }
        				if ($husb->getSex() == 'F') { echo  'H'; }  // female partners
        			}
        			if (!$husb->isDead() && !$wife->isDead()) { echo 'N'; } ?>
        			</td>
        			<td hidden><?php 
			         if (!$husb->getChildFamilies() && !$wife->getChildFamilies()) { echo 'R'; }
			         elseif (!$husb->isDead() && !$wife->isDead() && $family->getNumberOfChildren() < 1) { echo 'L'; }
			         else { echo '&nbsp;'; } ?>
			         </td>
				</tr>
        	<?php } ?>
        	</tbody>
        	<tfoot>
				<tr>
					<th colspan="14">
						<div class="btn-toolbar">
							<div class="btn-group">
								<button type="button" class="ui-state-default btn-toggle-parents">
									<?= I18N::translate('Show parents') ?>
								</button>
								<button id="btn-toggle-statistics-<?= $table_id ?>" type="button" class="ui-state-default btn-toggle-statistics">
									<?= I18N::translate('Show statistics charts') ?>
								</button>
							</div>
						</div>
					</th>
				</tr>
			</tfoot>
        	</table>
				<div id="fam_list_table-charts_<?php echo $table_id ?>" style="display:none">
					<table class="list-charts">
						<tr>
							<td><?= $this->data->get('chart_births') ?></td>
							<td><?= $this->data->get('chart_marriages') ?></td>
						</tr>
						<tr>
							<td colspan="2"><?= $this->data->get('chart_ages') ?></td>
						</tr>
					</table>
				</div>
			</div>
		<?php } else { ?>
        <p class="warning"><?= I18N::translate('No family has been found for generation %d', $this->data->get('generation')) ?></p>
        <?php 
		}
    }
    
}
 
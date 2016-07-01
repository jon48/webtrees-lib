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
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\GedcomTag;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Place;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Family;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Module\ModuleManager;

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
						<th colspan="24">
							<div class="btn-toolbar">
								<div class="btn-group">
									<button
										type="button"
										data-filter-column="22"
										data-filter-value="N"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show individuals who are alive or couples where both partners are alive.'); ?>"
									>
									<?php echo I18N::translate('Both alive');?>
									</button>
									<button
										type="button"
										data-filter-column="22"
										data-filter-value="W"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples where only the female partner is deceased.'); ?>"
									>
									<?php echo I18N::translate('Widower');?>
									</button>
									<button
										type="button"
										data-filter-column="22"
										data-filter-value="H"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples where only the male partner is deceased.'); ?>"
									>
									<?php echo I18N::translate('Widow'); ?>
									</button>
									<button
										type="button"
										data-filter-column="22"
										data-filter-value="Y"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show individuals who are dead or couples where both partners are deceased.'); ?>"
									>
									<?php echo I18N::translate('Both dead'); ?>
									</button>
								</div>
								<div class="btn-group">
									<button
										type="button"
										data-filter-column="23"
										data-filter-value="R"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show “roots” couples or individuals.  These individuals may also be called “patriarchs”.  They are individuals who have no parents recorded in the database.'); ?>"
									>
									<?php echo I18N::translate('Roots'); ?>
									</button>
									<button
										type="button"
										data-filter-column="23"
										data-filter-value="L"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show “leaves” couples or individuals.  These are individuals who are alive but have no children recorded in the database.'); ?>"
									>
									<?php echo I18N::translate('Leaves'); ?>
									</button>
								</div>
								<div class="btn-group">
									<button
										type="button"
										data-filter-column="21"
										data-filter-value="U"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples with an unknown marriage date.'); ?>"
									>
									<?php echo GedcomTag::getLabel('MARR'); ?>
									</button>
									<button
										type="button"
										data-filter-column="21"
										data-filter-value="YES"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples who married more than 100 years ago.'); ?>"
									>
									<?php echo GedcomTag::getLabel('MARR'); ?>&gt;100
									</button>
									<button
										type="button"
										data-filter-column="21"
										data-filter-value="Y100"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show couples who married within the last 100 years.'); ?>"
									>
									<?php echo GedcomTag::getLabel('MARR'); ?>&lt;=100
									</button>
									<button
										type="button"
										data-filter-column="21"
										data-filter-value="D"
										class="ui-state-default"
										title="<?php echo I18N::translate('Show divorced couples.'); ?>"
									>
									<?php echo GedcomTag::getLabel('DIV'); ?>
									</button>
									<button
										type="button"
										data-filter-column="21"
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
						<th>SOSA</th>
						<th><?php echo GedcomTag::getLabel('GIVN'); ?></th>
						<th><?php echo GedcomTag::getLabel('SURN'); ?></th>
						<th>HUSB:GIVN_SURN</th>
						<th>HUSB:SURN_GIVN</th>
						<th><?php echo GedcomTag::getLabel('AGE'); ?></th>
						<th>AGE</th>
						<th><?php echo GedcomTag::getLabel('GIVN'); ?></th>
						<th><?php echo GedcomTag::getLabel('SURN'); ?></th>
						<th>WIFE:GIVN_SURN</th>
						<th>WIFE:SURN_GIVN</th>
						<th><?php echo GedcomTag::getLabel('AGE'); ?></th>
						<th>AGE</th>
						<th><?php echo GedcomTag::getLabel('MARR'); ?></th>
						<th>MARR:DATE</th>
						<th><?php echo GedcomTag::getLabel('PLAC'); ?></th>';
						<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) { ?>
						<th><i class="icon-source" title="<?php echo I18N::translate('Sourced marriage'); ?>" border="0"></i></th>
						<th>SORT_MARRSC</th>
						<?php } else { ?>
						<th>&nbsp;</th>
						<th></th>
						<?php } ?>
						<th><i class="icon-children" title="<?php echo I18N::translate('Children'); ?>"></i></th>
						<th>NCHI</th>
						<th>MARR</th>
						<th>DEAT</th>
						<th>TREE</th>
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
			    $dhusb = new \MyArtJaub\Webtrees\Individual($husb);
			    $wife = $family->getWife();
			    if (is_null($wife)) {
			        $wife = new Individual('W', '0 @W@ INDI', null, $family->getTree());
			    }
			    $dwife = new \MyArtJaub\Webtrees\Individual($wife);
			    
			    $mdate=$family->getMarriageDate();
			    
			    if ($family->isPendingAddtion()) {
			        $class = ' class="new"';
			    } elseif ($family->isPendingDeletion()) {
			        $class = ' class="old"';
			    } else {
			        $class = '';
			    }
			    ?>			
        		<tr <?php echo $class?>>
        			<td class="transparent"><?php echo I18N::translate('%1$d/%2$d', $sosa, ($sosa + 1) % 10); ?></td>
        			<td class="transparent"><?php echo $sosa; ?></td>
        			<!--  HUSBAND -->
        			<td colspan="2">
        			<?php foreach ($husb->getAllNames() as $num=>$name) {
        				if ($name['type']=='NAME') {
        					$title='';
        				} else {
        					$title='title="'.strip_tags(GedcomTag::getLabel($name['type'], $husb)).'"';
        				}
        				if ($num==$husb->getPrimaryName()) {
        					$class=' class="name2"';
        					$sex_image=$husb->getSexImage();
        					list($surn, $givn)=explode(',', $name['sort']);
        				} else {
        					$class='';
        					$sex_image='';
        				} ?>
        				<a <?php echo $title.' '.$class; ?> href="<?php echo $husb->getHtmlUrl(); ?>">
        					<?php echo \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($name['full']); ?>
        				</a>
        				<?php echo $sex_image.FunctionsPrint::formatSosaNumbers($dhusb->getSosaNumbers(), 1, 'smaller'); ?>
        				<br/>
            		<?php }
            		echo $husb->getPrimaryParentsNames('parents details1', 'none');
            		?>
            		</td>
            		<!-- Dummy column to match colspan in header -->
            		<td style="display:none;"></td>
            		<td>
            			<?php echo Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)) . 'AAAA' . Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)); ?>
            		</td>
            		<td>
            			<?php echo Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)) . 'AAAA' . Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)); ?>
            		</td>
            		<?php $hdate=$husb->getBirthDate(); ?>
            		<td><?php  Date::getAge($hdate, $mdate, 2); ?></td>
            		<td><?php  Date::getAge($hdate, $mdate, 1); ?></td>
            		<!--  WIFE -->
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
        					list($surn, $givn)=explode(',', $name['sort']);
        				} else {
        					$class='';
        					$sex_image='';
        				} ?>
        				<a <?php echo $title.' '.$class; ?> href="<?php echo $wife->getHtmlUrl(); ?>">
        					<?php echo \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($name['full']); ?>
        				</a>
        				<?php echo $sex_image.FunctionsPrint::formatSosaNumbers($dwife->getSosaNumbers(), 1, 'smaller'); ?>
        				<br/>
            		<?php }
            		echo $wife->getPrimaryParentsNames('parents details1', 'none');
            		?>
            		</td>
            		<!-- Dummy column to match colspan in header -->
            		<td style="display:none;"></td>
            		<td>
            			<?php echo Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)) . 'AAAA' . Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)); ?>
            		</td>
            		<td>
            			<?php echo Filter::escapeHtml(str_replace('@N.N.', 'AAAA', $surn)) . 'AAAA' . Filter::escapeHtml(str_replace('@P.N.', 'AAAA', $givn)); ?>
            		</td>
            		<?php $wdate=$wife->getBirthDate(); ?>
            		<td><?php  Date::getAge($wdate, $mdate, 2); ?></td>
            		<td><?php  Date::getAge($wdate, $mdate, 1); ?></td>
            		<td><?php 
            		if ($marriage_dates = $family->getAllMarriageDates()) {
        				foreach ($marriage_dates as $n => $marriage_date) {
        					if ($n) { echo '<br>'; } ?>
        					<div><?php echo $marriage_date->display(true); ?></div>
        				<?php }
            		} elseif ($family->getFacts('_NMR')) {
            		    echo I18N::translate('no');
            		} elseif ($family->getFacts('MARR')) {
            		    echo I18N::translate('yes');
            		} else {
            		    echo '&nbsp;';
            		} ?>
            		</td>
            		<td><?php echo $marriage_dates ? $marriage_date->julianDay() : 0;  ?></td>
            		<td><?php 
            		foreach ($family->getAllMarriagePlaces() as $n => $marriage_place) {
				        $tmp = new Place($marriage_place, $family->getTree());
        				if ($n) { ?><br><?php } ?>
        				<a href="'<?php echo $tmp->getURL(); ?>" title="<?php echo strip_tags($tmp->getFullName()); ?>">
        					<?php echo \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($tmp->getShortName()); ?>
        				</a>
        			<?php  } ?>
        			</td>
        			<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) {
				        $isMSourced = $dfamily->isMarriageSourced(); ?>
				   	<td><?php echo FunctionsPrint::formatIsSourcedIcon('E', $isMSourced, 'MARR', 1, 'medium'); ?></td>
					<td><?php echo $isMSourced; ?></td>
					<?php } else { ?>
					<td>&nbsp;</td>
					<td></td>
					<?php } ?>
					<?php $nchi = $family->getNumberOfChildren(); ?>
					<td><?php echo I18N::number($nchi); ?></td>
					<td><?php echo $nchi; ?></td>
					<td><?php 
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
					<td><?php 
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
        			<td><?php 
			         if (!$husb->getChildFamilies() && !$wife->getChildFamilies()) { echo 'R'; }
			         elseif (!$husb->isDead() && !$wife->isDead() && $family->getNumberOfChildren() < 1) { echo 'L'; }
			         else { echo '&nbsp;'; } ?>
			         </td>
				</tr>
        	<?php } ?>
        	</tbody>
        	<tfoot>
				<tr>
					<th colspan="24">
						<div class="btn-toolbar">
							<div class="btn-group">
								<button type="button" class="ui-state-default btn-toggle-parents">
									<?php echo I18N::translate('Show parents') ?>
								</button>
								<button id="btn-toggle-statistics-<?php echo $table_id ;?>" type="button" class="ui-state-default btn-toggle-statistics">
									<?php echo I18N::translate('Show statistics charts') ?>
								</button>
							</div>
						</div>
					</th>
				</tr>
			</tfoot>
        	</table>
				<div id="fam_list_table-charts_<?php echo $table_id; ?>" style="display:none">
					<table class="list-charts">
						<tr>
							<td><?php echo $this->data->get('chart_births'); ?></td>
							<td><?php echo $this->data->get('chart_marriages'); ?></td>
						</tr>
						<tr>
							<td colspan="2"><?php echo $this->data->get('chart_ages'); ?></td>
						</tr>
					</table>
				</div>
			</div>
		<?php } else { ?>
        <p class="warning"><?php echo I18N::translate('No family has been found for generation %d', $this->data->get('generation')); ?></p>
        <?php 
		}
    }
    
}
 
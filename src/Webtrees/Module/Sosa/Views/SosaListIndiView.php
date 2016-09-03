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
        	<table id="<?php echo $table_id;?>">
				<thead>
    				<tr>
    					<th colspan="22">
    						<div class="btn-toolbar">
    							<div class="btn-group">
    								<button
    									class="ui-state-default"
    									data-filter-column="18"
    									data-filter-value="M"
    									title="<?php echo I18N::translate('Show only males.'); ?>"
    									type="button"
    								><?php echo Individual::sexImage('M', 'large'); ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="18"
    									data-filter-value="F"
    									title="<?php echo I18N::translate('Show only females.'); ?>"
    									type="button"
    								>
    									<?php echo Individual::sexImage('F', 'large'); ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="18"
    									data-filter-value="U"
    									title="<?php echo I18N::translate('Show only individuals for whom the gender is not known.'); ?>"
    									type="button"
    								>
    									<?php echo Individual::sexImage('U', 'large'); ?>
    								</button>
    							</div>
    							<div class="btn-group">
    								<button
    									class="ui-state-default"
    									data-filter-column="20"
    									data-filter-value="N"
    									title="<?php echo I18N::translate('Show individuals who are alive or couples where both partners are alive.'); ?>"
    									type="button"
    								>
    									<?php echo I18N::translate('Alive'); ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="20"
    									data-filter-value="Y"
    									title="<?php echo I18N::translate('Show individuals who are dead or couples where both partners are deceased.'); ?>"
    									type="button"
    								>
    									<?php echo I18N::translate('Dead'); ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="20"
    									data-filter-value="YES"
    									title="<?php echo I18N::translate('Show individuals who died more than 100 years ago.'); ?>"
    									type="button"
    								><?php echo GedcomTag::getLabel('DEAT'); ?>&gt;100
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="20"
    									data-filter-value="Y100"
    									title="<?php echo I18N::translate('Show individuals who died within the last 100 years.'); ?>"
    									type="button"
    								><?php echo GedcomTag::getLabel('DEAT'); ?>&lt;=100
    								</button>
    							</div>
    							<div class="btn-group">
    								<button
    									class="ui-state-default"
    									data-filter-column="19"
    									data-filter-value="YES"
    									title="<?php echo I18N::translate('Show individuals born more than 100 years ago.'); ?>"
    									type="button"
    								><?php echo GedcomTag::getLabel('BIRT'); ?>&gt;100
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="19"
    									data-filter-value="Y100"
    									title="<?php echo I18N::translate('Show individuals born within the last 100 years.'); ?>"
    									type="button"
    								><?php echo GedcomTag::getLabel('BIRT'); ?>&lt;=100
    								</button>
    							</div>
    							<div class="btn-group">
    								<button
    									class="ui-state-default"
    									data-filter-column="21"
    									data-filter-value="R"
    									title="<?php echo I18N::translate('Show “roots” couples or individuals.  These individuals may also be called “patriarchs”.  They are individuals who have no parents recorded in the database.'); ?>"
    									type="button"
    								>
    									<?php echo I18N::translate('Roots'); ?>
    								</button>
    								<button
    									class="ui-state-default"
    									data-filter-column="21"
    									data-filter-value="L"
    									title="<?php echo I18N::translate('Show “leaves” couples or individuals.  These are individuals who are alive but have no children recorded in the database.'); ?>"
    									type="button"
    								>
    									<?php echo I18N::translate('Leaves'); ?>
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
						<th><?php echo GedcomTag::getLabel('DEAT'); ?></th>
						<th>SORT_DEAT</th>
						<th><?php echo GedcomTag::getLabel('AGE'); ?></th>
						<th>AGE</th>
						<th><?php echo GedcomTag::getLabel('PLAC'); ?></th>
						<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) { ?>
						<th><i class="icon-source" title="<?php echo I18N::translate('Sourced death'); ?>" border="0"></i></th>
						<th>SORT_DEATSC</th>
						<?php } else { ?>
						<th></th>
						<th></th>
						<?php } ?>
						<th>SEX</th>
						<th>BIRT</th>
						<th>DEAT</th>
						<th>TREE</th>
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
        		<tr <?php echo $class?>>
        			<td class="transparent"><?php echo $sosa; ?></td>
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
            		<td>
            		<?php 
            		if ($birth_dates=$person->getAllBirthDates()) {
			            foreach ($birth_dates as $num=>$birth_date) {
    					   if ($num) { ?><br/><?php } ?>
    						<?php  echo $birth_date->display(true);
			            }
            		} else {
            		    $birth_date = new Date('');
            		    if ($person->getTree()->getPreference('SHOW_EST_LIST_DATES')) {
            		        $birth_date=$person->getEstimatedBirthDate();
            		        echo $birth_date->display(true);
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
					<td>
					<?php 
					if ($death_dates = $person->getAllDeathDates()) {
				        foreach ($death_dates as $num => $death_date) {
					       if ($num) { ?><br/><?php } ?>
					 		<?php echo $death_date->display(true); 
				        }
			         } else {
				        $death_date = $person->getEstimatedDeathDate();
				        if ($person->getTree()->getPreference('SHOW_EST_LIST_DATES') && $death_date->minimumJulianDay() < WT_CLIENT_JD) {
					       echo $death_date->display(true);
        				} elseif ($person->isDead()) {
        					echo I18N::translate('yes');
        					$death_date = new Date('');
        				} else {
        					echo '&nbsp;';
        					$death_date = new Date('');
        				}
        				$death_dates[0] = new Date('');
			         } ?>
			         </td>
			         <td><?php echo $death_date->julianDay(); ?></td>
			         <td><?php echo Date::getAge($birth_dates[0], $death_dates[0], 2); ?></td>
			         <td><?php echo Date::getAge($birth_dates[0], $death_dates[0], 1); ?></td>
			         <td>
        			 <?php foreach ($person->getAllDeathPlaces() as $n => $death_place) {
        				$tmp = new Place($death_place, $person->getTree());
        				if ($n) { ?><br><?php } ?>
        				<a href="'<?php echo $tmp->getURL(); ?>" title="<?php echo strip_tags($tmp->getFullName()); ?>">
        					<?php echo \Fisharebest\Webtrees\Functions\FunctionsPrint::highlightSearchHits($tmp->getShortName()); ?>
        				</a>
        			<?php } ?>
        			</td>
        			<?php if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME)) {
				        $isDSourced = $dperson->isDeathSourced(); ?>
				   	<td><?php echo FunctionsPrint::formatIsSourcedIcon('E', $isDSourced, 'DEAT', 1, 'medium'); ?></td>
					<td><?php echo $isDSourced; ?></td>
					<?php } else { ?>
					<td>&nbsp;</td>
					<td></td>
					<?php } ?>
					<td><?php  echo $person->getSex(); ?></td>
					<td>
					<?php if (!$person->canShow() || Date::compare($birth_date, new Date(date('Y') - 100)) > 0) {
					    echo 'Y100';
        			} else {
        				echo 'YES';
        			} ?>
        			</td>
        			<td>
        			<?php if (Date::compare($death_dates[0], new Date(date('Y') - 100)) > 0) {
				        echo 'Y100';
        			} elseif ($death_dates[0]->minimumJulianDay() || $person->isDead()) {
				        echo 'YES';
        			} else {
				        echo 'N';
			         } ?>
			         </td>
			         <td>
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
					<th class="ui-state-default" colspan="22">
						<div class="center">
							<?php echo I18N::translate('Number of Sosa ancestors: %1$s known / %2$s theoretical (%3$s)',
							    I18N::number($this->data->get('sosa_count')),
							    I18N::number($this->data->get('sosa_theo')), 
							    I18N::percentage($this->data->get('sosa_ratio'),2)
							    ); ?>
							<?php if($this->data->get('sosa_hidden') > 0) {
							    echo '['. I18N::translate('%s hidden', I18N::number($this->data->get('sosa_hidden'))).']';
							} ?>
						</div>
					</th>
				</tr>
				<tr>
					<th colspan="22">
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
				<div id="indi_list_table-charts_<?php echo $table_id; ?>" style="display:none">
					<table class="list-charts">
						<tr>
							<td><?php echo $this->data->get('chart_births'); ?></td>
							<td><?php echo $this->data->get('chart_deaths'); ?></td>
						</tr>
						<tr>
							<td colspan="2"><?php echo $this->data->get('chart_ages'); ?></td>
						</tr>
					</table>
				</div>
			</div>
		<?php 
        }
    }
    
}
 
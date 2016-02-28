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
 * View for SosaStat@index
 */
class SosaStatsView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {      
        ?>                
        <div id="maj-sosa-stats-page">
			<h2><?php echo $this->data->get('title'); ?></h2>
			
			<?php  if($this->data->get('is_setup')) {  
			    $general_stats = $this->data->get('general_stats'); ?>
			<h3><?php echo I18N::translate('General statistics'); ?></h3>
			<div class="maj-table">
				<div class="maj-row">
					<div class="label"><?php echo I18N::translate('Number of ancestors'); ?></div>
					<div class="value"><?php echo I18N::number($general_stats['sosa_count']); ?></div>
				</div>
				<div class="maj-row">
					<div class="label"><?php echo I18N::translate('Number of different ancestors'); ?></div>
					<div class="value"><?php echo I18N::number($general_stats['distinct_count']); ?></div>
				</div>
				<div class="maj-row">
					<div class="label"><?php echo I18N::translate('%% of ancestors in the base'); ?></div>
					<div class="value"><?php echo I18N::percentage($general_stats['sosa_rate'], 1); ?></div>
				</div>
				<div class="maj-row">
					<div class="label"><?php echo I18N::translate('Pedigree collapse'); ?></div>
					<div class="value"><?php echo I18N::percentage($general_stats['pedi_collapse'], 2); ?></div>
				</div>
				<div class="maj-row">
					<div class="label"><?php echo I18N::translate('Mean generation time'); ?></div>
					<div class="value"><?php echo I18N::plural('%s year', '%s years', $general_stats['mean_gen_time'],  I18N::number($general_stats['mean_gen_time'], 1)); ?></div>
				</div>
			</div>
			
			<h3><?php echo I18N::translate('Statistics by generations'); ?></h3>
			<table class="maj-table">
				<thead>
					<tr class="maj-row">
						<th class="label" colspan="2" >&nbsp;</th>
						<th class="label"><?php  echo I18N::translate('Theoretical'); ?></th>
						<th class="label"><?php  echo I18N::translate('Known'); ?></th>
						<th class="label"><?php  echo I18N::translate('%'); ?></th>
						<th class="label"><?php  echo I18N::translate('Losses G-1'); ?></th>
						<th class="label"><?php  echo I18N::translate('%'); ?></th>
						<th class="label"><?php  echo I18N::translate('Total known'); ?></th>
						<th class="label"><?php  echo I18N::translate('%'); ?></th>
						<th class="label"><?php  echo I18N::translate('Different'); ?></th>
						<th class="label"><?php  echo I18N::translate('%'); ?></th>
						<th class="label"><?php  echo I18N::translate('Total Different'); ?></th>
						<th class="label"><?php  echo I18N::translate('Pedigree collapse'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($this->data->get('generation_stats') as $gen => $row) { ?>
					<tr class="maj-row">
						<td class="label"><?php echo I18N::translate('<strong>G%d</strong>', $gen); ?></td>
						<td class="label"><?php echo I18N::translate('%1$s <> %2$s', $row['gen_min_birth'], $row['gen_max_birth']); ?></td>
						<td class="value"><?php echo I18N::number($row['theoretical']); ?></td>
						<td class="value"><?php echo I18N::number($row['known']); ?></td>
						<td class="value"><?php echo I18N::percentage($row['perc_known'], 2); ?></td>
						<td class="value"><?php echo $row['missing'] > 0 ? '<a href="'.$this->data->get('missinganc_url').$gen.'">'.I18N::number($row['missing']).'</a>' : I18N::number($row['missing']); ?></td>
						<td class="value"><?php echo I18N::percentage($row['perc_missing'], 2); ?></td>
						<td class="value"><?php echo I18N::number($row['total_known']); ?></td>
						<td class="value"><?php echo I18N::percentage($row['perc_total_known'], 2); ?></td>
						<td class="value"><?php echo I18N::number($row['different']); ?></td>
						<td class="value left percent_container">
							<div class="percent_frame">
								<div class="percent_cell" style="width:<?php echo 100*$row['perc_different'] ?>%;">
									&nbsp;<?php echo I18N::percentage($row['perc_different']); ?>&nbsp;
								</div>
							</div>
						</td>
						<td class="value"><?php echo I18N::number($row['total_different']); ?></td>
						<td class="value"><?php echo I18N::percentage($row['pedi_collapse'], 2); ?></td>
					</tr>
					<?php  } ?>
				</tbody>
				<tfoot>
					<tr class="maj-row">
						<td class="label" colspan="13">
							<?php echo I18N::translate('Generation-equivalent: %s generations', I18N::number($this->data->get('equivalent_gen'),2)); ?>
						</td>
					</tr>
				</tfoot>
			</table>
			
			<h3><?php echo I18N::translate('Known Sosa ancestors\' family dispersion'); ?></h3>
			<div class="center">
				<?php echo $this->data->get('chart_img_g2') ?: '' ; ?>
				<?php echo $this->data->get('chart_img_g3') ?: '' ; ?>				
				<!--  <canvas id="chart_ancestors_g2" width="300" height="300"></canvas>  -->
			</div>
			
			<?php   } else { ?>
			<div class="warning"><?php echo I18N::translate('No Sosa root individual has been defined.'); ?></div>
			<?php }     
    }
    
}
 
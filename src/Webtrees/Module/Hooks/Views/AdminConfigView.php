<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Hooks\Views;

use \MyArtJaub\Webtrees\Mvc\View\AbstractView;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\Module;

/**
 * View for Lineage@index
 */
class AdminConfigView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        
        $table_id = $this->data->get('table_id');
        
        ?>        
        <ol class="breadcrumb small">
        	<li><a href="admin.php"><?php echo I18N::translate('Control panel'); ?></a></li>
			<li><a href="admin_modules.php"><?php echo I18N::translate('Module administration'); ?></a></li>
			<li class="active"><?php echo $this->data->get('title'); ?></li>
		</ol>
		
		<div align="center">
			<div id="tabs">
				<form method="post" action="#">
					<?php echo Filter::getCsrf(); ?>
					<input type="hidden" name="action" value="update">
					<table id="<?php echo $table_id; ?>" class="table table-bordered table-condensed table-hover table-site-changes" >
						<thead>
							<tr>
								<th><?php echo I18N::translate('Enabled'); ?></th>
								<th>ENABLED_SORT</th>
								<th><?php echo I18N::translate('Hook Function'); ?></th>
								<th><?php echo I18N::translate('Hook Context'); ?></th>
								<th><?php echo I18N::translate('Module Name'); ?></th>
								<th><?php echo I18N::translate('Priority (1 is high)'); ?></th>
								<th>PRIORITY_SORT</th>
							</tr>
						</thead>
						<tbody> 
							<?php  
							$hooks = $this->data->get('hook_list');
							foreach ($hooks as $id => $hook) { 
							?>
							<tr>
								<td><?php echo FunctionsEdit::twoStateCheckbox('status-'.($hook->id), ($hook->status)=='enabled'); ?></td>
								<td><?php echo (($hook->status)=='enabled'); ?></td>
								<td><?php echo $hook->hook; ?></td>
								<td><?php echo $hook->context; ?></td>
								<td><?php if($mod = Module::getModuleByName($hook->module)) echo $mod->getTitle(); ?></td>
								<td><input type="text" class="center" size="2" value="<?php echo $hook->priority; ?>" name="moduleorder-<?php echo $hook->id; ?>" /></td>
								<td><?php echo $hook->priority; ?></td>
							</tr>
							<?php  } ?>
						</tbody>
					</table>
					<input type="submit" class="btn btn-primary save" value="<?php echo I18N::translate('save'); ?>">
				</form>
			</div>
		</div>	
		
		<?php        
    }
    
}
 
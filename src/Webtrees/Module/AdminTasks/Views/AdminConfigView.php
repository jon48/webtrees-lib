<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\AdminTasks\Views;

use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for AdminConfig@index
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
		
		<h1><?php echo $this->data->get('title'); ?></h1>
		
		<p><?php echo I18N::translate('The administration tasks are meant to be run at a regular interval - or as regularly as possible.'); ?></p>
		<p>
			<?php echo I18N::translate('It is sometimes necessary to force the execution of a task.'); ?><br />
			<?php echo I18N::translate('In order to do so, use the following URL, with the optional parameter <em>%s</em> if you only want to force the execution of one task: ', 'task'); ?>
		</p>
		<p>
			<code><?php echo $this->data->get('trigger_url_root') .'&force=<span id="token_url">'. $this->data->get('trigger_token') .'</span>[&task='. I18N::translate('task_name').']'; ?></code>
		</p>
		<p>
			<button id="bt_genforcetoken" class="bt bt-primary" onClick="generate_force_token();">
				<div id="bt_tokentext"><?php echo I18N::translate('Regenerate token'); ?></div>
			</button>
		</p>

		<table id="<?php echo $table_id; ?>" class="table table-condensed table-bordered">
    		<thead>
    			<tr>
    				<th><?php echo I18N::translate('Edit'); ?></th>
    				<th><!-- task name --></th>
    				<th><?php echo I18N::translate('Enabled'); ?></th>
    				<th><?php echo I18N::translate('Task name'); ?></th>
    				<th><?php echo I18N::translate('Last success'); ?></th>
    				<th><?php echo I18N::translate('Last result'); ?></th>
    				<th><?php echo I18N::translate('Frequency'); ?></th>
    				<th><?php echo I18N::translate('Remaining occurrences'); ?></th>    				
    				<th><?php echo I18N::translate('Is running?'); ?></th>			
    				<th><?php echo I18N::translate('Run task'); ?></th>
    			</tr>
    		</thead>
    		<tbody>
    		</tbody>
    	</table>
		
		<?php        
    }
    
}
 
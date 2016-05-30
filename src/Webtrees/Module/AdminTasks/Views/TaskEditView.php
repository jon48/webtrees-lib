<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\AdminTasks\Views;

use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\AbstractTask;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\ConfigurableTaskInterface;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for Task@edit
 */
class TaskEditView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        
        /** @var AbstractTask $task */
        $task = $this->data->get('task');
        ?>        
        <ol class="breadcrumb small">
        	<li><a href="admin.php"><?php echo I18N::translate('Control panel'); ?></a></li>
			<li><a href="admin_modules.php"><?php echo I18N::translate('Module administration'); ?></a></li>
			<li><a href="<?php echo $this->data->get('admin_config_url'); ?>"><?php echo $this->data->get('module_title'); ?></a></li>
			<li class="active"><?php echo $this->data->get('title'); ?></li>
		</ol>
		
		<h1><?php echo $this->data->get('title'); ?></h1>
		
		<form class="form-horizontal" name="newform" method="post" role="form" action="<?php echo $this->data->get('save_url'); ?>" autocomplete="off">
    		<?php echo Filter::getCsrf(); ?>
    		<input type="hidden" name="task" value="<?php echo $task->getName(); ?>">
    
			<h3><?php echo I18N::translate('General'); ?></h3>
	
    		<!-- FREQUENCY -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="frequency">
    				<?php echo I18N::translate('Frequency'); ?>
    			</label>
    			<div class="col-sm-9">
    				<div class="row">
    					<div class="col-sm-4">
            				<div class="input-group" >
                				<input class="form-control" type="number" min="0" id="frequency" name="frequency" required maxlength="70" value="<?php echo $task->getFrequency(); ?>" dir="auto">
            					<span class="input-group-addon">
            						<?php echo I18N::translate('minutes'); ?>
            					</span>
        					</div>
        				</div>
        			</div>
    				<p class="small text-muted">
    					<?php echo I18N::translate('Frequency at which the task should be run (in minutes).'); ?>
						<?php echo I18N::translate('The actual run of the task may not be fired exactly at the frequency defined, but should be run as close as possible to it.'); ?>
    				</p>
    			</div>
    		</div>
			
			<!-- LIMITED OCCURRENCES -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="is_limited">
    				<?php echo I18N::translate('Run a limited number of times'); ?>
    			</label>
    			<div class="col-sm-9">
    				<?php echo FunctionsEdit::editFieldYesNo('is_limited', $task->getRemainingOccurrences() > 0 ? 1 : 0, 'class="radio-inline"'); ?>
				    <p class="small text-muted">
    					<?php echo I18N::translate('Defines whether the task should be run only a limited number of times.'); ?>
    				</p>
    			</div>
    		</div>
			
			<!-- NB_OCCURRENCES -->
    		<div id="nb_occurences" class="form-group">
    			<label class="control-label col-sm-3" for="nb_occur">
    				<?php echo I18N::translate('Number of occurrences'); ?>
    			</label>
    			<div class="col-sm-9">
    				<div class="row">
    					<div class="col-sm-3">
            				<div class="input-group" >
                				<input class="form-control" type="number" min="0" id="nb_occur" name="nb_occur" maxlength="70" value="<?php echo $task->getRemainingOccurrences(); ?>" dir="auto">
            					<span class="input-group-addon">
            						<?php echo I18N::translate('time(s)'); ?>
            					</span>
        					</div>
        				</div>
        			</div>
    				<p class="small text-muted">
    					<?php echo I18N::translate('Defines the number of times the task will run.'); ?>
    				</p>
    			</div>
    		</div>
			
			<?php if($task instanceof ConfigurableTaskInterface) { ?>
			
			<h3><?php echo I18N::translate('Options for “%s”', $task->getTitle()); ?></h3>
			
			<?php echo $task->htmlConfigForm(); ?>
			
			<?php } ?>
    		
    		<div class="form-group">
    			<div class="col-sm-offset-3 col-sm-9">
    				<button type="submit" class="btn btn-primary">
    					<?php echo I18N::translate('save'); ?>
    				</button>
    			</div>
    		</div>
    	</form>
		
		<?php        
    }
    
}
 
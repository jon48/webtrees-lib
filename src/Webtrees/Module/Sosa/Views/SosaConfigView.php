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
use Fisharebest\Webtrees\Functions\FunctionsPrint;
use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for SosaConfig@index
 */
class SosaConfigView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        
        ?>        
        
        <div id="maj-sosa-config-page">
			<h2><?php echo $this->data->get('title'); ?></h2>
			
			<form name="maj-sosa-config-form" method="post" action="<?php echo $this->data->get('form_url'); ?>">
				<input type="hidden" name="action" value="update">
				<?php echo Filter::getCsrf(); ?>
				<div id="maj-sosa-config-page-table">
					<div class="label">
        				<?php echo I18N::translate('Tree'); ?>
        			</div>
        			<div class="value">
    					<label><?php echo $this->data->get('tree')->getTitleHtml(); ?></label>
        			</div>
        			<div class="label">
        				<?php echo I18N::translate('For user'); ?>
        			</div>
        			<div class="value">
        				<?php 
        				    $users = $this->data->get('users_settings');
        				    if(count($users) == 1) {
        				        $root_indi = $users[0]['rootid'];  ?>
        					<label>
        						<input id="maj_sosa_input_userid" type="hidden" name="userid" value="<?php echo $users[0]['user']->getUserId(); ?>" />
        						<?php echo $users[0]['user']->getRealNameHtml() ?>
        					</label>
        				<?php  } else if(count($users) > 1) { ?>
        					<select id='maj-sosa-config-select' name="userid">
        					<?php 
        					   $root_indi = $users[0]['rootid'];
        					   foreach ($this->data->get('users_settings') as $user) { ?>
        						<option value="<?php echo $user['user']->getUserId(); ?>"><?php echo $user['user']->getRealNameHtml() ?></option>
        					<?php  } ?>
        					</select>
        				<?php } ?>
        			</div>
        			<div class="label">
        				<?php echo I18N::translate('Root individual'); ?>
        			</div>
        			<div class="value">
        				<input data-autocomplete-type="INDI" type="text" name="rootid" id="rootid" size="3" value="<?php echo $root_indi; ?>">
						<?php echo FunctionsPrint::printFindIndividualLink('rootid'); ?>
        			</div>
        			<div class="label"></div>
        			<div class="value">
        				<input type="submit" value="<?php echo /* I18N: button label */ I18N::translate('Save'); ?>">
        				<input type="button" value="<?php echo /* I18N: button label */ I18N::translate('Compute'); ?>" id="bt_sosa_compute">
        				<span id="bt_sosa_computing"></span>
        			</div>
				</div>
			</form>	
		
		<?php        
    }
    
}
 
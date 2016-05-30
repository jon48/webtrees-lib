<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage MiscExtensions
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\MiscExtensions\Views;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Module\CkeditorModule;
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
        
        if (Module::getModuleByName('ckeditor')) {
            CkeditorModule::enableEditor($this->ctrl);
        }
        
        /** @var AbstractModule $module  */
        $module = $this->data->get('module');        
        ?>        
        <ol class="breadcrumb small">
        	<li><a href="admin.php"><?php echo I18N::translate('Control panel'); ?></a></li>
			<li><a href="admin_modules.php"><?php echo I18N::translate('Module administration'); ?></a></li>
			<li class="active"><?php echo $this->data->get('title'); ?></li>
		</ol>
		
		<h1><?php echo $this->data->get('title'); ?></h1>

		<form method="post" class="form-horizontal">
			<?php echo Filter::getCsrf(); ?>
			<input type="hidden" name="action" value="update">
			
			<h3><?php echo I18N::translate('Titles'); ?></h3>
			
			<!--  MAJ_TITLE_PREFIX -->        	
        	<div class="form-group">			
        		<label for="MAJ_TITLE_PREFIX" class="col-sm-3 control-label">
        			<?php echo I18N::translate('Title prefixes'); ?>
        		</label>
    			<div class="col-sm-9">
    				<input type="text" class="form-control" dir="auto" id="MAJ_TITLE_PREFIX" name="MAJ_TITLE_PREFIX" value="<?php echo Filter::escapeHtml($module->getSetting('MAJ_TITLE_PREFIX')); ?>" maxlength="255" placeholder="de |d'|du |of |von |vom |am |zur |van |del |della |t'|da |ten |ter |das |dos |af ">
        			<p class="small text-muted">
        				<?php echo I18N::translate('Set possible aristocratic particles to separate titles from the land they refer to (e.g. Earl <strong>of</strong> Essex). Variants must be separated by the character |.'); ?><br />
        				<?php echo I18N::translate('An example for this setting is : <strong>de |d\'|du |of |von |vom |am |zur |van |del |della |t\'|da |ten |ter |das |dos |af </strong> (covering some of French, English, German, Dutch, Italian, Spanish, Portuguese, Swedish common particles).'); ?>
        			</p>
        		</div>        		
        	</div>
        	
        	<h3><?php echo I18N::translate('Header'); ?></h3>
        	
        	<!-- MAJ_ADD_HTML_HEADER -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="MAJ_ADD_HTML_HEADER">
    				<?php echo I18N::translate('Include additional HTML in header'); ?>
    			</label>
    			<div class="col-sm-9">
    				<?php echo FunctionsEdit::editFieldYesNo('MAJ_ADD_HTML_HEADER', $module->getSetting('MAJ_ADD_HTML_HEADER', 0), 'class="radio-inline"'); ?>
				    <p class="small text-muted">
    					<?php echo I18N::translate('Enable this option to include raw additional HTML in the header of the page.'); ?>
    				</p>
    			</div>
    		</div>
        	
        	<!-- MAJ_SHOW_HTML_HEADER -->
        	<div class="form-group">
        		<label class="control-label col-sm-3" for="MAJ_SHOW_HTML_HEADER">
        			<?php echo I18N::translate('Hide additional header'); ?>
        		</label>
        		<div class="col-sm-9">
        			<?php echo FunctionsEdit::editFieldAccessLevel('MAJ_SHOW_HTML_HEADER', $module->getSetting('MAJ_SHOW_HTML_HEADER', Auth::PRIV_HIDE), 'class="form-control"'); ?>
        			<p class="small text-muted">
        				<?php echo I18N::translate('Select the access level until which the additional header should be displayed. The <em>Hide from everyone</em> should be used to show the header to everybody.'); ?>
        			</p>
        		</div>
        	</div>
        	
        	<!--  MAJ_HTML_HEADER -->        	
        	<div class="form-group">			
        		<label for="MAJ_HTML_HEADER" class="col-sm-3 control-label">
        			<?php echo I18N::translate('Additional HTML in header'); ?>
        		</label>
    			<div class="col-sm-9">
    				<textarea class="form-control html-edit" rows="10" dir="auto" id="MAJ_HTML_HEADER" name="MAJ_HTML_HEADER" ><?php echo Filter::escapeHtml($module->getSetting('MAJ_HTML_HEADER')); ?></textarea>
        			<p class="small text-muted">
        				<?php echo I18N::translate('If the option has been enabled, the saved HTML will be inserted in the header.'); ?><br>
        				<?php echo I18N::translate('In edit mode, the HTML characters might have been transformed to their HTML equivalents (for instance &amp;gt; for &gt;), it is however possible to insert HTML characters, they will be automatically converted to their equivalent values.'); ?>
        			</p>
        		</div>        		
        	</div>
        	
        	<h3><?php echo I18N::translate('Footer'); ?></h3>
        	
        	<!-- MAJ_DISPLAY_CNIL -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="MAJ_DISPLAY_CNIL">
    				<?php echo I18N::translate('Display French <em>CNIL</em> disclaimer'); ?>
    			</label>
    			<div class="col-sm-9">
    				<?php echo FunctionsEdit::editFieldYesNo('MAJ_DISPLAY_CNIL', $module->getSetting('MAJ_DISPLAY_CNIL', 0), 'class="radio-inline"'); ?>
				    <p class="small text-muted">
    					<?php echo I18N::translate('Enable this option to display an information disclaimer in the footer required by the French <em>CNIL</em> for detaining personal information on users.'); ?>
    				</p>
    			</div>
    		</div>
    		
    		<!--  MAJ_CNIL_REFERENCE -->        	
        	<div class="form-group">			
        		<label for="MAJ_CNIL_REFERENCE" class="col-sm-3 control-label">
        			<?php echo I18N::translate('<em>CNIL</em> reference'); ?>
        		</label>
    			<div class="col-sm-9">
    				<input type="text" class="form-control" dir="auto" id="MAJ_CNIL_REFERENCE" name="MAJ_CNIL_REFERENCE" value="<?php echo Filter::escapeHtml($module->getSetting('MAJ_CNIL_REFERENCE')); ?>" maxlength="255">
        			<p class="small text-muted">
        				<?php echo I18N::translate('If the website has been notified to the French <em>CNIL</em>, an authorisation number may have been delivered. Providing this reference will display a message in the footer visible to all users.'); ?>
        			</p>
        		</div>        		
        	</div>
        	
        	<!-- MAJ_ADD_HTML_FOOTER -->
    		<div class="form-group">
    			<label class="control-label col-sm-3" for="MAJ_ADD_HTML_FOOTER">
    				<?php echo I18N::translate('Include additional HTML in footer'); ?>
    			</label>
    			<div class="col-sm-9">
    				<?php echo FunctionsEdit::editFieldYesNo('MAJ_ADD_HTML_FOOTER', $module->getSetting('MAJ_ADD_HTML_FOOTER', 0), 'class="radio-inline"'); ?>
				    <p class="small text-muted">
    					<?php echo I18N::translate('Enable this option to include raw additional HTML in the footer of the page.'); ?>
    				</p>
    			</div>
    		</div>
        	
        	<!-- MAJ_SHOW_HTML_FOOTER -->
        	<div class="form-group">
        		<label class="control-label col-sm-3" for="MAJ_SHOW_HTML_FOOTER">
        			<?php echo I18N::translate('Hide additional footer'); ?>
        		</label>
        		<div class="col-sm-9">
        			<?php echo FunctionsEdit::editFieldAccessLevel('MAJ_SHOW_HTML_FOOTER', $module->getSetting('MAJ_SHOW_HTML_FOOTER', Auth::PRIV_HIDE), 'class="form-control"'); ?>
        			<p class="small text-muted">
        				<?php echo I18N::translate('Select the access level until which the additional footer should be displayed. The <em>Hide from everyone</em> should be used to show the footer to everybody.'); ?>
        			</p>
        		</div>
        	</div>
        	
        	<!--  MAJ_HTML_FOOTER -->        	
        	<div class="form-group">			
        		<label for="MAJ_HTML_FOOTER" class="col-sm-3 control-label">
        			<?php echo I18N::translate('Additional HTML in footer'); ?>
        		</label>
    			<div class="col-sm-9">
    				<textarea class="form-control html-edit" rows="10" dir="auto" id="MAJ_HTML_FOOTER" name="MAJ_HTML_FOOTER" ><?php echo Filter::escapeHtml($module->getSetting('MAJ_HTML_FOOTER')); ?></textarea>
        			<p class="small text-muted">
        				<?php echo I18N::translate('If the option has been enabled, the saved HTML will be inserted in the footer, before the logo.'); ?><br>
        				<?php echo I18N::translate('In edit mode, the HTML characters might have been transformed to their HTML equivalents (for instance &amp;gt; for &gt;), it is however possible to insert HTML characters, they will be automatically converted to their equivalent values.'); ?>
        			</p>
        		</div>        		
        	</div>
        	
        	<div class="form-group">
        		<div class="col-sm-offset-3 col-sm-9">
        			<button type="submit" class="btn btn-primary">
        				<i class="fa fa-check"></i>
        				<?php echo I18N::translate('save'); ?>
        			</button>
        		</div>
        	</div>
        	
        </form>
		
		<?php        
    }
    
}
 
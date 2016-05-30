<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Certificates\Views;

use \MyArtJaub\Webtrees\Mvc\View\AbstractView;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Auth;

/**
 * View for AdminConfig@index
 */
class AdminConfigView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        
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
			
			<h3><?php echo I18N::translate('General'); ?></h3>
			
			<!--  MAJ_CERT_ROOTDIR -->        	
        	<div class="form-group">
        		<label class="control-label col-sm-3" for="MAJ_CERT_ROOTDIR">
        			<?php echo I18N::translate('Certificates directory'); ?>
        		</label>
        		<div class="col-sm-9">
        			<div class="input-group">
        				<span class="input-group-addon">
        					<?php echo WT_DATA_DIR; ?>
        				</span>
        				<input
        					class="form-control"
        					dir="ltr"
        					id="MAJ_CERT_ROOTDIR"
        					maxlength="255"
        					name="MAJ_CERT_ROOTDIR"
        					type="text"
        					value="<?php echo Filter::escapeHtml($module->getSetting('MAJ_CERT_ROOTDIR', 'certificates/')); ?>"
        					required
        				>
        			</div>
        			<p class="small text-muted">
        				<?php echo I18N::translate('This folder will be used to store the certificate files.'); ?>
        				<?php echo I18N::translate('If you select a different folder, you must also move any certificate files from the existing folder to the new one.'); ?>
        			</p>
        		</div>
        	</div>
        	
        	<!-- MAJ_SHOW_CERT -->
        	<div class="form-group">
        		<label class="control-label col-sm-3" for="MAJ_SHOW_CERT">
        			<?php echo I18N::translate('Show certificates'); ?>
        		</label>
        		<div class="col-sm-9">
        			<?php echo FunctionsEdit::editFieldAccessLevel('MAJ_SHOW_CERT', $module->getSetting('MAJ_SHOW_CERT', Auth::PRIV_HIDE), 'class="form-control"'); ?>
        			<p class="small text-muted">
        				<?php echo I18N::translate('Define access level required to display certificates in facts sources. By default, nobody can see the certificates.'); ?>
        			</p>
        		</div>
        	</div>
        	
        	<h3><?php echo I18N::translate('Watermarks'); ?></h3>
        	
        	<!-- MAJ_SHOW_NO_WATERMARK -->
        	<div class="form-group">
        		<label class="control-label col-sm-3" for="MAJ_SHOW_NO_WATERMARK">
        			<?php echo I18N::translate('Show certificates without watermark'); ?>
        		</label>
        		<div class="col-sm-9">
        			<?php echo FunctionsEdit::editFieldAccessLevel('MAJ_SHOW_NO_WATERMARK', $module->getSetting('MAJ_SHOW_NO_WATERMARK', Auth::PRIV_HIDE), 'class="form-control"'); ?>
        			<p class="small text-muted">
        				<?php echo I18N::translate('Define access level of users who can see certificates without any watermark. By default, everybody will see the watermark.'); ?>
        			</p>
        			<p class="small text-muted">
        				<?php echo I18N::translate('When displayed, the watermark is generated from the name of the repository and of the sources, if they exist. Otherwise, a default text is displayed.'); ?>
        			</p>
        		</div>
        	</div>
        	
        	<!--  MAJ_WM_DEFAULT -->
			<div class="form-group">			
        		<label for="MAJ_WM_DEFAULT" class="col-sm-3 control-label">
        			<?php echo I18N::translate('Default watermark'); ?>
        		</label>
    			<div class="col-sm-9">
    				<input type="text" class="form-control" dir="ltr" id="MAJ_WM_DEFAULT" name="MAJ_WM_DEFAULT" value="<?php echo Filter::escapeHtml($module->getSetting('MAJ_WM_DEFAULT')); ?>" maxlength="255" placeholder="<?php echo I18N::translate('This image is protected under copyright law.')?>">
        			<p class="small text-muted">
        				<?php echo I18N::translate('Text to be displayed by default if no source has been associated with the certificate.'); ?>
        			</p>
        		</div>        		
        	</div>
        	
        	<!--  MAJ_WM_FONT_COLOR -->
			<div class="form-group">			
        		<label for="MAJ_WM_FONT_COLOR" class="col-sm-3 control-label">
        			<?php echo I18N::translate('Watermark font color'); ?>
        		</label>
    			<div class="col-sm-9">
    				<div class="row">
    				    <!--  MAJ_WM_FONT_COLOR -->
        				<div class="col-sm-3">
        					<div class="input-group">
        						<label class="input-group-addon" for="MAJ_WM_FONT_COLOR">
        							<?php echo I18N::translate('Color'); ?>
        						</label>
        						<input type="color" class="form-control" dir="ltr" id="MAJ_WM_FONT_COLOR" name="MAJ_WM_FONT_COLOR" value="<?php echo Filter::escapeHtml($module->getSetting('MAJ_WM_FONT_COLOR', '#4D6DF3')); ?>" maxlength="11">
        					</div>
    					</div>
	   				</div>
    				<p class="small text-muted">
        				<?php echo I18N::translate('Font color for the watermark. By default, <span style="color:#4d6df3;">the color #4D6DF3</span> is used.'); ?>
        			</p>
        		</div>        		
        	</div>
        	        	
			<div class="form-group">
				<legend class="control-label col-sm-3">
					<?php echo I18N::translate('Watermark font size'); ?>
				</legend>
    			<div class="col-sm-9">    			
    				<div class="row">
    					<!--  MAJ_WM_FONT_MAXSIZE -->
    					<div class="col-sm-4">
        					<div class="input-group">
        						<label class="input-group-addon" for="MAJ_WM_FONT_MAXSIZE">
        							<?php echo I18N::translate('Maximum font size'); ?>
        						</label>
        						<input
        							class="form-control"
        							dir="ltr"
        							id="MAJ_WM_FONT_MAXSIZE"
        							maxlength="2"
        							name="MAJ_WM_FONT_MAXSIZE"
        							type="number"
        							placeholder="18"
        							value="<?php echo Filter::escapeHtml($module->getSetting('MAJ_WM_FONT_MAXSIZE')); ?>"
        							>        							
        						<span class="input-group-addon">
        							<?php echo I18N::translate('pixels'); ?>
        						</span>
        					</div>
    					</div>
    				</div>    			
    				<p class="small text-muted">
        				<?php echo I18N::translate('Watermark font size'); ?>
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
 
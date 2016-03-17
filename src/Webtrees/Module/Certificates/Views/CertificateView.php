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
use Fisharebest\Webtrees\Module;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use Fisharebest\Webtrees\Functions\FunctionsPrintLists;

/**
 * View for Certificate@index
 */
class CertificateView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        ?>                
        <div id="maj-cert-page" class="center">
			<h2><?php echo $this->data->get('title'); ?></h2>
			
    		<?php if($this->data->get('has_certif', false)) { 
    		    /** @var Certificate $certificate   */
    		  $certificate = $this->data->get('certificate');
    		  $has_linked_indis = $this->data->get('has_linked_indis', false);
    		  $has_linked_fams = $this->data->get('has_linked_fams', false);
    		?>
    		<div id="certificate-details">
        		<h3>
        			<?php echo $certificate->getCity(); ?>
        		</h3>
        		<h4>
        			<a href="<?php echo $this->data->get('url_certif_city'); ?>">
        			[<?php echo I18N::translate('See all certificates for %s', $certificate->getCity()); ?>]
        			</a>
        		</h4>
    			<div id="certificate-tabs">
    				<div id="certificate-edit">
    					<?php echo $certificate->displayImage(); ?>
    				</div>
    				<?php if($has_linked_indis || $has_linked_fams) { ?>
    				<ul>
    					<?php if($has_linked_indis) { ?>
    					<li>
    						<a href="#indi-certificate">
    							<span id="indisource"><?php echo I18N::translate('Individuals'); ?></span>
    						</a>
    					</li>
    					<?php } ?>
    					<?php if($has_linked_fams) { ?>
    					<li>
    						<a href="#fam-certificate">
    							<span id="famsource"><?php echo I18N::translate('Families'); ?></span>
    						</a>
    					</li>
    					<?php } ?>
    				</ul>
    				
    				<?php if($has_linked_indis) { ?>
    				<div id="indi-certificate">
    					<?php echo FunctionsPrintLists::individualTable($this->data->get('linked_indis')); ?>
    				</div>
					<?php } ?>
					
					<?php if($has_linked_fams) { ?>
    				<div id="fam-certificate">
    					<?php echo FunctionsPrintLists::familyTable($this->data->get('linked_fams')); ?>
    				</div>
					<?php } ?>
					
    				<?php } ?>
    			</div>
    		</div>
    		<?php } ?>
    	</div>
    	
    	<?php 
    }
    
}
 
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

use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for Certificate@listAll
 */
class CertificatesListView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        
        $cities = $this->data->get('cities');
        $selected_city = $this->data->get('selected_city');
        
        ?>                
        <div id="maj-cert-list-page" class="center">
			<h2><?= $this->data->get('title') ?></h2>
			
			<form method="get" name="selcity" action="module.php">
    			<input type="hidden" name="mod" value="<?= $this->data->get('url_module') ?>">
    			<input type="hidden" name="mod_action" value="<?= $this->data->get('url_action') ?>">
    			<input type="hidden" name="ged" value="<?= $this->data->get('url_ged') ?>">
    			<select name="city">
    			<?php foreach ($cities as $city) { ?>
    				<option value="<?= Functions::encryptToSafeBase64($city) ?>" <?php if(trim($city) == trim($selected_city)) echo 'selected="selected"'?> ><?= $city ?></option>
    			<?php } ?>
    			</select>
    			<input type="submit" value="<?= I18N::translate('Show') ?>" />
    		</form>
    		
    		<?php if($this->data->get('has_list', false)) { ?>
    		<div class="loading-image">&nbsp;</div>
    		<div class="certificate-list">
    			<table id="<?= $this->data->get('table_id') ?>">
    				<thead>
    					<tr>
    						<th><?= I18N::translate('Date') ?></th>
    						<th><?= I18N::translate('Type') ?></th>
    						<th><?= I18N::translate('Certificate') ?></th>
    					</tr>
    				</thead>
    				<tbody>
    				<?php foreach ($this->data->get('certificate_list') as $certificate) { 
    				    /** @var \MyArtJaub\Webtrees\Module\Certificates\Model\Certificate $certificate */
    				    ?>
    					<tr>
    						<!-- Certificate date -->
    						<?php if($date = $certificate->getCertificateDate()) { ?>
    						<td data-sort="<?= $date->julianDay() ?>"><?= $date->display() ?></td>
    						<?php } else { ?>
    						<td data-sort="0">&nbsp;</td>
    						<?php  } ?>
    						<!--  Certificate Type -->
    						<td><?= Filter::escapeHtml($certificate->getCertificateType() ?: '') ?></td>
    						<!--  Certificate Name -->
    						<?php 
    						$name = $certificate->getCertificateDetails() ?: '';
    						$sortname = "";
    						$ct_names=preg_match("/([A-Z]{2,})/", $name, $match);
    						if($ct_names > 0) $sortname = $match[1].'_';
    						$sortname .= $name;
    						?>
    						<td data-sort="<?= Filter::escapeHtml($sortname) ?>">
    							<a href="<?= $certificate->getHtmlUrl() ?>"><?= Filter::escapeHtml($name) ?></a>
    						</td>
    					</tr>
    				<?php } ?>
    				</tbody>
    			</table>
    		</div>    		
    		<?php } ?>
    	</div>
    	
    	<?php 
    }
    
}
 
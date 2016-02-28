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
 * View for SosaConfig@index
 */
class SosaComputeResultView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {        
        
        if($this->data->get('is_success', false)) {
        ?>        
        	<i class="icon-maj-success" title="<?php echo I18N::translate('Success'); ?>"></i>&nbsp;
        	<?php echo I18N::translate('Success'); ?>
        <?php } else { ?>
			<i class="icon-maj-error" title="<?php echo I18N::translate('Error'); ?>"></i>&nbsp;
			<?php echo I18N::translate('Error'); ?>
			<?php if($error = $this->data->get('error')) { echo '&nbsp;-&nbsp;' . $error; }
        }
    }
    
}
 
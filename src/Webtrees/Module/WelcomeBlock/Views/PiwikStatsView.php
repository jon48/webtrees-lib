<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage WelcomeBlock
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\WelcomeBlock\Views;

use \MyArtJaub\Webtrees\Mvc\View\AbstractView;
use Fisharebest\Webtrees\I18N;

/**
 * View for Piwik@index
 */
class PiwikStatsView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {    
        if($this->data->get('has_stats', false)) {
            $html = I18N::translate('%1$s visits since the beginning of %2$s<br>(%3$s today)' ,
                '<span class="odometer">' . I18N::number($this->data->get('visits_year')) . '</span>',
                date('Y'),
                '<span class="odometer">' . I18N::number($this->data->get('visits_today')) . '</span>'
                );
        }
        else {
            $html = I18N::translate('No statistics could be retrieved from Piwik.');
        }
        
        return $html;
    }
    

}
 
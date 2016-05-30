<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\GeoDispersion\Views;

use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * Abstract View for GeoAnalysis@dataTabs, used for general information
 * 
 * @abstract
 */
abstract class AbstractGeoAnalysisTabGeneralView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        $nb_found = $this->data->get('stats_gen_nb_found');
        $nb_other = $this->data->get('stats_gen_nb_other');
        $nb_unknown = $this->data->get('stats_gen_nb_unknown');
        $perc_known = Functions::safeDivision($nb_found - $nb_other, $nb_found + $nb_unknown);

        $html = '<div id="geodispersion_summary">
        	<div class="maj-table center">
        		<div class="maj-row">
        			<div class="label">' . I18N::translate('Places found'). '</div>
        			<div class="value">' . I18N::translate('%1$d (%2$s)',$nb_found - $nb_other, I18N::percentage($perc_known)). '</div>
        		</div>';
        if($nb_other > 0){
            $perc_other = Functions::safeDivision($nb_other, $nb_found + $nb_unknown);
            $html .=
        		'<div class="maj-row">
        			<div class="label">' . I18N::translate('Other places'). '</div>
        			<div class="value">' . I18N::translate('%1$d (%2$s)',$nb_other, I18N::percentage($perc_other)). '</div>
        		</div>';
        }
        $html .= '<div class="maj-row">
        			<div class="label">' . I18N::translate('Places not found'). '</div>
        			<div class="value">' . I18N::translate('%1$d (%2$s)',$nb_unknown, I18N::percentage(1 - $perc_known)). '</div>
        		</div>
        	</div>
        </div>
        <br/>
		<div id="geodispersion_data">
		' . $this->htmlAnalysisData(). '
		</div>';
        
        return $html;
    }
    
	/**
	 * Returns HTML code to display the analysis data, under the appropriate format.
	 *
	 * @return string HTML Code for analysis display
	 * @abstract
	 */
    protected abstract function htmlAnalysisData();        
    
}
 
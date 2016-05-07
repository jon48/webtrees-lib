<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\GeoDispersion\Views;

use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Functions\Functions;

/**
 * View for GeoAnalysis@dataTabs, used for general information with table
 */
class GeoAnalysisTabGeneralTableView extends AbstractGeoAnalysisTabGeneralView {
        
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisTabGeneralView::htmlAnalysisData()
	 */
    protected function htmlAnalysisData() {
        $results = $this->data->get('results');
        $analysis_level = $this->data->get('analysis_level');
        
        $nb_found = $this->data->get('stats_gen_nb_found');
        $nb_other = $this->data->get('stats_gen_nb_other');
        
        $i=1;
        $previous_nb=0;        
        
        $html='<div class="maj-table center">';        
        foreach($results as $place => $nb){
            $perc = Functions::safeDivision($nb, $nb_found - $nb_other);
            if($nb!=$previous_nb){
                $j= I18N::number($i);
            }
            else{
                $j='&nbsp;';
            }
            	
            $levels = array_map('trim',explode(',', $place));
            $placename = $levels[$analysis_level-1];
            if($placename == '' && $analysis_level > 1) $placename = I18N::translate('Unknown (%s)', $levels[$analysis_level-2]);
            $html.=
            '<div class="maj-row">
                <div class="label"><strong>'.$j.'</strong></div>
                <div class="label">'.$placename.'</div>
                <div class="value">'.I18N::translate('%d',$nb).'</div>
                <div class="value">'.I18N::percentage($perc,1).'</div>
             </div>';
            $i++;
            $previous_nb=$nb;
        }
        
        $html.='</div>';
        
        return $html;
    }
    
}
 
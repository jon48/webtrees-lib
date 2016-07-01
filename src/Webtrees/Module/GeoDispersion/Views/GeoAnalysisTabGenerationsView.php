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
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Mvc\View\AbstractView;

/**
 * View for GeoAnalysis@dataTabs, used for generations information
 */
class GeoAnalysisTabGenerationsView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {       

        $max_details_gen = $this->data->get('max_details_gen');
        $use_flags = $this->data->get('use_flags');
        $analysis_level = $this->data->get('analysis_level');
        $results_by_gen = $this->data->get('results_by_generations');
        $display_all_places = $this->data->get('display_all_places', true);
        
        $html = 
        '<div id="geodispersion_gen">
        	<table id="geodispersion_gentable" class="center">';
            
        foreach($results_by_gen as $gen => $genData){
            $html .= 
            '<tr>
                <td class="descriptionbox">' .
                    I18N::translate("Generation %s", I18N::number($gen)).
                    ($display_all_places ? '<br />' : ' ').
                    I18N::translate('(%s)', I18N::percentage(Functions::safeDivision($genData['sum'] + $genData['other'], $genData['sum'] + $genData['other'] + $genData['unknown']),1)) . 
                '</td>
                 <td class="optionbox left">'.
                    ($display_all_places ? 
                        $this->htmlGenerationAllPlacesRow($genData, $analysis_level) :
                        $this->htmlGenerationTopPlacesRow($genData, $analysis_level)
                     ) .
                '</ditdv>
            </tr>';
        }       
        
        $html.= 
            '</table>
            <div class="left">
                <strong>' . I18N::translate('Interpretation help:') . '</strong>
                <br />'.
                I18N::translate('<strong>Generation X (yy %%)</strong>: The percentage indicates the number of found places compared to the total number of ancestors in this generation.') . 
                '<br />';
        if(!is_null($max_details_gen) && $max_details_gen == 0){
            $html .= I18N::translate('<strong><em>Place</em> or <em>Flag</em> aa (bb %%)</strong>: The first number indicates the total number of ancestors born in this place, the percentage relates this count to the total number of found places. No percentage means it is less than 10%%.').'<br />';
            $html .= I18N::translate('If any, the darker area indicates the number of unknown places within the generation or places outside the analysed area, and its percentage compared to the number of ancestors. No percentage means it is less than 10%%.');
        }
        else{
            $html .= I18N::translate('<strong><em>Place</em> [aa - bb %%]</strong>: The first number indicates the total number of ancestors born in this place, the percentage compares this count to the total number of found places.').'<br />';
            $html .= I18N::translate('Only the %d more frequent places for each generation are displayed.', $max_details_gen);
        }
        $html.= 
            '</div>
        </div>';
        
        return $html;
    }
    
    
    /**
     * Return the HTML code to display a row with all places found in a generation.
     *
     * @param array $data Data array
     * @param int $analysis_level Level of subdivision of analysis
     * @return string HTML code for all places row
     */
    protected function htmlGenerationAllPlacesRow($data, $analysis_level) {
        $html =
        '<table class="geodispersion_bigrow">
            <tr>';
        
        $sum_gen = $data['sum'];
        $unknownother = $data['unknown'] + $data['other'];
        foreach($data['places'] as $placename=> $dataplace){
            $levels = array_map('trim',explode(',', $placename));
            $content = '';
            if(isset($dataplace['flag'])){
                $content .= '<td class="geodispersion_flag">'. FunctionsPrint::htmlPlaceIcon($dataplace['place'], $dataplace['flag']) .'</td><td>';
            }
            else{
                $content .= '<td><span title="'.implode(I18N::$list_separator, array_reverse($levels)).'">'.$levels[$analysis_level-1].'</span><br/>';
            }
            $count = $dataplace['count'];
            $content .= I18N::number($count);
            $perc = Functions::safeDivision($count, $sum_gen + $unknownother);
            $perc2= Functions::safeDivision($count, $sum_gen);
            if($perc2>=0.1) 
                $content.= '<br/><span class="small">('.I18N::percentage($perc2, 1).')</span>';
            $content .= '</td>';
               
            $html .= '
                <td class="geodispersion_rowitem" width="'.max(round(100*$perc, 0),1).'%">
                    <table>
                        <tr>
                            <td>
                                <table>
                                    <tr>'.$content.'</tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>';
        }
        
        if($unknownother>0){
            $perc= Functions::safeDivision($unknownother, $sum_gen + $unknownother);
            $html .='<td class="geodispersion_unknownitem left" >'.I18N::number($unknownother);
            if($perc>=0.1) $html.= '<br/><span class="small">('.I18N::percentage($perc, 1).')</span>';
            $html .='</td>';
        }
        
        $html .= 
            '</tr>
        </table>';
        return $html;
    }
    
	/**
	 * Returns the HTML code fo display a row of the Top Places found for a generation.
	 *
	 * @param array $data Data array
     * @param int $analysis_level Level of subdivision of analysis
	 * @return string HTML code for Top Places row
	 */
    protected function htmlGenerationTopPlacesRow($data, $analysis_level) {
        $tmp_places = array();
        $sum_gen = $data['sum'];
        $other = $data['other'];
        
        foreach($data['places'] as $placename => $count) {
            if($placename != 'other'){
                $levels = array_map('trim',explode(',', $placename));
                $placename = '<span title="'.implode(I18N::$list_separator, array_reverse($levels)).'">'.$levels[$analysis_level-1].'</span>';
            }
            else{
                $placename = I18N::translate('Other places');
            }
            $tmp_places[] = I18N::translate('<strong>%s</strong> [%d - %s]', $placename, $count, I18N::percentage(Functions::safeDivision($count, $sum_gen + $other), 1));         	
        }
        
        return implode(I18N::$list_separator, $tmp_places);
    }
    
}
 
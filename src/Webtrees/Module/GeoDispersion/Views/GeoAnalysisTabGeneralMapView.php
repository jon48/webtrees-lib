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

use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\OutlineMap;

/**
 * View for GeoAnalysis@dataTabs, used for general information with table
 */
class GeoAnalysisTabGeneralMapView extends AbstractGeoAnalysisTabGeneralView {
        
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisTabGeneralView::htmlAnalysisData()
	 */
    protected function htmlAnalysisData() {
        
        /** @var OutlineMap $map */
        $map = $this->data->get('map');
        $canvas = $map->getCanvas();
        $subdvisions_results = $this->data->get('results_by_subdivisions');
        
        $nb_found = $this->data->get('stats_gen_nb_found');
        $nb_other = $this->data->get('stats_gen_nb_other');
        
        $html = '<script>
			var tip = null;
			var tipText = "";
			var over = false;
			var isin = false;

			function addTip(node, txt){
			    jQuery(node).bind({
			    	mouseover : function(){
			    		oldisin = isin;
			    		isin = true;
			    		if(oldisin != isin){
			       			tipText = txt;
			       			tip.stop(true, true).fadeIn();
			       			over = true;
			       		}
			    	},
			    	mouseout : function(){
			    		oldisin = isin;
			    		isin = false;
			    		if(oldisin != isin){
			       			tip.stop(true, true).fadeOut("fast");
			       			over = false;
			       		}
			    	}

			    });
			}
			jQuery(document).ready(function() {
				tip = $("#geodispersion_tip").hide();

				var positionTab = jQuery("#geodispersion-tabs").offset();

				jQuery("#geodispersion_map").mousemove(function(e){
				    if (over){
					  tip.css("left", e.pageX + 20 - positionTab.left).css("top", e.pageY + 20 - positionTab.top);
				      tip.html(tipText);
				    }
				});

				var paper = new Raphael(document.getElementById("geodispersion_map"), '. $canvas->width .', '. $canvas->height .');
				var background = paper.rect(0, 0, ' . $canvas->width . ', '. $canvas->height . ');
				background.attr({"fill" : "'. $canvas->background_color .'", "stroke" : "'. $canvas->background_stroke .'", "stroke-width": 1, "stroke-linejoin": "round" });
				var attr = { fill: "'. $canvas->default_color .'", stroke: "'. $canvas->default_stroke .'", "stroke-width": 1, "stroke-linejoin": "round" };
				var map = {};
		';
        
        foreach($subdvisions_results as $name => $location){
            $html.= 'map.area'.$location['id'].' = paper.path("'.$location['coord'].'").attr(attr);';
            if(isset($location['transparency'])) {
                $textToolTip = '<strong>'.$location['displayname'].'</strong><br/>';
                if($this->data->get('use_flags') && $location['flag'] != '') $textToolTip .= '<span class="geodispersion_flag">'.FunctionsPrint::htmlPlaceIcon($location['place'], $location['flag']).'</span><br/>';
                $textToolTip .= I18N::translate('%d individuals', $location['count']).'<br/>'.I18N::percentage(Functions::safeDivision($location['count'], $nb_found - $nb_other), 1);
                $html.= 'addTip(map.area'.$location['id'].'.node, "'.Filter::escapeJs($textToolTip).'");';
                $html.= 'map.area'.$location['id'].'.attr({"fill" : "'. $canvas->max_color .'", "fill-opacity" : '.$location['transparency'].' });';
                $html.= 'map.area'.$location['id'].'.mouseover(function () {'.
                    'map.area'.$location['id'].'.stop().animate({"fill" : "'. $canvas->hover_color .'", "fill-opacity" : 1}, 100, "linear");'.
                    '});'.
                    'map.area'.$location['id'].'.mouseout(function () {'.
                    'map.area'.$location['id'].'.stop().animate({"fill" : "'.$canvas->max_color.'", "fill-opacity" : '.$location['transparency'].'}, 100, "linear");'.
                    '});';
            }
        }
        $html .= '});
            </script>
            
            <div id="geodispersion_map"></div>
    	   <div id="geodispersion_tip"></div>';
        
        return $html;
    }
    
} 
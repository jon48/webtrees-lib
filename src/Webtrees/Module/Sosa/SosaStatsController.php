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
namespace MyArtJaub\Webtrees\Module\Sosa;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\AbstractModule;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\Globals;
use MyArtJaub\Webtrees\Module\Sosa\Model\SosaProvider;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;

/**
 * Controller for SosaStats
 */
class SosaStatsController extends MvcController
{
    /**
     * Sosa Provider for the controller
     * @var SosaProvider $sosa_provider
     */
    protected $sosa_provider;
    
    /**
     * Constructor for SosaStatsController
     * @param AbstractModule $module
     */
    public function __construct(AbstractModule $module) {
        parent::__construct($module);
        
        $this->sosa_provider = new SosaProvider(Globals::getTree(), Auth::user());
    }
    
    /**
     * Pages
     */
    
    /**
     * SosaStats@index
     */
    public function index() {
        $wt_tree = Globals::getTree();
        $controller = new PageController();
        $controller
            ->setPageTitle(I18N::translate('Sosa Statistics'))
            ->addInlineJavascript('$(".help_tooltip").tooltip();')
        ;

        $view_bag = new ViewBag();
        $view_bag->set('title', $controller->getPageTitle());
        $view_bag->set('is_setup', false);
        
        if($this->sosa_provider->isSetup()) {
            $view_bag->set('is_setup', true);
            
            $view_bag->set('root_indi', $this->sosa_provider->getRootIndi());
            
            $sosaCount = $this->sosa_provider->getSosaCount();
            $diffSosaCount = $this->sosa_provider->getDifferentSosaCount();
            
            $general_stats = array(
                'sosa_count' => $sosaCount,
                'distinct_count' => $diffSosaCount,
                'sosa_rate' => Functions::safeDivision($diffSosaCount, $this->sosa_provider->getTotalIndividuals()),
                'pedi_collapse' => 1 - Functions::safeDivision($diffSosaCount, $sosaCount),
                'mean_gen_time' => $this->sosa_provider->getMeanGenerationTime()
            );
            $view_bag->set('general_stats', $general_stats);
            
            $stats_gen = $this->sosa_provider->getStatisticsByGeneration();
            $view_bag->set('missinganc_url', 'module.php?mod='.$this->module->getName().'&mod_action=SosaList@missing&ged='.$wt_tree->getNameUrl().'&gen=');
            $view_bag->set('sosaanc_url', 'module.php?mod='.$this->module->getName().'&mod_action=SosaList&ged='.$wt_tree->getNameUrl().'&gen=');
            
            $gen_theoretical=1;
            $total_theoretical=0;
            $prev_diff=0;
            $prev_known=0.5;
            $gen_equiv=0;            
            $generation_stats = array();
            
            foreach($stats_gen as $gen => $tab){
                $genY1= I18N::translate('-');
                $genY2= I18N::translate('-');
                if($tab['firstBirth']>0) $genY1=$tab['firstEstimatedBirth'];
                if($tab['lastBirth']>0) $genY2=$tab['lastEstimatedBirth'];
                $total_theoretical += $gen_theoretical;
                $perc_sosa_count_theor = Functions::safeDivision($tab['sosaCount'], $gen_theoretical);
                $gen_equiv += $perc_sosa_count_theor;
                $missing=2*$prev_known - $tab['sosaCount'];
                $gen_diff=$tab['diffSosaTotalCount']-$prev_diff;
                
                $generation_stats[$gen] = array(
                    'gen_min_birth' => $genY1,
                    'gen_max_birth' => $genY2,
                    'theoretical' => $gen_theoretical,
                    'known' => $tab['sosaCount'],
                    'perc_known' => $perc_sosa_count_theor,
                    'missing' => $missing,
                    'perc_missing' => 1-Functions::safeDivision($tab['sosaCount'], 2*$prev_known),
                    'total_known' => $tab['sosaTotalCount'],
                    'perc_total_known' => Functions::safeDivision($tab['sosaTotalCount'], $total_theoretical),
                    'different' => $gen_diff,
                    'perc_different' => Functions::safeDivision($gen_diff, $tab['sosaCount']),
                    'total_different' => $tab['diffSosaTotalCount'],
                    'pedi_collapse' => 1 - Functions::safeDivision($tab['diffSosaTotalCount'], $tab['sosaTotalCount'])
                );
                
                $gen_theoretical = $gen_theoretical * 2;
                $prev_known=$tab['sosaCount'];
                $prev_diff=$tab['diffSosaTotalCount'];
            }
            
            $view_bag->set('generation_stats', $generation_stats);
            $view_bag->set('equivalent_gen', $gen_equiv);
            
            $top10multiancestors = $this->sosa_provider->getTopMultiSosaAncestorsNoTies(10);
            $top10ancestors = array();
            if($top10multiancestors !== null && count($top10multiancestors)) {
                foreach($top10multiancestors as $pid => $count) {
                    $indi = Individual::getInstance($pid, $wt_tree);
                    if($indi !== null && $indi->canShowName()) {
                        array_key_exists($count, $top10ancestors) ?
                            $top10ancestors[$count][] = $indi:
                            $top10ancestors[$count] = array($count => $indi);
                    }
                }
            }
            $view_bag->set('top10multiancestors', $top10ancestors);
            
            $view_bag->set('chart_img_g2', $this->htmlAncestorDispersionG2());
            $view_bag->set('chart_img_g3', $this->htmlAncestorDispersionG3());
            
        }
        
        ViewFactory::make('SosaStats', $this, $controller, $view_bag)->render();   
    }
    
    /**
     * Returns HTML code for a graph showing the dispersion of ancestors across father & mother
     * @return string HTML code
     */
     private function htmlAncestorDispersionG2()
    {
        $ancestorsDispGen2 = $this->sosa_provider->getAncestorDispersionForGen(2);
        if(count($ancestorsDispGen2) == 0) return;
        
        $size = '600x300';
        
        $total = array_sum($ancestorsDispGen2);
        $father_count = array_key_exists(1, $ancestorsDispGen2) ? $ancestorsDispGen2[1] : 0;
        $father = array (
            'color' => '84beff', 
            'count' => $father_count, 
            'perc' => Functions::safeDivision($father_count, $total), 
            'name' => \Fisharebest\Webtrees\Functions\Functions::getRelationshipNameFromPath('fat')            
        );
        $mother_count = array_key_exists(2, $ancestorsDispGen2) ? $ancestorsDispGen2[2] : 0;
        $mother = array (
            'color' => 'ffd1dc', 
            'count' => $mother_count, 
            'perc' => Functions::safeDivision($mother_count, $total),
            'name' => \Fisharebest\Webtrees\Functions\Functions::getRelationshipNameFromPath('mot')
        );
        $shared_count = array_key_exists(-1, $ancestorsDispGen2) ? $ancestorsDispGen2[-1] : 0;
        $shared = array (
            'color' => '777777', 
            'count' => $shared_count, 
            'perc' => Functions::safeDivision($shared_count, $total),
            'name' => I18N::translate('Shared')
        );
        
        $chd = $this->arrayToExtendedEncoding(array(4095 * $father['perc'], 4095 * $shared['perc'], 4095 * $mother['perc']));
        $chart_title = I18N::translate('Known Sosa ancestors\' dispersion');
        $chl = 
             $father['name'] . ' - ' . I18N::percentage($father['perc'], 1) . '|' .
             $shared['name'] . ' - ' . I18N::percentage($shared['perc'], 1) . '|' .
             $mother['name'] . ' - ' . I18N::percentage($mother['perc'], 1);
        return "<img src=\"https://chart.googleapis.com/chart?cht=p&chp=1.5708&amp;chd=e:{$chd}&amp;chs={$size}&amp;chco={$father['color']},{$shared['color']},{$mother['color']}&amp;chf=bg,s,ffffff00&amp;chl={$chl}\" alt=\"" . $chart_title . "\" title=\"" . $chart_title . "\" />";
    }
    
    /**
     * Returns HTML code for a graph showing the dispersion of ancestors across grand-parents
     * @return string HTML code
     */
    private function htmlAncestorDispersionG3()
    {
        $ancestorsDispGen2 = $this->sosa_provider->getAncestorDispersionForGen(3);
        
        $size = '700x300';
        
        $color_motmot = 'ffd1dc';
        $color_motfat = 'b998a0';
        $color_fatfat = '577292';
        $color_fatmot = '84beff';
        $color_shared = '777777';
    
        $total_fatfat = array_key_exists(1, $ancestorsDispGen2) ? $ancestorsDispGen2[1] : 0;
        $total_fatmot = array_key_exists(2, $ancestorsDispGen2) ? $ancestorsDispGen2[2] : 0;
        $total_motfat = array_key_exists(4, $ancestorsDispGen2) ? $ancestorsDispGen2[4] : 0;
        $total_motmot = array_key_exists(8, $ancestorsDispGen2) ? $ancestorsDispGen2[8] : 0;
        $total_sha = array_key_exists(-1, $ancestorsDispGen2) ? $ancestorsDispGen2[-1] : 0;
        $total = $total_fatfat + $total_fatmot + $total_motfat+ $total_motmot + $total_sha;
    
        $chd = $this->arrayToExtendedEncoding(array(
	    4095 * Functions::safeDivision($total_fatfat, $total), 
            4095 * Functions::safeDivision($total_fatmot, $total),
            4095 * Functions::safeDivision($total_sha, $total), 
            4095 * Functions::safeDivision($total_motfat, $total),
            4095 * Functions::safeDivision($total_motmot, $total)          
        ));
        $chart_title = I18N::translate('Known Sosa ancestors\' dispersion - G3');
        $chl =
            \Fisharebest\Webtrees\Functions\Functions::getRelationshipNameFromPath('fatfat') . ' - ' . I18N::percentage(Functions::safeDivision($total_fatfat, $total), 1) . '|' .
            \Fisharebest\Webtrees\Functions\Functions::getRelationshipNameFromPath('fatmot') . ' - ' . I18N::percentage(Functions::safeDivision($total_fatmot, $total), 1) . '|' .
            I18N::translate('Shared') . ' - ' . I18N::percentage(Functions::safeDivision($total_sha, $total), 1) . '|' .
            \Fisharebest\Webtrees\Functions\Functions::getRelationshipNameFromPath('motfat') . ' - ' . I18N::percentage(Functions::safeDivision($total_motfat, $total), 1) . '|' .
            \Fisharebest\Webtrees\Functions\Functions::getRelationshipNameFromPath('motmot') . ' - ' . I18N::percentage(Functions::safeDivision($total_motmot, $total), 1);
         return "<img src=\"https://chart.googleapis.com/chart?cht=p&chp=1.5708&amp;chd=e:{$chd}&amp;chs={$size}&amp;chco={$color_fatfat},{$color_fatmot},{$color_shared},{$color_motfat},{$color_motmot}&amp;chf=bg,s,ffffff00&amp;chl={$chl}\" alt=\"" . $chart_title . "\" title=\"" . $chart_title . "\" />";
    }

    /**
     * Convert an array to Google Chart encoding
     * @param arrat $a Array to encode
     * @return string
     */
    private function arrayToExtendedEncoding($a) {
        $xencoding = WT_GOOGLE_CHART_ENCODING;
    
        $encoding = '';
        foreach ($a as $value) {
            if ($value < 0) {
                $value = 0;
            }
            $first  = (int) ($value / 64);
            $second = $value % 64;
            $encoding .= $xencoding[(int) $first] . $xencoding[(int) $second];
        }
    
        return $encoding;
    }
    
    
}
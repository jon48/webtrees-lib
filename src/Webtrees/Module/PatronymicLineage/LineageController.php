<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\PatronymicLineage;

use \Fisharebest\Webtrees as fw;
use \MyArtJaub\Webtrees as mw;
use \Fisharebest\Webtrees\I18N;
use \Fisharebest\Webtrees\Filter;
use \Fisharebest\Webtrees\Query\QueryName;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Module\PatronymicLineage\Model\LineageBuilder;

/**
 * Controller for Lineage
 */
class LineageController extends fw\Controller\PageController
{   
    
    /**
     * Generate the patronymic lineage for this surname
     * @var string $surname Reference surname
     */
    private $surname;   
    
    /**
     * Initial letter
     * @var string $alpha
     */
    private $alpha;
    
    /**
     * Show all names (values: yes|no)
     * @var bool $show
     */
    private $show_all;
    
    /**
     * Page to display (values: surn|lineage)
     * @var unknown $show
     */
    private $show;
    
    /**
     * Page title
     * @var string $legend
     */
    private $legend;
    
    /**
     * Constructor for LineageConstructor
     */
    public function __construct() {                
        parent::__construct();
        
        $this->surname     = Filter::get('surname');
        $this->alpha       = Filter::get('alpha'); // All surnames beginning with this letter where "@"=unknown and ","=none
        $this->show_all    = Filter::get('show_all', 'no|yes', 'no'); // All indis
        // Make sure selections are consistent.
        // i.e. can’t specify show_all and surname at the same time.
        if ($this->show_all === 'yes') {
            $this->alpha   = '';
            $this->surname = '';
            $this->legend  = I18N::translate('All');
            $this->show    = Filter::get('show', 'surn|lineage', 'surn');
        } elseif ($this->surname) {
            $this->alpha    = QueryName::initialLetter($this->surname); // so we can highlight the initial letter
            $this->show_all = 'no';
            if ($this->surname === '@N.N.') {
                $this->legend = I18N::translateContext('Unknown surname', '…');
            } else {
                $this->legend = Filter::escapeHtml($this->surname);
            }
            $this->show = 'lineage'; // SURN list makes no sense here
        } elseif ($this->alpha === '@') {
            $this->show_all = 'no';
            $this->legend   = I18N::translateContext('Unknown surname', '…');
            $this->show     = 'lineage'; // SURN list makes no sense here
        } elseif ($this->alpha === ',') {
            $this->show_all = 'no';
            $this->legend   = I18N::translate('None');
            $this->show     = 'lineage'; // SURN list makes no sense here
        } elseif ($this->alpha) {
            $this->show_all = 'no';
            $this->legend   = Filter::escapeHtml($this->alpha) . '…';
            $this->show     = Filter::get('show', 'surn|lineage', 'surn');
        } else {
            $this->show_all = 'no';
            $this->legend   = '…';
            $this->show     = 'none'; // Don't show lists until something is chosen
        }
        $this->legend = '<span dir="auto">' . $this->legend . '</span>';
        
        $this->setPageTitle(I18N::translate('Patronymic Lineages') . ' : ' . $this->legend);
          
    }
    
    /**
     * Indicates whether the list of surname should be displayed
     * @return bool
     */
    protected function isShowingSurnames() {
        return $this->show === 'surn';
    }
    
    /**
     * Indicates whether the lineages should be displayed
     * @return bool
     */
    protected function isShowingLineages() {
        return $this->show === 'lineage';
    } 
    
    /**
     * Get list of surnames, starting with the specified initial
     * @return array
     */
    protected function getSurnamesList() {
        global $WT_TREE;
        
        return QueryName::surnames($WT_TREE, $this->surname, $this->alpha, false, false);
    }
    
    /**
     * Get the lineages for the controller's specified surname
     */
    protected function getLineages() {
		global $WT_TREE;
		
		$builder = new LineageBuilder($this->surname, $WT_TREE);
		$lineages = $builder->buildLineages();
		
    	return $lineages;
    }    
    
    /**
     * Pages
     */
    
    /**
     * Lineage@index
     */
    public function index() {
        global $WT_TREE;
        
        $view_bag = new ViewBag();
        $view_bag->set('title', $this->getPageTitle());
        $view_bag->set('tree', $WT_TREE);
        $view_bag->set('alpha', $this->alpha);
        $view_bag->set('surname', $this->surname);
        $view_bag->set('legend', $this->legend);
        $view_bag->set('show_all', $this->show_all);
        if($this->isShowingSurnames()) {
            $view_bag->set('issurnames', true);
            $view_bag->set('surnameslist', $this->getSurnamesList());
        }
        if($this->isShowingLineages()) {
            $view_bag->set('islineages', true);
            $view_bag->set('lineages', $this->getLineages());

            if ($this->show_all==='no') {
            	$view_bag->set('table_title', I18N::translate('Individuals in %s lineages', $this->legend));
            }
            else {
            	$view_bag->set('table_title', I18N::translate('All lineages'));
            }
        }
        
        ViewFactory::make('Lineage', $this, $view_bag)->render();   
    }
    
    
    
}
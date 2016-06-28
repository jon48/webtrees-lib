<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\PatronymicLineage\Views;

use \MyArtJaub\Webtrees\Mvc\View\AbstractView;
use Fisharebest\Webtrees\Query\QueryName;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Auth;
use MyArtJaub\Webtrees\Constants;
use Fisharebest\Webtrees\Functions\FunctionsPrintLists;
use MyArtJaub\Webtrees\Module\PatronymicLineage\Model\LineageNode;
use MyArtJaub\Webtrees\Module\PatronymicLineage\Model\LineageRootNode;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Family;

/**
 * View for Lineage@index
 */
class LineageView extends AbstractView {
        
	/**
	 * {@inhericDoc}
	 * @see \MyArtJaub\Webtrees\Mvc\View\AbstractView::renderContent()
	 */
    protected function renderContent() {
        
        /** @var \Fisharebest\Webtrees\Tree $tree */
        $tree = $this->data->get('tree');
        
        echo '<h2 class="center">', $this->data->get('title') , '</h2>';
        echo '<p class="center alpha_index">', implode(' | ', $this->getInitialLettersList()), '</p>';
         
        if($this->data->get('issurnames', false)) {
            $surns = $this->data->get('surnameslist', array());
            $extra_params = array ('mod' => Constants::MODULE_MAJ_PATROLIN_NAME, 'mod_action' => 'Lineage');
            // Show the surname list
            switch ($tree->getPreference('SURNAME_LIST_STYLE')) {
                case 'style1':
					echo FunctionsPrintLists::surnameList($surns, 3, true, WT_SCRIPT_NAME, $tree, $extra_params);
					break;
                case 'style3':
                    echo FunctionsPrintLists::surnameTagCloud($surns, WT_SCRIPT_NAME, true, $tree, $extra_params);
                    break;
                case 'style2':
                default:
                    echo FunctionsPrintLists::surnameTable($surns, WT_SCRIPT_NAME, $tree, $extra_params);
                    break;
            }
        }
        else if ($this->data->get('islineages', false)) {
            //Link to indilist
            echo '<p class="center"><strong>'.
                '<a href="indilist.php?ged=' . $tree->getNameUrl() . '&surname=' . rawurlencode($this->data->get('surname')) .'">'. 
                I18N::translate('Go to the list of individuals with surname %s', $this->data->get('legend')).
                '</a></strong></p>';
            
            $lineages = $this->data->get('lineages', null);
            $nb_lineages = count($lineages);
            if(is_null($lineages) || $nb_lineages == 0) {
            	echo '<p class="center"><span class="warning">',
            		I18N::translate('No individuals with surname %s has been found. Please try another name.',
            			'<span dir="auto">' . $this->data->get('legend') . '</span>'),
            			'</span></p>';
            } else { 
            	
            	echo '<div id="patronymiclineages">'.
            		'<div class="list_label">',
            		$this->data->get('table_title'),
            		'</div>';
            	
            	echo '<div class="list_value_wrap">';            	
            	
            	foreach($lineages as $i => $lineage) {
            		$this->printRootLineage($lineage);
            		if($i < $nb_lineages - 1) echo '<hr />';
            	}
            	
            	echo '</div>';
            	
            	echo '<div class="list_label">',
            		I18N::translate('%s lineages found', $nb_lineages),
            		'</div>'.
            		'</div>';

            }
        }
    }
    
    /**
     * Get the list of initial letters
     * 
     * @return string[]
     */
    private function getInitialLettersList() { 
        $list = array();
        /** @var \Fisharebest\Webtrees\Tree $tree */
        $tree = $this->data->get('tree');
		$script_base_url = WT_SCRIPT_NAME . '?mod=' . \MyArtJaub\Webtrees\Constants::MODULE_MAJ_PATROLIN_NAME . '&mod_action=Lineage';
		
        foreach (QueryName::surnameAlpha($tree, false, false) as $letter => $count) {
            switch ($letter) {
                case '@':
                    $html = I18N::translateContext('Unknown surname', '…');
                    break;
                case ',':
                    $html = I18N::translate('None');
                    break;
                default:
                    $html = Filter::escapeHtml($letter);
                    break;
            }
            if ($count) {
                if ($letter == $this->data->get('alpha')) {
                    $list[] = '<a href="' . $script_base_url . '&alpha=' . rawurlencode($letter) . '&amp;ged=' . $tree->getNameUrl() . '" class="warning" title="' . I18N::number($count) . '">' . $html . '</a>';
                } else {
                    $list[] = '<a href="' . $script_base_url . '&alpha=' . rawurlencode($letter) . '&amp;ged=' . $tree->getNameUrl() . '" title="' . I18N::number($count) . '">' . $html . '</a>';
                }
            } else {
                $list[] = $html;
            }
        }
    
        // Search spiders don't get the "show all" option as the other links give them everything.
        if (!Auth::isSearchEngine()) {
            if ($this->data->get('show_all') === 'yes') {
                $list[] = '<span class="warning">' . I18N::translate('All') . '</span>';
            } else {
                $list[] = '<a href="' . $script_base_url . '&show_all=yes' . '&amp;ged=' . $tree->getNameUrl() . '">' . I18N::translate('All') . '</a>';
            }
        }
    
        return $list;
    }

    /**
     * Print a root lineage node
     * @param LineageRootNode $node
     */
    private function printRootLineage(LineageRootNode $node) {    	
    	print '<div class="patrolin_tree">';
    	if($node->getIndividual() === null) {
    		$fam_nodes = $node->getFamiliesNodes();
    		foreach($fam_nodes as $fam){
    			foreach($fam_nodes[$fam] as $child_node) {
    				if($child_node) {
    					$this->printLineage($child_node);
    				}
    			}
    		}
    	}
    	else {
    		$this->printLineage($node);
    	}
    	echo '</div>';
    	
    	$places = $node->getPlaces();
    	if($places && count($places)>0){
    		echo '<div class="patrolin_places">';
    		echo \MyArtJaub\Webtrees\Functions\FunctionsPrint::htmlPlacesCloud($places, false, $this->data->get('tree'));
    		echo '</div>';
    	}
    }
    
    /**
     * Print a lineage node, recursively.
     * @param LineageNode $node
     */
    private function printLineage(LineageNode $node) {
    	
    	echo '<ul>';
    	$fam_nodes = $node->getFamiliesNodes();
    	if(count($fam_nodes) > 0) {
    		$is_first_family = true;
    		foreach($fam_nodes as $fam) {
    			$node_indi = $node->getIndividual();
    			echo '<li>';
    			if($is_first_family){
    				echo FunctionsPrint::htmlIndividualForList($node_indi);
    			}
				else{
					echo FunctionsPrint::htmlIndividualForList($node_indi, false);
				}
				//Get individual's spouse
				$dfam = new Family($fam);
				$spouse=$dfam->getSpouseById($node_indi);
				//Print the spouse if relevant
				if($spouse){
					$marrdate = I18N::translate('yes');
					$marryear = '';
					echo '&nbsp;<a href="'.$fam->getHtmlUrl().'">';
					if ($fam->getMarriageYear()){
						$marrdate = strip_tags($fam->getMarriageDate()->Display());
						$marryear = $fam->getMarriageYear();
					}
					echo '<span class="details1" title="'.$marrdate.'"><i class="icon-rings"></i>'.$marryear.'</span></a>&nbsp;';
					echo FunctionsPrint::htmlIndividualForList($spouse);
				}
    			foreach($fam_nodes[$fam] as $child_node) {
    				if($child_node) {
    					$this->printLineage($child_node);
    				}
    				else {
    					echo '<ul><li><strong>&hellip;</strong></li></ul>';
    				}
    			}
    			$is_first_family = false;
    		}
    	}
    	else {
    		echo '<li>';
    		echo \MyArtJaub\Webtrees\Functions\FunctionsPrint::htmlIndividualForList($node->getIndividual());
    		if($node->hasFollowUpSurname()) {
    			$url_base = WT_SCRIPT_NAME . '?mod=' . \MyArtJaub\Webtrees\Constants::MODULE_MAJ_PATROLIN_NAME . '&mod_action=Lineage';
    			echo '&nbsp;'.
    				'<a href="' . $url_base . '&surname=' . rawurlencode($node->getFollowUpSurname()) . '&amp;ged=' . $this->data->get('tree')->getNameUrl() . '">'.
    				'('.I18N::translate('Go to %s lineages', $node->getFollowUpSurname()).')'.
    				'</a>';
    		}
    		echo '</li>';
    	}
    	echo '</ul>';
    	
    }

}
 
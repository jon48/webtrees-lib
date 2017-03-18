<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage IsSourced
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use Fisharebest\Webtrees\Controller\IndividualController;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\GedcomTag;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleSidebarInterface;
use MyArtJaub\Webtrees\Family;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtenderInterface;
use MyArtJaub\Webtrees\Hook\HookInterfaces\RecordNameTextExtenderInterface;
use MyArtJaub\Webtrees\Hook\HookSubscriberInterface;
use MyArtJaub\Webtrees\Individual;
/**
 * IsSourced Module
 */
class IsSourcedModule extends AbstractModule 
implements ModuleSidebarInterface, HookSubscriberInterface, IndividualHeaderExtenderInterface, RecordNameTextExtenderInterface
{
    /** @var string For custom modules - link for support, upgrades, etc. */
    const CUSTOM_WEBSITE = 'https://github.com/jon48/webtrees-lib';
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::getTitle()
     */
    public function getTitle() {
        return I18N::translate('Sourced events');
    }
    
   /**
    * {@inheritDoc}
    * @see \Fisharebest\Webtrees\Module\AbstractModule::getDescription()
    */
    public function getDescription() {
        return I18N::translate('Indicate if events related to an record are sourced.');
    }
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookSubscriberInterface::getSubscribedHooks()
     */
	public function getSubscribedHooks() {
		return array(
 			'hExtendIndiHeaderIcons' => 10,
			'hRecordNameAppend' => 50
		);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtenderInterface::hExtendIndiHeaderIcons()
	 */
	public function hExtendIndiHeaderIcons(IndividualController $ctrlIndi) {
	    if($ctrlIndi){
	        $dindi = new Individual($ctrlIndi->getSignificantIndividual());
	        if ($dindi->canDisplayIsSourced()) 
	            return FunctionsPrint::formatIsSourcedIcon('R', $dindi->isSourced(), 'INDI', 1, 'large');
	    }
	    return '';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtenderInterface::hExtendIndiHeaderLeft()
	 */
	public function hExtendIndiHeaderLeft(IndividualController $ctrlIndi) { }
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtenderInterface::hExtendIndiHeaderRight()
	 */
	public function hExtendIndiHeaderRight(IndividualController $ctrlIndi) { }
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\RecordNameTextExtenderInterface::hRecordNamePrepend()
	 */
	public function hRecordNamePrepend(GedcomRecord $grec, $size){ }
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\RecordNameTextExtenderInterface::hRecordNameAppend()
	 */
	public function hRecordNameAppend(GedcomRecord $grec, $size = 'small'){
	    $html = '';
	    if($grec instanceof \Fisharebest\Webtrees\Individual){
	        $dindi = new Individual($grec);
	        $html .= FunctionsPrint::formatIsSourcedIcon('R', $dindi->isSourced(), 'INDI', 1, $size);
	        $html .= FunctionsPrint::formatIsSourcedIcon('E', $dindi->isBirthSourced(), 'BIRT', 1, $size);
	        if($grec->isDead())
	            $html .= FunctionsPrint::formatIsSourcedIcon('E', $dindi->isDeathSourced(), 'DEAT', 1, $size);
	    }
	    return $html;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::defaultSidebarOrder()
	 */
	public function defaultSidebarOrder() {
	    return 15;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::hasSidebarContent()
	 */
	public function hasSidebarContent(){ 
	    return true;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::getSidebarAjaxContent()
	 */
	public function getSidebarAjaxContent() {
	    return '';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::getSidebarContent()
	 */
	public function getSidebarContent() {
	    global $controller;
	    
	    ob_start();
	    $root = $controller->getSignificantIndividual();
	    if ($root) {
	        $dindi = new Individual($root);
	        	
	        if (!$dindi->canDisplayIsSourced()) {
	            echo '<div class="error">', I18N::translate('This information is private and cannot be shown.'), '</div>';
	        } else {
	            echo '
	                <table class="issourcedtable">
	                   <tr>
	                       <td class="slabel"> ' . GedcomTag::getLabel('INDI') . '</td>
	                       <td class="svalue">' . FunctionsPrint::formatIsSourcedIcon('R', $dindi->isSourced(), 'INDI', 1).'</td>
	                   </tr>
	                   <tr>
	                       <td class="slabel">' . GedcomTag::getLabel('BIRT') . '</td>
	                       <td class="svalue">' . FunctionsPrint::formatIsSourcedIcon('E', $dindi->isBirthSourced(), 'BIRT', 1).'</td>
	                   </tr>';
	            
	            $fams = $root->getSpouseFamilies();
	            ($ct = count($fams)) > 1 ? $nb=1 : $nb=' ';	            
	            foreach($fams as $fam){
	                $dfam = new Family($fam);
	                echo '
	                    <tr>
	                       <td class="slabel right">
	                           <a href="' . $fam->getHtmlUrl() . '"> '. GedcomTag::getLabel('MARR');
	                if($ct > 1){
	                    echo ' ',$nb;
	                    $nb++;
	                }
	                echo '     </a>
	                       </td>
	                       <td class="svalue">' . FunctionsPrint::formatIsSourcedIcon('E', $dfam->isMarriageSourced(), 'MARR', 1).'</td>
	                   </tr>';
	            }
	            
	            if( $root->isDead() )
	                echo '
	                    <tr>
	                       <td class="slabel">' . GedcomTag::getLabel('DEAT') . '</td>
	                       <td class="svalue">' . FunctionsPrint::formatIsSourcedIcon('E', $dindi->isDeathSourced(), 'DEAT', 1).'</td>
	                   </tr>';
	            
	            echo '</table>';
	        }
	    }
	    return ob_get_clean();	    
	}
	
	
}
 
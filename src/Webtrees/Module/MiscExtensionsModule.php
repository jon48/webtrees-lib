<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage MiscExtensions
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\IndividualController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Theme;
use Fisharebest\Webtrees\User;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtender;
use MyArtJaub\Webtrees\Hook\HookInterfaces\PageFooterExtender;
use MyArtJaub\Webtrees\Hook\HookInterfaces\PageHeaderExtender;
use MyArtJaub\Webtrees\Hook\HookSubscriberInterface;
use MyArtJaub\Webtrees\Individual;
/**
 * MiscExtension Module
 */
class MiscExtensionsModule extends AbstractModule 
implements HookSubscriberInterface, IndividualHeaderExtender, PageHeaderExtender, PageFooterExtender, ModuleConfigInterface
{
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::getTitle()
     */
    public function getTitle() {
        return I18N::translate('Miscellaneous extensions');
    }
    
   /**
    * {@inheritDoc}
    * @see \Fisharebest\Webtrees\Module\AbstractModule::getDescription()
    */
    public function getDescription() {
        return I18N::translate('Miscellaneous extensions for <strong>webtrees</strong>.');
    }
    
    /**
     * {@inhericDoc}
     */
    public function modAction($mod_action) {
        \MyArtJaub\Webtrees\Mvc\Dispatcher::getInstance()->handle($this, $mod_action);
    }
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleConfigInterface::getConfigLink()
     */
    public function getConfigLink() {
        return 'module.php?mod=' . $this->getName() . '&amp;mod_action=AdminConfig';
    }
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookSubscriberInterface::getSubscribedHooks()
     */
	public function getSubscribedHooks() {
		return array(
		    'hExtendIndiHeaderLeft' => 20,
		    'hPrintHeader' => 20,
		    'hPrintFooter' => 20
		);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtender::hExtendIndiHeaderIcons()
	 */
	public function hExtendIndiHeaderIcons(IndividualController $ctrlIndi) { }
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtender::hExtendIndiHeaderLeft()
	 */
	public function hExtendIndiHeaderLeft(IndividualController $ctrlIndi) { 
	    $res = '';
	    $dindi = new Individual($ctrlIndi->getSignificantIndividual());
	    $titles = $dindi->getTitles();
	    if(count($titles)>0){
	        $res = '
	            <dl>
	               <dt class="label">'.I18N::translate('Titles').'</dt>';
            foreach($titles as $title=>$props){
                $res .= 
                    '<dd class="field">' . $title. ' ' .
                    FunctionsPrint::getListFromArray($props) .
                    '</dd>';
            }
            $res .=  '</dl>';
        }
	    return array( 'indi-header-titles' , $res);	    
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtender::hExtendIndiHeaderRight()
	 */
	public function hExtendIndiHeaderRight(IndividualController $ctrlIndi) { }
		
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\PageHeaderExtender::hPrintHeader()
	 */
	public function hPrintHeader() {	 
	    global $WT_TREE;
	    
	    $html = '';
	    if($this->getSetting('MAJ_ADD_HTML_HEADER', 0) == 1){
	        if(Auth::accessLevel($WT_TREE) >= $this->getSetting('MAJ_SHOW_HTML_HEADER', Auth::PRIV_HIDE)  && !Filter::getBool('noheader')){
	            $html .= $this->getSetting('MAJ_HTML_HEADER', '');
	        }
	    }	
	    return $html;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\PageFooterExtender::hPrintFooter()
	 */
	public function hPrintFooter() {
	    global $WT_TREE;
	     
	    $html = '';
	    if($this->getSetting('MAJ_DISPLAY_CNIL', 0) == 1){
	        $html .= '<br/>';
	        $html .= '<div class="center">';
	        $cnil_ref = $this->getSetting('MAJ_CNIL_REFERENCE', '');
	        if($cnil_ref != ''){
	            $html .= I18N::translate('This site has been notified to the French National Commission for Data protection (CNIL) and registered under number %s. ', $cnil_ref);
	        }
	        $html .= I18N::translate('In accordance with the French Data protection Act (<em>Loi Informatique et Libertés</em>) of January 6th, 1978, you have the right to access, modify, rectify and delete personal information that pertains to you. To exercice this right, please contact %s, and provide your name, address and a proof of your identity.', Theme::theme()->contactLink(User::find($WT_TREE->getPreference('WEBMASTER_USER_ID'))));
	        $html .= '</div>';
	    }
	    
	    if($this->getSetting('MAJ_ADD_HTML_FOOTER', 0) == 1){
	        if(Auth::accessLevel($WT_TREE) >= $this->getSetting('MAJ_SHOW_HTML_FOOTER', Auth::PRIV_HIDE)  && !Filter::getBool('nofooter')){
	            $html .= $this->getSetting('MAJ_HTML_FOOTER', '');
	        }
	    }
	    return $html;
	}
	
}
 
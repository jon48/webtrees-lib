<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use MyArtJaub\Webtrees\Hook\HookSubscriberInterface;
use MyArtJaub\Webtrees\Module\Certificates\Model\CertificateFileProvider;
use MyArtJaub\Webtrees\Module\Certificates\Model\CertificateProviderInterface;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Hook\HookInterfaces\FactSourceTextExtender;
use Fisharebest\Webtrees\Auth;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use Fisharebest\Webtrees\Source;
use MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManager;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Rhumsaa\Uuid\Uuid;

/**
 * Certificates Module.
 */
class CertificatesModule 
    extends AbstractModule 
    implements HookSubscriberInterface, ModuleConfigInterface, ModuleMenuItemInterface, FactSourceTextExtender, CustomSimpleTagManager
{
        
    /**
     * Provider for Certificates
     * @var CertificateProviderInterface $provider
     */
    protected $provider;
    
    /**
     * {@inhericDoc}
     */
    public function getTitle() {
        return /* I18N: Name of the “Certificates” module */ I18N::translate('Certificates');
    }
    
    /**
     * {@inhericDoc}
     */
    public function getDescription() {
        return /* I18N: Description of the “Certificates” module */ I18N::translate('Display and edition of certificates linked to sources.');
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
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookSubscriberInterface::getSubscribedHooks()
     */
    public function getSubscribedHooks() {
        return array(
            'hFactSourcePrepend' => 50,
            'hGetExpectedTags' => 50,
            'hHtmlSimpleTagDisplay#_ACT' => 50,
            'hHtmlSimpleTagEditor#_ACT'	=> 50,
            'hAddSimpleTag#SOUR'	=> 50,
            'hHasHelpTextTag#_ACT'	=> 50,
            'hGetHelpTextTag#_ACT'	=> 50
        );
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Module\ModuleMenuItemInterface::getMenu()
     */
    public function getMenu(Tree $tree, $reference = null) {
        $tree_url = $tree ? $tree->getNameUrl() : '';
        return new Menu($this->getTitle(), 'module.php?mod=' . $this->getName() . '&mod_action=Certificate@listAll&ged=' . $tree_url, 'menu-maj-list-certificate', array('rel' => 'nofollow'));
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\FactSourceTextExtender::hFactSourcePrepend()
     */
    public function hFactSourcePrepend($srec) {
        global $WT_TREE;
        
        $html='';
        $sid=null;
        
        if($this->getSetting('MAJ_SHOW_CERT', Auth::PRIV_HIDE) >= Auth::accessLevel($WT_TREE)){
            if (!$srec || strlen($srec) == 0) return $html;
            	
            $certificate = null;
            $subrecords = explode("\n", $srec);
            $levelSOUR = substr($subrecords[0], 0, 1);
            if (preg_match('~^'.$levelSOUR.' SOUR @('.WT_REGEX_XREF.')@$~', $subrecords[0], $match)) {
                $sid=$match[1];
            };
            for ($i=0; $i<count($subrecords); $i++) {
                $subrecords[$i] = trim($subrecords[$i]);
                $level = substr($subrecords[$i], 0, 1);
                $tag = substr($subrecords[$i], 2, 4);
                $text = substr($subrecords[$i], 7);
                if($tag == '_ACT') $certificate= new Certificate($text, $WT_TREE, $this->getProvider());
            }
            	
            if($certificate && $certificate->canShow())
                $html = $this->getDisplay_ACT($certificate, $sid);
                	
        }
        return $html;
    }
   
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\FactSourceTextExtender::hFactSourceAppend()
     */
    public function hFactSourceAppend($srec) { }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManager::hGetExpectedTags()
     */
    public function hGetExpectedTags() {
        return array('SOUR' => '_ACT');
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManager::hHtmlSimpleTagDisplay()
     */
    public function hHtmlSimpleTagDisplay($tag, $value, $context = null, $contextid = null) {
        $html = '';
        switch($tag){
            case '_ACT':
                if($context == 'SOUR') $html = $this->getDisplay_ACT($value, $contextid);
                break;
        }
        return $html;
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManager::hHtmlSimpleTagEditor()
     */
    public function hHtmlSimpleTagEditor($tag, $value = null, $element_id = '', $element_name = '', $context = null, $contextid = null) {
        global $controller, $WT_TREE;
        
        $html = '';
		
		switch($tag){
			case '_ACT':
				$element_id = Uuid::uuid4();
				$controller
					->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
					->addExternalJavascript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/js/autocomplete.js')
					->addExternalJavascript(WT_STATIC_URL.WT_MODULES_DIR.$this->getName().'/js/updatecertificatevalues.js');
				$certificate = null;
				if($value){
					$certificate = new Certificate($value, $WT_TREE, $this->getProvider());
				}
				$tabCities = $this->getProvider()->getCitiesList();
				$html .= '<select id="certifCity'.$element_id.'" class="_CITY">';
				foreach ($tabCities as $cities){
					$selectedCity='';
					if($certificate && $cities== $certificate->getCity()) $selectedCity='selected="true"';
					$html .= '<option value="'.$cities.'" '.$selectedCity.' />'.$cities.'</option>';
				}
				$html .= '</select>';
				$html .= '<input id="certifFile'.$element_id.'" autocomplete="off" class="_ACT" value="'.
					($certificate ? basename($certificate->getFilename()) : '').
					'" size="35" />';
				$html .= '<input type="hidden" id="'.$element_id.'" name = "'.$element_name.'" value="'.$value.'" size="35"/>';
		}
		
		return $html;
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManager::hAddSimpleTag()
     */
    public function hAddSimpleTag($context, $level) {
        switch($context){
            case 'SOUR':
                FunctionsEdit::addSimpleTag($level.' _ACT');
                break;
        }
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManager::hHasHelpTextTag()
     */
    public function hHasHelpTextTag($tag) {
        switch($tag){
			case '_ACT':
				return true;
				break;
		}
		return false;
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManager::hGetHelpTextTag()
     */
    public function hGetHelpTextTag($tag) {
        switch($tag){
            case '_ACT':
                return array(
                I18N::translate('Certificate'),
                '<p>'.I18N::translate('Path to a certificate linked to a source reference.').'</p>');
            default:
                return null;
        }
    }

    /**
     * Returns the default Certificate File Provider, as configured in the module
     *
     * @return \MyArtJaub\Webtrees\Module\Certificates\Model\CertificateProviderInterface
     */
    public function getProvider() {
        global $WT_TREE;
    
        if(!$this->provider) {
            $root_path = $this->getSetting('MAJ_CERT_ROOTDIR', 'certificates/');
            $this->provider = new CertificateFileProvider($root_path, $WT_TREE);
        }
        return $this->provider;
    }
    
    
    /**
     * Return the HTML code for custom simple tag _ACT
     *
     * @param Certificate $certificatePath Certificate (as per the GEDCOM)
     * @param string|null $sid Linked Source ID, if it exists
     */
    protected function getDisplay_ACT(Certificate $certificate, $sid = null){    
        $html = '';
        if($certificate){
            $certificate->setSource($sid);
            $html = $certificate->displayImage('icon');
        }
        return $html;
    }

}
 
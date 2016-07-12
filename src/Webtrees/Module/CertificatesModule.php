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

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Functions\FunctionsEdit;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManagerInterface;
use MyArtJaub\Webtrees\Hook\HookInterfaces\FactSourceTextExtenderInterface;
use MyArtJaub\Webtrees\Hook\HookSubscriberInterface;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Model\CertificateFileProvider;
use MyArtJaub\Webtrees\Module\Certificates\Model\CertificateProviderInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Certificates Module.
 */
class CertificatesModule 
    extends AbstractModule 
    implements HookSubscriberInterface, ModuleConfigInterface, ModuleMenuItemInterface, FactSourceTextExtenderInterface, CustomSimpleTagManagerInterface, DependentInterface
{
    /** @var string For custom modules - link for support, upgrades, etc. */
    const CUSTOM_WEBSITE = 'https://github.com/jon48/webtrees-lib';
        
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
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\DependentInterface::validatePrerequisites()
     */
    public function validatePrerequisites() {
        return Functions::isEncryptionCompatible();    
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
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\FactSourceTextExtenderInterface::hFactSourcePrepend()
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
            $match = null;
            if (preg_match('~^'.$levelSOUR.' SOUR @('.WT_REGEX_XREF.')@$~', $subrecords[0], $match)) {
                $sid=$match[1];
            };
            $nb_subrecords = count($subrecords);
            for ($i=0; $i < $nb_subrecords; $i++) {
                $subrecords[$i] = trim($subrecords[$i]);
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
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\FactSourceTextExtenderInterface::hFactSourceAppend()
     */
    public function hFactSourceAppend($srec) { }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManagerInterface::hGetExpectedTags()
     */
    public function hGetExpectedTags() {
        return array('SOUR' => '_ACT');
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManagerInterface::hHtmlSimpleTagDisplay()
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
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManagerInterface::hHtmlSimpleTagEditor()
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
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManagerInterface::hAddSimpleTag()
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
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManagerInterface::hHasHelpTextTag()
     */
    public function hHasHelpTextTag($tag) {
        switch($tag){
			case '_ACT':
				return true;
		}
		return false;
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\CustomSimpleTagManagerInterface::hGetHelpTextTag()
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
 
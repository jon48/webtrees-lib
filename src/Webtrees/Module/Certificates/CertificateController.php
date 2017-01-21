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
namespace MyArtJaub\Webtrees\Module\Certificates;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Module\AbstractModule;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Controller\JsonController;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\ImageBuilder;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Model\CertificateProviderInterface;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use Rhumsaa\Uuid\Uuid;

/**
 * Controller for Certificate
 */
class CertificateController extends MvcController
{
    /**
     * Certificate Provider
     * @var CertificateProviderInterface $provider
     */
    protected $provider;
    
    /**
     * Constructor for Certificate controller
     * @param AbstractModule $module
     */
    public function __construct(AbstractModule $module) {
        parent::__construct($module);
        
        $this->provider = $this->module->getProvider();
    }
    
    
    /**
     * Pages
     */
        
    /**
     * Certificate@index
     */
    public function index() {
        global $WT_TREE;
        
        $controller = new PageController();
        $controller
        ->setPageTitle(I18N::translate('Certificate'))
        ->restrictAccess(
            $this->module->getSetting('MAJ_SHOW_CERT', Auth::PRIV_HIDE) >= Auth::accessLevel($WT_TREE)
        );
        
        $cid = Filter::get('cid');
        
        $certificate = null;
        if(!empty($cid) && strlen($cid) > 22){
            $certificate = Certificate::getInstance($cid, $WT_TREE, null, $this->provider);
        }
        
        $data = new ViewBag();
        $data->set('title', $controller->getPageTitle());
        
        $data->set('has_certif', false);
        if($certificate) {
            $controller->restrictAccess($certificate->canShow());
            $data->set('title', $certificate->getTitle());
            $data->set('has_certif', true);
            $data->set('certificate', $certificate);
            
            $data->set(
                'url_certif_city', 
                'module.php?mod=' . Constants::MODULE_MAJ_CERTIF_NAME . 
                    '&mod_action=Certificate@listAll' .
                    '&ged=' . $WT_TREE->getNameUrl() .
                    '&city=' . Functions::encryptToSafeBase64($certificate->getCity())
            );
            
            $controller->addInlineJavascript('
                jQuery("#certificate-tabs").tabs();
			    jQuery("#certificate-tabs").css("visibility", "visible");    
            ');
            
            $data->set('has_linked_indis', false);
            $data->set('has_linked_fams', false);
            
            $linked_indis = $certificate->linkedIndividuals();
            $linked_fams = $certificate->linkedFamilies();
                        
            if($linked_indis && count($linked_indis) > 0) {
                $data->set('has_linked_indis', true);
                $data->set('linked_indis', $linked_indis);
            }
            
            if(!empty($linked_fams)) {
                $data->set('has_linked_fams', true);
                $data->set('linked_fams', $linked_fams);
            }
        }
        
        ViewFactory::make('Certificate', $this, $controller, $data)->render();
    }
    
    /**
     * Certificate@image
     */
    public function image() {      
        global $WT_TREE;
        
        $cid   = Filter::get('cid');
        $certificate = null;
        if(!empty($cid)) $certificate =  Certificate::getInstance($cid, $WT_TREE, null, $this->provider);
        
        $imageBuilder = new ImageBuilder($certificate);
        
        if (!empty(Filter::get('cb'))) {
            $imageBuilder->setExpireOffset($imageBuilder->getExpireOffset() * 7);
        }
        
        $imageBuilder
            ->setShowWatermark(Auth::accessLevel($WT_TREE) >= $this->module->getSetting('MAJ_SHOW_NO_WATERMARK', Auth::PRIV_HIDE))
            ->setFontMaxSize($this->module->getSetting('MAJ_WM_FONT_MAXSIZE', 18))
            ->setFontColor($this->module->getSetting('MAJ_WM_FONT_COLOR', '#4D6DF3'))
        ;
        
        $imageBuilder->render();
        
    }
    
    /**
     * Certificate@listAll
     */
    public function listAll() {
        global $WT_TREE;
        
        $controller = new PageController();
        $controller
            ->setPageTitle(I18N::translate('Certificates'))
            ->restrictAccess(
                $this->module->getSetting('MAJ_SHOW_CERT', Auth::PRIV_HIDE) >= Auth::accessLevel($WT_TREE)
            );
        
        $city = Filter::get('city');
        
        if(!empty($city) && strlen($city) > 22){
            $city = Functions::decryptFromSafeBase64($city);
            $controller->setPageTitle(I18N::translate('Certificates for %s', $city));
        }
        
        $data = new ViewBag();
        $data->set('title', $controller->getPageTitle());
        $data->set('url_module', $this->module->getName());
        $data->set('url_action', 'Certificate@listAll');
        $data->set('url_ged', $WT_TREE->getNameUrl());
        
        $data->set('cities', $this->provider->getCitiesList());
        $data->set('selected_city', $city);
        
        $data->set('has_list', false);        
        if(!empty($city)) {            
            $table_id = 'table-certiflist-' . Uuid::uuid4();
            
            $certif_list = $this->provider->getCertificatesList($city);            
            if(!empty($certif_list)) {                
                $data->set('has_list', true);
                $data->set('table_id', $table_id);
                $data->set('certificate_list', $certif_list);
                
                $controller
                    ->addExternalJavascript(WT_JQUERY_DATATABLES_JS_URL)
                    ->addInlineJavascript('
				        jQuery.fn.dataTableExt.oSort["text-asc"] = textCompareAsc;
				        jQuery.fn.dataTableExt.oSort["text-desc"] = textCompareDesc;
                        
                        jQuery("#'.$table_id.'").dataTable( {
        					dom: \'<"H"<"filtersH_' . $table_id . '">T<"dt-clear">pf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_' . $table_id . '">>\',
    					    '.I18N::datatablesI18N().',
    					    jQueryUI: true,
        					autoWidth: false,
        					processing: true,
        					columns: [
        		                    /* 0-Date */  			{ type: "num", width: "15%", class: "center" },
        		                    /* 1-Type */ 			{ type: "text", width: "5%", searchable: false, class: "center"},
        		                    /* 2-Certificate */     { type: "text", class: "left" }
        		                ],
        		            sorting: [[0,"asc"], [1,"asc"]],
        					displayLength: 20,
        					pagingType: "full_numbers"
        			   });
        				jQuery(".certificate-list").css("visibility", "visible");
        				jQuery(".loading-image").css("display", "none");
                    ');
            }
        }
        
        ViewFactory::make('CertificatesList', $this, $controller, $data)->render();
        
    }
    
    /**
     * Certificate@autocomplete
     */
    public function autocomplete() {
        global $WT_TREE;
        
        $controller = new JsonController();
        
        $city = Filter::get('city');
        $contains = Filter::get('term');        

        $controller
            ->restrictAccess(Auth::isEditor($WT_TREE) && !empty($city) && !empty($contains))
            ->pageHeader();
        
        $listCert = $this->provider->getCertificatesListBeginWith($city, $contains); 
        $controller->encode($listCert);
    }
}
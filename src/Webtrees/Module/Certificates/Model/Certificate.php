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

namespace MyArtJaub\Webtrees\Module\Certificates\Model;

use Fisharebest\Webtrees\Media;
use MyArtJaub\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\File;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Source;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module;
use MyArtJaub\Webtrees\Constants;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Repository;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Controller\IndividualController;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Family;

/**
 * Class for managing certificates, extending a Media object.
 * 
 */
class Certificate extends Media {
    
    const URL_PREFIX  = 'module.php?mod=myartjaub_certificates&mod_action=Certificate&cid=';
		
    /** @var string The "TITL" value from the GEDCOM 
     * This is a tweak to overcome the private level from the parent object...
     */
    protected $title = '';
    
	/**
	 * Certificate provider
	 * @var CertificateProviderInterface $provider
	 */
	protected $provider = null;
	
	/**
	 * Certificate type
	 * @var string $certType
	 */
	protected $certType = null;
	
	/**
	 * Certificate date
	 * @var (null|Date) $certDate
	 */
	protected $certDate = null;
	
	/**
	 * Certificate description
	 * @var unknown $certDetails
	 */
	protected $certDetails = null;
	
	/**
	 * Certificate source
	 * @var unknown $source
	 */
	protected $source = null;
	
	/**
	 * Extends the Media constructor.
	 * Create a certificate from the file path
	 * @param string $data
	 * @param Tree $tree Reference tree
	 * @param CertificateProviderInterface $provider
	 */
	public function __construct($data, Tree $tree, CertificateProviderInterface $provider) {
	    $this->provider = $provider;
		// Data is only the file name
		$data = str_replace("\\", '/', $data);
		$xref = Functions::encryptToSafeBase64($data);
		$gedcom = sprintf(
			'0 @%1$s@ OBJE'.PHP_EOL.
			'1 FILE %2$s',
			$xref, $data
		);
		parent::__construct($xref, $gedcom, '', $tree);
				
		$this->title = basename($this->getFilename(), '.'.$this->extension());
		
		$ct = preg_match("/(?<year>\d{1,4})(\.(?<month>\d{1,2}))?(\.(?<day>\d{1,2}))?( (?<type>[A-Z]{1,2}) )?(?<details>.*)/", $this->title, $match);
		if($ct > 0){
			$monthId = (int) $match['month'];
			$calendarShortMonths = Functions::getCalendarShortMonths();
			$monthShortName = array_key_exists($monthId, $calendarShortMonths) ? $calendarShortMonths[$monthId] : $monthId;
			$this->certDate = new Date($match['day'].' '.strtoupper($monthShortName).' '.$match['year']);
			$this->certType = $match['type'];
			$this->certDetails = $match['details'];			
		} else {
			$this->certDetails = $this->title;
		}
	}
	
	/**
	 * {@inhericDoc}
	 * @see \Fisharebest\Webtrees\GedcomRecord::getInstance()
	 */	
	static public function getInstance($xref, Tree $tree, $gedcom = null, CertificateProviderInterface $provider = null) {
		try{
			$certfile = Functions::decryptFromSafeBase64($xref);
			
			//NEED TO CHECK THAT !!!
			if(Functions::isValidPath($certfile, true)) {
				return new Certificate($certfile, $tree, $provider);
			}
		}
		catch (\Exception $ex) { 
			Log::addErrorLog('Certificate module error : > '.$ex->getMessage().' < with data > '.$xref.' <');
		}	

		return null;
	}
		
	/**
	 * {@inhericDoc}
	 * @see \Fisharebest\Webtrees\Media::canShowByType()
	 */
	protected function canShowByType($access_level) {	    
		$linked_indis = $this->linkedIndividuals('_ACT');
		foreach ($linked_indis as $linked_indi) {
			if ($linked_indi && !$linked_indi->canShow($access_level)) {
				return false;
			}
		}
		$linked_fams = $this->linkedFamilies('_ACT');
		foreach ($linked_fams as $linked_fam) {
			if ($linked_fam && !$linked_fam->canShow($access_level)) {
				return false;
			}
		}
		
		return count($linked_indis) + count($linked_fams) > 0;
	}
	
	/**
	 * Define a source associated with the certificate
	 *
	 * @param string|Source $xref
	 */
	public function setSource($xref){
		if($xref instanceof Source){
			$this->source = $data;
		} else {
			$this->source = Source::getInstance($xref, $this->tree);
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Fisharebest\Webtrees\Media::getTitle()
	 */
	public function getTitle() {
	    return $this->title;
	}
	
	/**
	 * Returns the certificate date
	 *
	 * @return Date Certificate date
	 */
	public function getCertificateDate(){
		return $this->certDate;
	}
	
	/**
	 * Returns the type of certificate
	 *
	 * @return string Certificate date
	 */
	public function getCertificateType(){
		return $this->certType;
	}
	
	/**
	 * Returns the details of the certificate (basename without the date and type)
	 *
	 * @return string Certificate details
	 */
	public function getCertificateDetails(){
		return $this->certDetails;
	}
	
	/**
	 * Return the city the certificate comes from
	 *
	 * @return string|NULL Certificate city
	 */
	public function getCity(){
		$chunks = explode('/', $this->getFilename(), 2);
		if(count($chunks) > 1) return $chunks[0];
		return null;
	}
	
	/**
	 * {@inhericDoc}
	 * @see \Fisharebest\Webtrees\Media::getServerFilename()
	 */
	public function getServerFilename($which='main') {
		$filename =  $this->provider->getRealCertificatesDirectory() . $this->getFilename();
		return Functions::encodeUtf8ToFileSystem($filename);
	}
	
	/**
	 * {@inhericDoc}
	 * @see \Fisharebest\Webtrees\Media::getHtmlUrlDirect()
	 */
	public function getHtmlUrlDirect($which = 'main', $download = false) {
		$sidstr = ($this->source) ? '&sid='.$this->source->getXref() : '';
		return
			'module.php?mod='. \MyArtJaub\Webtrees\Constants::MODULE_MAJ_CERTIF_NAME . 
			'&mod_action=Certificate@image' . 
			'&ged='. $this->tree->getNameUrl() .
			'&cid=' . $this->getXref() . $sidstr .
			'&cb=' . $this->getEtag($which);
	}
	
	/**
	 * Returns the watermark text to be displayed.
	 * If a source ID has been provided with the certificate, use this image,
	 * otherwise try to find a linked source within the GEDCOM (the first occurence found is used).
	 * Else a default text is used.
	 *
	 * @return string Watermark text
	 */
	 public function getWatermarkText(){	
	 	$module = Module::getModuleByName(Constants::MODULE_MAJ_CERTIF_NAME);
	 	
	 	if($module) {
    		$wmtext = $module->getSetting('MAJ_WM_DEFAULT', I18N::translate('This image is protected under copyright law.'));
    		$sid= Filter::get('sid', WT_REGEX_XREF);	
    	
    		if($sid){
    			$this->source = Source::getInstance($sid, $this->tree);
    		}
    		else{
    			$this->fetchALinkedSource();  // the method already attach the source to the Certificate object;
    		}
    		
    		if($this->source) {
    			$wmtext = '&copy;';
    			$repofact = $this->source->getFirstFact('REPO');
    			if($repofact) {
    				$repo = $repofact->getTarget();
    				if($repo && $repo instanceof Repository)  $wmtext .= ' '.$repo->getFullName().' - ';
    			}
    			$wmtext .= $this->source->getFullName();			
    		}	
    		return $wmtext;
	 	}
	 	return '';
	}
	
	/**
	 * {@inhericDoc}
	 * @see \Fisharebest\Webtrees\Media::displayImage()
	 */
	public function displayImage($which = 'main') {
		global $controller;
		
		$js = '	if(isCertifColorboxActive == 0) { 
					activatecertifcolorbox();
					isCertifColorboxActive = 1;
				}
		';
		
		$script = '';
		if($controller && !($controller instanceof IndividualController)){
			$controller->addInlineJavascript('$(document).ready(function() { '.$js.' });');
		} else {
			$script = '<script>' . $js . '</script>';
		}
		
		if ($which == 'icon' || !file_exists($this->getServerFilename())) {
			// Use an icon
			$image =
			'<i dir="auto" class="icon-maj-certificate margin-h-2"' .
			' title="' . strip_tags($this->getFullName()) . '"' .
			'></i>';
		} else {
			$imgsize = getimagesize($this->getServerFilename());
			$image =
			'<img' .
			' class ="'. 'certif_image'					 	. '"' .
			' dir="'   . 'auto'                           	. '"' . // For the tool-tip
			' src="'   . $this->getHtmlUrlDirect() 			. '"' .
			' alt="'   . strip_tags($this->getFullName()) 	. '"' .
			' title="' . strip_tags($this->getFullName()) 	. '"' .
			$imgsize[3] . // height="yyy" width="xxx"
			'>';
		}	
		return
		'<a' .
		' class="'          . 'certgallery'                          . '"' .
		' href="'           . $this->getHtmlUrlDirect()    		 . '"' .
		' type="'           . $this->mimeType()                  . '"' .
		' data-obje-url="'  . $this->getHtmlUrl()                . '"' .
		' data-title="'     . strip_tags($this->getFullName())   . '"' .
		'>' . $image . '</a>'.$script;
	}
	
	/**
	 * {@inhericDoc}
	 * @see \Fisharebest\Webtrees\GedcomRecord::linkedIndividuals()
	 */
	public function linkedIndividuals($link = '_ACT'){
		$rows = Database::prepare(
				'SELECT i_id AS xref, i_gedcom AS gedcom'.
				' FROM `##individuals`'.
				' WHERE i_file= :gedcom_id AND i_gedcom LIKE :gedcom')
		->execute(array(
		    'gedcom_id' => $this->tree->getTreeId(),
		    'gedcom' => '%_ACT '.$this->getFilename().'%'		    
		))->fetchAll();
		
		$list = array();
		foreach ($rows as $row) {
			$record = Individual::getInstance($row->xref, $this->tree, $row->gedcom);
			if ($record->canShowName()) {
				$list[] = $record;
			}
		}
		return $list;
	}

	/**
	 * {@inhericDoc}
	 * @see \Fisharebest\Webtrees\GedcomRecord::linkedFamilies()
	 */
	public function linkedFamilies($link = '_ACT'){
		$rows = Database::prepare(
				'SELECT f_id AS xref, f_gedcom AS gedcom'.
				' FROM `##families`'.
				' WHERE f_file= :gedcom_id AND f_gedcom LIKE :gedcom')
		->execute(array(
		    'gedcom_id' => $this->tree->getTreeId(),
		    'gedcom' => '%_ACT '.$this->getFilename().'%'		    
		))->fetchAll();
		
		$list = array();
		foreach ($rows as $row) {
			$record = Family::getInstance($row->xref, $this->tree, $row->gedcom);
			if ($record->canShowName()) {
				$list[] = $record;
			}
		}
		return $list;
	}
	
	/**
	 * Returns a unique source linked to the certificate
	 *
	 * @return Source|NULL Linked source
	 */
	public function fetchALinkedSource(){		
		$sid = null;
		
		// Try to find in individual, then families, then other types of records. We are interested in the first available value.
		$ged =
		Database::prepare(
				'SELECT i_gedcom AS gedrec FROM `##individuals`'.
				' WHERE i_file=:gedcom_id AND i_gedcom LIKE :gedcom')
		  ->execute(array(
		      'gedcom_id' => $this->tree->getTreeId(), 
		      'gedcom' => '%_ACT '.$this->getFilename().'%'		      
		  ))->fetchOne();
		if(!$ged){
			$ged = Database::prepare(
					'SELECT f_gedcom AS gedrec FROM `##families`'.
					' WHERE f_file=:gedcom_id AND f_gedcom LIKE :gedcom')
			     ->execute(array(
			         'gedcom_id' => $this->tree->getTreeId(), 
			         'gedcom' => '%_ACT '.$this->getFilename().'%'			         
			     ))->fetchOne();
			if(!$ged){
				$ged = Database::prepare(
				    'SELECT o_gedcom AS gedrec FROM `##other`'.
				    ' WHERE o_file=:gedcom_id AND o_gedcom LIKE :gedcom')
				    ->execute(array(
				        'gedcom_id' => $this->tree->getTreeId(),
				        'gedcom' => '%_ACT '.$this->getFilename().'%'				        
				    ))->fetchOne();
			}
		}
		//If a record has been found, parse it to find the source reference.
		if($ged){
			$gedlines = explode("\n", $ged);
			$level = 0;
			$levelsource = -1;
			$sid_tmp=null;
			$sourcefound = false;
			foreach($gedlines as $gedline){
				// Get the level
				if (!$sourcefound && preg_match('~^('.WT_REGEX_INTEGER.') ~', $gedline, $match)) {
					$level = $match[1];
					//If we are not any more within the context of a source, reset
					if($level <= $levelsource){
						$levelsource = -1;
						$sid_tmp = null;
					}
					// If a source, get the level and the reference
					if (preg_match('~^'.$level.' SOUR @('.WT_REGEX_XREF.')@$~', $gedline, $match2)) {
						$levelsource = $level;
						$sid_tmp=$match2[1];
					}
					// If the image has be found, get the source reference and exit.
					if($levelsource>=0 && $sid_tmp && preg_match('~^'.$level.' _ACT '.preg_quote($this->getFilename()).'~', $gedline, $match3)){
						$sid = $sid_tmp;
						$sourcefound = true;
					}
				}
			}
		}
		
		if($sid) $this->source = Source::getInstance($sid, $this->tree);
		
		return $this->source;	
	}
		
}

?>
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

use MyArtJaub\Webtrees\Functions\Functions;
use Fisharebest\Webtrees\File;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module;

/**
 * Provide access to certificates on the file system
 */
class CertificateFileProvider implements CertificateProviderInterface {
    
    /**
     * Relative path to the root certificate folder
     * @var string $root_path
     */
    protected $root_path;
    
    /**
     * Reference tree
     * @var Tree $tree
     */
    protected $tree;
    
    /**
     * Cached list of certificates' cities.
     * @var (null|array) $cities_list
     */
    protected $cities_list = null;
    
    /**
     * Constructor for the File Provider
     * @param string $root_path
     * @param Tree $tree
     */
    public function __construct($root_path, Tree $tree) {
        $this->root_path = $root_path;
        $this->tree = $tree;
    }
        
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Module\Certificates\Model\CertificateProviderInterface::getRealCertificatesDirectory()
     */
    public function getRealCertificatesDirectory(){
        return WT_DATA_DIR . $this->root_path;
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Module\Certificates\Model\CertificateProviderInterface::getCitiesList()
     */
    public function getCitiesList(){
        if(!isset($this->cities_list) || is_null($this->cities_list)){
            $certdir = $this->getRealCertificatesDirectory();
            $this->cities_list = array();
    
            $dir = opendir($certdir);
            
            while($entry = readdir($dir)){
                if($entry != '.' && $entry != '..' && is_dir($certdir.$entry)){
                    $this->cities_list[]= Functions::encodeFileSystemToUtf8($entry);
                }
            }
            sort($this->cities_list);
        }
        return $this->cities_list;
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Module\Certificates\Model\CertificateProviderInterface::getCertificatesList()
     */
    public function getCertificatesList($selCity){
    
        $selCity = Functions::encodeUtf8ToFileSystem($selCity);
    
        $certdir = $this->getRealCertificatesDirectory();
        $tabCertif= array();
    
        if(is_dir($certdir.$selCity)){
            $dir=opendir($certdir.$selCity);
            while($entry = readdir($dir)){
                if($entry!='.' && $entry!='..' && !is_dir($certdir.$entry.'/')){
                    $path = Functions::encodeFileSystemToUtf8($selCity.'/'.$entry);
                    $certificate = new Certificate($path, $this->tree, $this);
                    if(Functions::isImageTypeSupported($certificate->extension())){
                        $tabCertif[] = 	$certificate;
                    }
                }
            }
        }
        return $tabCertif;
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Module\Certificates\Model\CertificateProviderInterface::getCertificatesListBeginWith()
     */
    public function getCertificatesListBeginWith($city, $contains, $limit= 9999){
        $tabFiles= array();
        $dirPath=$certdir = $this->getRealCertificatesDirectory() . Functions::encodeUtf8ToFileSystem($city).'/';
        $contains = utf8_decode($contains);
        $nbCert = 0;
    
        if(is_dir($dirPath)){
            $dir=opendir($dirPath);
            while(($entry = readdir($dir)) && $nbCert < $limit){
                if($entry!='.' && $entry!='..' && $entry!='Thumbs.db' &&!is_dir($dirPath.$entry.'/') && stripos($entry, $contains)!== false){
                    $tabFiles[]= Functions::encodeFileSystemToUtf8($entry);
                    $nbCert++;
                }
            }
        }
        sort($tabFiles);
        return $tabFiles;
    }
    
}

?>
<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

namespace MyArtJaub\Webtrees\Module\Certificates\Model;

/**
 * Interface for providers of certificates
 */
interface CertificateProviderInterface  {
    
    /**
     * Returns the certificates directory path as it is really (within the firewall directory).
     *
     * @return string Real certificates directory path
     */
    function getRealCertificatesDirectory();
    
    /**
     * Returns an array of the folders (cities) in the certificate directory.
     * Cities name are UTF8 encoded.
     *
     * @return array Array of cities name
     */
    function getCitiesList();
    
    /**
     * Returns the list of available certificates for a specified city.
     * Format of the list :
     * < file name , date of the certificate , type of certificate , name of the certificate >
     * Data are UTF8 encoded.
     *
     * @param string $selCity City to look in
     * @return array List of certificates
     */
    function getCertificatesList($selCity);
    
    /**
     * Return the list of certificates from a city $city and containing the characters $contains
     *
     * @param string $city City to search in
     * @param string $contains Characters to match
     * @param string $limit Maximum number of results
     * @return array Array of matching certificates
     */
    function getCertificatesListBeginWith($city, $contains, $limit);
    
}

?>
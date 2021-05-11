<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Elements;

use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Elements\AbstractElement;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;

/**
 * Gedcom element for certificate associated to a source.
 * Structure:
 *  n   SOUR @XREF@
 *  n+1 _ACT certificate_file_path
 */
class SourceCertificate extends AbstractElement
{
    protected CertificatesModule $module;
    protected CertificateFilesystemService $certif_filesystem;
    protected UrlObfuscatorService $url_obfuscator_service;

    /**
     * Constructor for SourceCertificate element
     *
     * @param string $label
     * @param CertificatesModule $module
     * @param CertificateFilesystemService $certif_filesystem
     * @param UrlObfuscatorService $url_obfuscator_service
     */
    public function __construct(
        string $label,
        CertificatesModule $module,
        CertificateFilesystemService $certif_filesystem = null,
        UrlObfuscatorService $url_obfuscator_service = null
    ) {
        parent::__construct($label, null);
        $this->module = $module;
        $this->certif_filesystem = $certif_filesystem ?? app(CertificateFilesystemService::class);
        $this->url_obfuscator_service = $url_obfuscator_service ?? app(UrlObfuscatorService::class);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Elements\AbstractElement::canonical()
     */
    public function canonical($value): string
    {
        return strtr($value, '\\', '/');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Elements\AbstractElement::edit()
     */
    public function edit(string $id, string $name, string $value, Tree $tree): string
    {
        list($city, $file) = explode('/', $this->canonical($value), 2) + ['', ''];

        $cities = array_map(function (string $item): array {
            return [$this->url_obfuscator_service->obfuscate($item), $item];
        }, $this->certif_filesystem->cities($tree));

        return view($this->module->name() . '::components/edit-certificate', [
            'module_name'   =>  $this->module->name(),
            'tree'          =>  $tree,
            'id'            =>  $id,
            'name'          =>  $name,
            'cities'        =>  $cities,
            'value'         =>  $this->canonical($value),
            'value_city'    =>  $city,
            'value_file'    =>  $file,
            'js_script_url' =>  $this->module->assetUrl('js/certificates.min.js')
        ]);
    }
}

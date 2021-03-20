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

namespace MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying list of certificates
 */
class CertificatesList implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * @var CertificatesModule|null $module
     */
    private $module;

    /**
     * @var CertificateFilesystemService $certif_filesystem
     */
    private $certif_filesystem;

    /**
     * @var UrlObfuscatorService $url_obfuscator_service
     */
    private $url_obfuscator_service;


    /**
     * Constructor for CertificatesList Request Handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(
        ModuleService $module_service,
        CertificateFilesystemService $certif_filesystem,
        UrlObfuscatorService $url_obfuscator_service
    ) {
        $this->module = $module_service->findByInterface(CertificatesModule::class)->first();
        $this->certif_filesystem = $certif_filesystem;
        $this->url_obfuscator_service = $url_obfuscator_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $title = I18N::translate('Certificates');

        $cities = array_map(function (string $item): array {
            return [$this->url_obfuscator_service->obfuscate($item), $item];
        }, $this->certif_filesystem->cities($tree));

        $city = $request->getAttribute('cityobf') ?? $request->getQueryParams()['cityobf'] ?? '';

        if ($city !== '' && $this->url_obfuscator_service->tryDeobfuscate($city)) {
            $title = I18N::translate('Certificates for %s', $city);
            $certificates = $this->certif_filesystem->certificatesForCity($tree, $city);
        }

        return $this->viewResponse($this->module->name() . '::certificates-list', [
            'title'                     =>  $title,
            'tree'                      =>  $tree,
            'module_name'               =>  $this->module->name(),
            'cities'                    =>  $cities,
            'selected_city'             =>  $city,
            'certificates_list'         =>  $certificates ?? collect(),
            'url_obfuscator_service'    =>  $this->url_obfuscator_service
        ]);
    }
}

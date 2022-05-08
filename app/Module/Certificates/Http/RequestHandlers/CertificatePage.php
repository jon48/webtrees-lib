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

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Http\RequestHandlers\TreePage;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateDataService;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying details of a certificate
 */
class CertificatePage implements RequestHandlerInterface
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
     * @var CertificateDataService $certif_data_service
     */
    private $certif_data_service;

    /**
     * @var UrlObfuscatorService $url_obfuscator_service
     */
    private $url_obfuscator_service;


    /**
     * Constructor for CertificatePage Request Handler
     *
     * @param ModuleService $module_service
     * @param CertificateFilesystemService $certif_filesystem
     * @param CertificateDataService $certif_data_service
     * @param UrlObfuscatorService $url_obfuscator_service
     */
    public function __construct(
        ModuleService $module_service,
        CertificateFilesystemService $certif_filesystem,
        CertificateDataService $certif_data_service,
        UrlObfuscatorService $url_obfuscator_service
    ) {
        $this->module = $module_service->findByInterface(CertificatesModule::class)->first();
        $this->certif_filesystem = $certif_filesystem;
        $this->certif_data_service = $certif_data_service;
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

        $tree = Validator::attributes($request)->tree();

        $certif_path = Validator::attributes($request)->string('cid', '');
        if ($certif_path !== '' && $this->url_obfuscator_service->tryDeobfuscate($certif_path)) {
            $certificate = $this->certif_filesystem->certificate($tree, $certif_path);
        }

        if (!isset($certificate)) {
            FlashMessages::addMessage('The requested certificate is not valid.');
            return redirect(route(TreePage::class, ['tree' => $tree->name()]));
        }

        return $this->viewResponse($this->module->name() . '::certificate-page', [
            'title'                     =>  I18N::translate('Certificate - %s', $certificate->name()),
            'tree'                      =>  $tree,
            'module_name'               =>  $this->module->name(),
            'certificate'               =>  $certificate,
            'url_obfuscator_service'    =>  $this->url_obfuscator_service,
            'linked_individuals'        =>  $this->certif_data_service->linkedIndividuals($certificate),
            'linked_families'           =>  $this->certif_data_service->linkedFamilies($certificate)
        ]);
    }
}

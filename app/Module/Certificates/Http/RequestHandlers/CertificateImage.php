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

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Factories\CertificateImageFactory;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Model\Watermark;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateDataService;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for providing an image of a certificate.
 */
class CertificateImage implements RequestHandlerInterface
{
    /**
     * @var CertificatesModule|null $module
     */
    private $module;

    /**
     * @var CertificateFilesystemService $certif_filesystem
     */
    private $certif_filesystem;

    /**
     * @var CertificateImageFactory $certif_image_factory
     */
    private $certif_image_factory;

    /**
     * @var CertificateDataService $certif_data_service
     */
    private $certif_data_service;

    /**
     * @var UrlObfuscatorService $url_obfuscator_service
     */
    private $url_obfuscator_service;

    /**
     * Constructor for Certificate Image Request Handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(
        ModuleService $module_service,
        CertificateFilesystemService $certif_filesystem,
        CertificateDataService $certif_data_service,
        UrlObfuscatorService $url_obfuscator_service
    ) {
        $this->module = $module_service->findByInterface(CertificatesModule::class)->first();
        $this->certif_filesystem = $certif_filesystem;
        $this->certif_image_factory = new CertificateImageFactory($this->certif_filesystem);
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

        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $user = $request->getAttribute('user');
        assert($user instanceof UserInterface);

        $certif_path = $request->getAttribute('cid');
        $certificate = null;
        if ($certif_path !== '' && $this->url_obfuscator_service->tryDeobfuscate($certif_path)) {
            $certificate = $this->certif_filesystem->certificate($tree, $certif_path);
        }

        if ($certificate === null) {
            return $this->certif_image_factory
            ->replacementImageResponse((string) StatusCodeInterface::STATUS_NOT_FOUND)
            ->withHeader('X-Image-Exception', I18N::translate('The certificate was not found in this family tree.'))
            ;
        }

        $use_watermark = $this->certif_image_factory->certificateNeedsWatermark($certificate, $user);
        $watermark = $use_watermark ? $this->watermark($request, $certificate) : null;

        return $this->certif_image_factory->certificateFileResponse(
            $certificate,
            $use_watermark,
            $watermark
        );
    }

    /**
     * Get watermark data for a certificate.
     *
     * @param ServerRequestInterface $request
     * @param Certificate $certificate
     * @return Watermark
     */
    private function watermark(ServerRequestInterface $request, Certificate $certificate): Watermark
    {
        $color = $certificate->tree()->getPreference('MAJ_CERTIF_WM_FONT_COLOR', Watermark::DEFAULT_COLOR);
        $size = $certificate->tree()->getPreference('MAJ_CERTIF_WM_FONT_MAXSIZE');
        $text = $this->watermarkText($request, $certificate);

        return new Watermark($text, $color, is_numeric($size) ? (int) $size : Watermark::DEFAULT_SIZE);
    }

    /**
     * Get the text to be watermarked for a certificate.
     *
     * @param ServerRequestInterface $request
     * @param Certificate $certificate
     * @return string
     */
    private function watermarkText(ServerRequestInterface $request, Certificate $certificate): string
    {
        $sid = $request->getQueryParams()['sid'] ?? '';
        if ($sid !== '') {
            $source = Registry::sourceFactory()->make($sid, $certificate->tree());
        } else {
            $source = $this->certif_data_service->oneLinkedSource($certificate);
        }

        if ($source !== null && $source->canShowName()) {
            $repo = $source->facts(['REPO'])->first();
            if ($repo !== null && ($repo = $repo->target()) !== null && $repo->canShowName()) {
                return I18N::translate('© %s - %s', strip_tags($repo->fullName()), strip_tags($source->fullName()));
            }
            return strip_tags($source->fullName());
        }
        $default_text = $certificate->tree()->getPreference('MAJ_CERTIF_WM_DEFAULT');
        return $default_text !== '' ? $default_text : I18N::translate('This image is protected under copyright law.');
    }
}

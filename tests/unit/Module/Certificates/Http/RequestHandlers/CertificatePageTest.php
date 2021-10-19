<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Tests\Helpers\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificatePage;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateDataService;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;

/**
 * Class CertificatePageTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificatePage
 */
class CertificatePageTest extends TestCase
{

    public function testHandle(): void
    {
        $tree = self::createMock(Tree::class);
        $certificate = $this->createMock(Certificate::class);
        $certificate->method('tree')->willReturn($tree);

        $certificate_module = $this->createMock(CertificatesModule::class);
        $certificate_module->setName('mod-certificates');

        $module_service = $this->createMock(ModuleService::class);
        $module_service->method('findByInterface')
            ->with(CertificatesModule::class)
            ->willReturn(collect([$certificate_module]));

        $certif_filesystem = $this->createMock(CertificateFilesystemService::class);
        $certif_filesystem->method('certificate')
            ->willReturnMap([
                [$tree, 'nocertificate', null],
                [$tree, 'certificate', $certificate]
            ]);

        $certif_data_service = $this->createMock(CertificateDataService::class);

        $url_obfuscator_service = $this->createMock(UrlObfuscatorService::class);
        $url_obfuscator_service->method('tryDeobfuscate')->willReturn(true);

        $certificate_page = new CertificatePage(
            $module_service,
            $certif_filesystem,
            $certif_data_service,
            $url_obfuscator_service
        );

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('cid', 'nocertificate');

        $response = $certificate_page->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_FOUND, $response->getStatusCode());

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('cid', 'certificate');

        self::useDefaultViewFor('mod-certificates::certificate-page');
        self::useDefaultViewFor('::layouts/default');

        $response = $certificate_page->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testHandleWithNoModule(): void
    {
        $module_service = $this->createMock(ModuleService::class);
        $module_service->method('findByInterface')
            ->with(CertificatesModule::class)
            ->willReturn(collect());

        $certif_filesystem = $this->createMock(CertificateFilesystemService::class);
        $certif_data_service = $this->createMock(CertificateDataService::class);
        $url_obfuscator_service = $this->createMock(UrlObfuscatorService::class);

        $certificate_page = new CertificatePage(
            $module_service,
            $certif_filesystem,
            $certif_data_service,
            $url_obfuscator_service
        );

        $request = self::createRequest();

        self::expectException(HttpNotFoundException::class);
        $certificate_page->handle($request);
    }
}

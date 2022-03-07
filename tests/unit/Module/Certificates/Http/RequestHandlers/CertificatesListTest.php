<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Tests\Helpers\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificatesList;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;

/**
 * Class CertificatesListTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificatesList
 */
class CertificatesListTest extends TestCase
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
        $certif_filesystem->method('cities')->willReturn(['city1']);

        $url_obfuscator_service = $this->createMock(UrlObfuscatorService::class);
        $url_obfuscator_service->method('tryDeobfuscate')->willReturn(true);
        $url_obfuscator_service->method('obfuscate')->willReturnArgument(0);

        $certificates_list = new CertificatesList($module_service, $certif_filesystem, $url_obfuscator_service);

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('cityobf', 'city');

        self::useDefaultViewFor('mod-certificates::certificates-list');
        self::useDefaultViewFor('::layouts/default');

        $response = $certificates_list->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    public function testHandleWithNoModule(): void
    {
        $module_service = $this->createMock(ModuleService::class);
        $module_service->method('findByInterface')
            ->with(CertificatesModule::class)
            ->willReturn(collect());

        $certif_filesystem = $this->createMock(CertificateFilesystemService::class);

        $url_obfuscator_service = $this->createMock(UrlObfuscatorService::class);

        $certificate_page = new CertificatesList(
            $module_service,
            $certif_filesystem,
            $url_obfuscator_service
        );

        $request = self::createRequest();

        self::expectException(HttpNotFoundException::class);
        $certificate_page->handle($request);
    }
}

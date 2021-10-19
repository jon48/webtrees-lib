<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Repository;
use Fisharebest\Webtrees\Source;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Factories\SourceFactory;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificateImage;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateDataService;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;

/**
 * Class CertificateImageTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificateImage
 */
class CertificateImageTest extends TestCase
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

        $certificate_image = new CertificateImage(
            $module_service,
            $certif_filesystem,
            $certif_data_service,
            $url_obfuscator_service
        );

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('cid', 'nocertificate');

        $response = $certificate_image->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertNotEmpty($response->getHeaderLine('X-Image-Exception'));

        $request = self::createRequest()
            ->withAttribute('tree', $tree)
            ->withAttribute('cid', 'certificate');

        $response = $certificate_image->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame('Image source not readable', $response->getHeaderLine('X-Image-Exception'));

        $source_norepo = $this->createMock(Source::class);
        $source_norepo->method('canShowName')->willReturn(true);

        $repo = $this->createMock(Repository::class);
        $repo->method('canShowName')->willReturn(true);

        $fact = $this->createMock(Fact::class);
        $fact->method('target')->willReturn($repo);

        $source_repo = $this->createMock(Source::class);
        $source_repo->method('canShowName')->willReturn(true);
        $source_repo->method('facts')->willReturn(collect([$fact]));

        $source_factory = $this->createMock(SourceFactory::class);
        $source_factory->method('make')->willReturnMap([
            ['S1', $tree, null, $source_norepo],
            ['S2', $tree, null, $source_repo]
        ]);

        $old_source_factory = Registry::sourceFactory();
        Registry::sourceFactory($source_factory);

        $response = $certificate_image->handle($request->withQueryParams(['sid' => 'S1']));
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $response = $certificate_image->handle($request->withQueryParams(['sid' => 'S2']));
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        Registry::sourceFactory($old_source_factory);
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

        $certificate_image = new CertificateImage(
            $module_service,
            $certif_filesystem,
            $certif_data_service,
            $url_obfuscator_service
        );

        $request = self::createRequest();

        self::expectException(HttpNotFoundException::class);
        $certificate_image->handle($request);
    }
}

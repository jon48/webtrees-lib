<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\SearchService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AutoCompleteFile;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;

/**
 * Class AutoCompleteFileTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AutoCompleteFile
 */
class AutoCompleteFileTest extends TestCase
{

    public function testHandle(): void
    {
        $certificate_module = $this->createMock(CertificatesModule::class);
        $certificate_module->setName('mod-certificates');

        $module_service = $this->createMock(ModuleService::class);
        $module_service->method('findByInterface')
            ->with(CertificatesModule::class)
            ->willReturn(collect([$certificate_module]));

        $certif_filesystem = $this->createMock(CertificateFilesystemService::class);
        $certif_filesystem->method('certificatesForCityContaining')->willReturn(collect());

        $url_obfuscator_service = $this->createMock(UrlObfuscatorService::class);
        $url_obfuscator_service->method('tryDeobfuscate')->willReturn(true);

        $search_service = $this->createMock(SearchService::class);

        $autocompletefile = new AutoCompleteFile(
            $module_service,
            $certif_filesystem,
            $url_obfuscator_service,
            $search_service
        );

        $tree = self::createMock(Tree::class);

        $request = self::createRequest()->withAttribute('tree', $tree);

        $response = $autocompletefile->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $request = $request
            ->withAttribute('query', 'test')
            ->withQueryParams(['cityobf' => 'city']);
        $response = $autocompletefile->handle($request);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }
}

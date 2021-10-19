<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Model;

use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;

/**
 * Class CertificateTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Model\Certificate
 */
class CertificateTest extends TestCase
{
    /**
     * Data provider for certificate tests
     * @return array<array<string|array<string>>>
     */
    public function certificateData(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            ['city/certificate.jpg', ['city/certificate.jpg', 'certificate', 'certificate.jpg', 'city', '', '', 'certificate', 'certificate', 'image/jpeg']],
            ['city\\certificate.png', ['city/certificate.png', 'certificate', 'certificate.png', 'city', '', '', 'certificate', 'certificate', 'image/png']],
            ['city/1830.04.15 certificate.jpg', ['city/1830.04.15 certificate.jpg', '1830.04.15 certificate', '1830.04.15 certificate.jpg', 'city', '15 APR 1830', '', 'certificate', 'certificate', 'image/jpeg']],
            ['city/1830.04 T certificate.jpg', ['city/1830.04 T certificate.jpg', '1830.04 T certificate', '1830.04 T certificate.jpg', 'city', 'APR 1830', 'T', 'certificate', 'certificate', 'image/jpeg']],
            ['city/cert SORTFIRST test SORTOTHER.jpg', ['city/cert SORTFIRST test SORTOTHER.jpg', 'cert SORTFIRST test SORTOTHER', 'cert SORTFIRST test SORTOTHER.jpg', 'city', '', '', 'cert SORTFIRST test SORTOTHER', 'SORTFIRST_SORTOTHER_cert SORTFIRST test SORTOTHER', 'image/jpeg']],
        ];
        // phpcs:enable
    }

    /**
     * @param string $path
     * @param array<string> $expected
     *
     * @dataProvider certificateData
     */
    public function testCertificateData(string $path, array $expected): void
    {
        $tree = $this->createMock(Tree::class);
        $certificate = new Certificate($tree, $path);

        self::assertSame($tree, $certificate->tree());
        self::assertSame($path, $certificate->path());
        self::assertSame($expected[0], $certificate->gedcomPath());
        self::assertSame($expected[1], $certificate->name());
        self::assertSame($expected[2], $certificate->filename());
        self::assertSame($expected[3], $certificate->city());
        self::assertSame(0, Date::compare(new Date($expected[4]), $certificate->date()));
        self::assertSame($expected[5], $certificate->type());
        self::assertSame($expected[6], $certificate->description());
        self::assertSame($expected[7], $certificate->sortDescription());
        self::assertSame($expected[8], $certificate->mimeType());
    }

    public function testUrlParameters(): void
    {
        $tree = $this->createMock(Tree::class);
        $certificate = new Certificate($tree, 'city/certificate.jpg');

        $url_obfuscator_service = $this->createMock(UrlObfuscatorService::class);
        $url_obfuscator_service->method('obfuscate')->willReturnArgument(0);

        $parameters = $certificate->urlParameters($url_obfuscator_service);
        self::assertCount(2, $parameters);
        self::assertSame('city/certificate.jpg', $parameters['cid'] ?? '');
    }
}

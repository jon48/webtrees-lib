<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Elements;

use Fisharebest\Webtrees\Elements\AbstractElementTest;
use MyArtJaub\Tests\Helpers\Webtrees\TestCase;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Elements\SourceCertificate;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;

/**
 * Class SourceCertificateTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Elements\SourceCertificate
 */
class SourceCertificateTest extends AbstractElementTest
{
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::setUp()
     */
    public function setUp(): void
    {
        parent::setUp();

        $module = $this->createMock(CertificatesModule::class);
        $module->setName('mod-certificates');

        $certif_filesystem = $this->createMock(CertificateFilesystemService::class);
        $certif_filesystem->method('cities')->willReturn(['city1']);

        $url_obfuscator_service = $this->createMock(UrlObfuscatorService::class);
        $url_obfuscator_service->method('obfuscate')->willReturn('obfuscated');

        self::$element = new SourceCertificate('label', $module, $certif_filesystem, $url_obfuscator_service);

        TestCase::useDefaultViewFor('mod-certificates::components/edit-certificate');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Elements\AbstractElementTest::testCanonical()
     */
    public function testCanonical(): void
    {
        self::assertSame('certificate-path.jpg', self::$element->canonical('certificate-path.jpg'));
        self::assertSame('dir/certificate.jpg', self::$element->canonical('dir/certificate.jpg'));
        self::assertSame('dir/certificate.jpg', self::$element->canonical('dir\\certificate.jpg'));
    }
}

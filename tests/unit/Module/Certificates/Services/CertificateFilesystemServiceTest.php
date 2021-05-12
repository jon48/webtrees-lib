<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Services;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Contracts\FilesystemFactoryInterface;
use Intervention\Image\ImageManager;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class CertificateFilesystemServiceTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService
 */
class CertificateFilesystemServiceTest extends TestCase
{
    /**
     * @var CertificateFilesystemService $certificate_filesystem_service
     */
    protected $certificate_filesystem_service;

    /**
     * @var Tree&MockObject $tree
     */
    protected $tree;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->certificate_filesystem_service = new CertificateFilesystemService();

        $this->tree = self::createMock(Tree::class);
        $this->tree->method('id')->willReturn(1);

        $this->filesystem = new Filesystem(new InMemoryFilesystemAdapter());
        $this->filesystem->createDirectory('location1');
        $this->filesystem->createDirectory('location2');
        $this->filesystem->createDirectory('location3');

        $image = new ImageManager(['driver' => 'gd']);

        // phpcs:disable Generic.Files.LineLength.TooLong
        $this->filesystem->write('location1/textfile.txt', 'Content text file');
        $this->filesystem->write('location1/invalidfile', 'Invalid');
        $this->filesystem->write('location1/image1.jpg', (string) $image->canvas(200, 200)->encode('jpg'));
        $this->filesystem->write('location1/1700.01.03 T first SURNAMEONE.jpg', (string) $image->canvas(200, 200)->encode('jpg'));
        $this->filesystem->write('location1/1700.12.14 T first SURNAMEONE second SURNAMETWO.jpg', (string) $image->canvas(200, 200)->encode('jpg'));
        $this->filesystem->write('location1/1713.04 first SURNAMEONE.jpg', (string) $image->canvas(200, 200)->encode('jpg'));
        $this->filesystem->write('location1/1713.3.28 T first SURNAMEONE small.jpg', (string) $image->canvas(30, 100)->encode('jpg'));
        // phpcs:enable

        $this->certificate_filesystem_service->setFilesystem($this->tree, $this->filesystem);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->certificate_filesystem_service);
    }

    public function testGetterSetterFilesystem(): void
    {
        self::assertSame($this->filesystem, $this->certificate_filesystem_service->filesystem($this->tree));

        $tree2 = self::createMock(Tree::class);
        $tree2->method('id')->willReturn(2);
        $tree2->method('getPreference')->with('MAJ_CERTIF_ROOTDIR') ->willReturn('certificates');

        $filesystem_factory = self::createMock(FilesystemFactoryInterface::class);
        $filesystem_factory->method('data')->willReturn(new Filesystem(new InMemoryFilesystemAdapter()));
        Registry::filesystem($filesystem_factory);

        self::assertInstanceOf(Filesystem::class, $this->certificate_filesystem_service->filesystem($tree2));
    }

    public function testCertificate(): void
    {
        $certificate = $this->certificate_filesystem_service->certificate($this->tree, 'location1/image1.jpg');
        self::assertNotNull($certificate);  /** @var Certificate $certificate */
        self::assertSame('image1', $certificate->name());

        $certificate = $this->certificate_filesystem_service->certificate($this->tree, 'location1/image_not.jpg');
        self::assertNull($certificate);
    }

    public function testCities(): void
    {
        self::assertCount(3, $this->certificate_filesystem_service->cities($this->tree));
    }

    public function testCertificatesForCity(): void
    {
        self::assertCount(5, $this->certificate_filesystem_service->certificatesForCity($this->tree, 'location1'));
    }

    public function testCertificatesForCityContaining(): void
    {
        self::assertCount(
            1,
            $this->certificate_filesystem_service->certificatesForCityContaining($this->tree, 'location1', 'image1')
        );
    }
}

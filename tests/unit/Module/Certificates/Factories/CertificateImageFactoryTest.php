<?php

namespace MyArtJaub\Tests\Unit\Webtrees\Module\Certificates\Factories;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\MediaFile;
use Fisharebest\Webtrees\TestCase;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Intervention\Image\ImageManager;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use MyArtJaub\Webtrees\Module\Certificates\Factories\CertificateImageFactory;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Model\Watermark;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use PHPUnit\Framework\MockObject\MockObject;
use BadMethodCallException;

/**
 * Class certificate_image_factoryTest.
 *
 * @covers \MyArtJaub\Webtrees\Module\Certificates\Factories\CertificateImageFactory
 */
class CertificateImageFactoryTest extends TestCase
{
    protected static Filesystem $filesystem;

    protected CertificateImageFactory $certificate_image_factory;

    /** @var Tree|MockObject $tree */
    protected $tree;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $im_manager = new ImageManager(['driver' => 'gd']);
        $image = $im_manager->canvas(200, 150, '#96C8FF');
        $image->text('Test', 50, 80);

        $mem_adapter = new InMemoryFilesystemAdapter();
        $mem_adapter->write('city/certificate.png', $image->stream('png')->getContents(), new Config());
        $mem_adapter->write('file.txt', 'Test', new Config());

        self::$filesystem = new Filesystem($mem_adapter);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tree = $this->createMock(Tree::class);
        $this->tree->method('id')->willReturn(42);

        $filesystem_service = $this->createMock(CertificateFilesystemService::class);
        $filesystem_service->method('filesystem')->with($this->tree)->willReturn(self::$filesystem);

        $this->certificate_image_factory = new CertificateImageFactory($filesystem_service);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\TestCase::tearDown()
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->certificate_image_factory);
    }

    public function testIsMimeTypeSupported(): void
    {
        self::assertTrue($this->certificate_image_factory->isMimeTypeSupported('image/jpeg'));
        self::assertFalse($this->certificate_image_factory->isMimeTypeSupported('application/pdf'));
    }

    public function testCertificateFileResponse(): void
    {
        $certificate = $this->createMock(Certificate::class);
        $certificate->method('tree')->willReturn($this->tree);
        $certificate->method('path')->willReturn('city/certificate.png');

        $response = $this->certificate_image_factory->certificateFileResponse($certificate, false);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame('image/png', $response->getHeaderLine('Content-Type'));
        self::assertEmpty($response->getHeaderLine('X-Image-Exception'));

        $watermark = new Watermark('Test', '#ff0000', 18);

        $response = $this->certificate_image_factory->certificateFileResponse($certificate, true, $watermark);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame('image/png', $response->getHeaderLine('Content-Type'));
        self::assertEmpty($response->getHeaderLine('X-Image-Exception'));

        $response = $this->certificate_image_factory->certificateFileResponse($certificate, true, null);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame('Watermark data not defined', $response->getHeaderLine('X-Image-Exception'));

        $certificate_invalid = $this->createMock(Certificate::class);
        $certificate_invalid->method('tree')->willReturn($this->tree);
        $certificate_invalid->method('path')->willReturn('city/certificate-invalid.png');

        $response = $this->certificate_image_factory->certificateFileResponse($certificate_invalid, true, $watermark);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertSame('image/svg+xml', $response->getHeaderLine('Content-Type'));

        $certificate_notimage = $this->createMock(Certificate::class);
        $certificate_notimage->method('tree')->willReturn($this->tree);
        $certificate_notimage->method('path')->willReturn('file.txt');

        $response = $this->certificate_image_factory->certificateFileResponse($certificate_notimage, true, $watermark);
        self::assertSame(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        self::assertNotEmpty($response->getHeaderLine('X-Image-Exception'));
    }

    public function testCertificateNeedsWatermark(): void
    {
        $certificate = $this->createMock(Certificate::class);
        $user = $this->createMock(UserInterface::class);

        self::assertTrue($this->certificate_image_factory->certificateNeedsWatermark($certificate, $user));
    }

    public function testMediaFileResponse(): void
    {
        $media_file = $this->createMock(MediaFile::class);

        self::expectException(BadMethodCallException::class);
        $this->certificate_image_factory->mediaFileResponse($media_file, false, false);
    }

    public function testMediaFileThumbnailResponse(): void
    {
        $media_file = $this->createMock(MediaFile::class);

        self::expectException(BadMethodCallException::class);
        $this->certificate_image_factory->mediaFileThumbnailResponse($media_file, 1, 1, '42', false);
    }

    public function testCreateWatermark(): void
    {
        $media_file = $this->createMock(MediaFile::class);

        self::expectException(BadMethodCallException::class);
        $this->certificate_image_factory->createWatermark(1, 1, $media_file);
    }

    public function testFileNeedsWatermark(): void
    {
        $media_file = $this->createMock(MediaFile::class);
        $user = $this->createMock(UserInterface::class);

        self::expectException(BadMethodCallException::class);
        $this->certificate_image_factory->fileNeedsWatermark($media_file, $user);
    }

    public function testThumbnailNeedsWatermark(): void
    {
        $media_file = $this->createMock(MediaFile::class);
        $user = $this->createMock(UserInterface::class);

        self::expectException(BadMethodCallException::class);
        $this->certificate_image_factory->thumbnailNeedsWatermark($media_file, $user);
    }
}

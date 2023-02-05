<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Factories;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\MediaFile;
use Fisharebest\Webtrees\Webtrees;
use Fisharebest\Webtrees\Contracts\ImageFactoryInterface;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Factories\ImageFactory;
use Intervention\Image\AbstractFont;
use Intervention\Image\Image;
use Intervention\Image\Exception\NotReadableException;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToReadFile;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Model\Watermark;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use Psr\Http\Message\ResponseInterface;
use BadMethodCallException;
use InvalidArgumentException;
use Throwable;

/**
 * Make a certificate image (from a certificate file).
 */
class CertificateImageFactory extends ImageFactory implements ImageFactoryInterface
{
    /**
     * @var CertificateFilesystemService $filesystem_service
     */
    private $filesystem_service;

    /**
     * Constructor for the Certificate Image Factory
     *
     * @param CertificateFilesystemService $filesystem_service
     */
    public function __construct(CertificateFilesystemService $filesystem_service)
    {
        $this->filesystem_service = $filesystem_service;
    }

    /**
     * Check is a file MIME type is supported by the system.
     *
     * @param string $mime
     * @return bool
     */
    public function isMimeTypeSupported(string $mime): bool
    {
        return array_key_exists($mime, self::SUPPORTED_FORMATS);
    }

    /**
     * Create a full-size version of a certificate.
     *
     * @param Certificate $certificate
     * @param bool $add_watermark
     * @param Watermark $watermark
     * @throws InvalidArgumentException
     * @return ResponseInterface
     */
    public function certificateFileResponse(
        Certificate $certificate,
        bool $add_watermark = false,
        Watermark $watermark = null
    ): ResponseInterface {
        $filesystem =  $this->filesystem_service->filesystem($certificate->tree());
        $filename   = $certificate->path();

        if (!$add_watermark) {
            return $this->fileResponse($filesystem, $filename, false);
        }

        try {
            $image = $this->imageManager()->make($filesystem->readStream($filename));
            $image = $this->autorotateImage($image);

            if ($watermark === null) {
                throw new InvalidArgumentException('Watermark data not defined');
            }

            $width = $image->width();
            $height = $image->height();

            $watermark->adjustSize($width);
            $watermark_x = (int) ceil($watermark->textLengthEstimate() * 1.5);
            $watermark_y = $watermark->size() * 12 + 1;

            $font_definition = function (AbstractFont $font) use ($watermark): void {
                $font->file(Webtrees::ROOT_DIR . 'resources/fonts/DejaVuSans.ttf');
                $font->color($watermark->color());
                $font->size($watermark->size());
                $font->valign('top');
                $font->align('center'); // Required for bug in Intervention / image
            };

            for ($i = min((int) ceil($width * 0.1), $watermark_x); $i < $width; $i += $watermark_x) {
                for ($j = min((int) ceil($height * 0.1), $watermark_y); $j < $height; $j += $watermark_y) {
                    $image = $image->text($watermark->text(), $i, $j, $font_definition);
                }
            }

            $format  = static::SUPPORTED_FORMATS[$image->mime()] ?? 'jpg';
            $quality = $this->extractImageQuality($image, static::GD_DEFAULT_IMAGE_QUALITY);
            $data    = (string) $image->encode($format, $quality);

            return $this->imageResponse($data, $image->mime(), '');
        } catch (NotReadableException $ex) {
            return $this->replacementImageResponse(pathinfo($filename, PATHINFO_EXTENSION))
            ->withHeader('X-Image-Exception', $ex->getMessage());
        } catch (FilesystemException | UnableToReadFile $ex) {
            return $this->replacementImageResponse((string) StatusCodeInterface::STATUS_NOT_FOUND);
        } catch (Throwable $ex) {
            return $this->replacementImageResponse((string) StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR)
            ->withHeader('X-Image-Exception', $ex->getMessage());
        }
    }

    /**
     * Does a full-sized certificate need a watermark?
     *
     * @param Certificate $certificate
     * @param UserInterface $user
     * @return bool
     */
    public function certificateNeedsWatermark(Certificate $certificate, UserInterface $user): bool
    {
        $tree = $certificate->tree();
        $watermark_level = (int) ($tree->getPreference('MAJ_CERTIF_SHOW_NO_WATERMARK', (string) Auth::PRIV_HIDE));

        return Auth::accessLevel($tree, $user) > $watermark_level;
    }

    /**
     * Neutralise the methods associated with MediaFile.
     */

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Factories\ImageFactory::mediaFileResponse()
     */
    public function mediaFileResponse(MediaFile $media_file, bool $add_watermark, bool $download): ResponseInterface
    {
        throw new BadMethodCallException("Invalid method for Certificates");
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Factories\ImageFactory::mediaFileThumbnailResponse()
     */
    public function mediaFileThumbnailResponse(
        MediaFile $media_file,
        int $width,
        int $height,
        string $fit,
        bool $add_watermark
    ): ResponseInterface {
        throw new BadMethodCallException("Invalid method for Certificates");
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Factories\ImageFactory::createWatermark()
     */
    public function createWatermark(int $width, int $height, MediaFile $media_file): Image
    {

        throw new BadMethodCallException("Invalid method for Certificates");
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Factories\ImageFactory::fileNeedsWatermark()
     */
    public function fileNeedsWatermark(MediaFile $media_file, UserInterface $user): bool
    {
        throw new BadMethodCallException("Invalid method for Certificates");
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Factories\ImageFactory::thumbnailNeedsWatermark()
     */
    public function thumbnailNeedsWatermark(MediaFile $media_file, UserInterface $user): bool
    {
        throw new BadMethodCallException("Invalid method for Certificates");
    }
}

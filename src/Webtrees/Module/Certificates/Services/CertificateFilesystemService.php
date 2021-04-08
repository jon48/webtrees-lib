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

namespace MyArtJaub\Webtrees\Module\Certificates\Services;

use Fisharebest\Flysystem\Adapter\ChrootAdapter;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToRetrieveMetadata;
use MyArtJaub\Webtrees\Module\Certificates\Factories\CertificateImageFactory;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;

/**
 * Service for accessing certificates on the file system..
 */
class CertificateFilesystemService
{
    /**
     * @var array<int,FilesystemOperator> $filesystem
     */
    private $filesystem = [];

    /**
     * Get the filesystem containing certificates for a tree.
     *
     * @param Tree $tree
     * @return FilesystemOperator
     */
    public function filesystem(Tree $tree): FilesystemOperator
    {
        if (!isset($this->filesystem[$tree->id()])) {
            $cert_dir = $tree->getPreference('MAJ_CERTIF_ROOTDIR', 'certificates/');
            $adapter   = new ChrootAdapter(Registry::filesystem()->data(), $cert_dir);

            $this->filesystem[$tree->id()] = new FileSystem($adapter);
        }
        return $this->filesystem[$tree->id()];
    }

    /**
     * Set the filesystem containing certificates for a tree.
     *
     * @param Tree $tree
     * @param FilesystemOperator $filesystem
     */
    public function setFilesystem(Tree $tree, FilesystemOperator $filesystem): void
    {
        $this->filesystem[$tree->id()] = $filesystem;
    }

    /**
     * Create the Certificate object defined by a path on the filesystem.
     *
     * @param Tree $tree
     * @param string $path
     * @return Certificate|NULL
     */
    public function certificate(Tree $tree, string $path): ?Certificate
    {
        $filesystem = $this->filesystem($tree);
        if ($filesystem->fileExists($path) && $this->isFileSupported($filesystem, $path)) {
            return new Certificate($tree, $path);
        }
        return null;
    }

    /**
     * Get the cities (first-level folder) available in a the filesystem.
     *
     * @param Tree $tree
     * @return string[]
     */
    public function cities(Tree $tree): array
    {
        return $this->filesystem($tree)
            ->listContents('')
            ->filter(function (StorageAttributes $attributes): bool {
                return $attributes->isDir();
            })->map(function (StorageAttributes $attributes): string {
                return $attributes->path();
            })->toArray();
    }

    /**
     * Get the certificates available for a city (first-level folder).
     *
     * @param Tree $tree
     * @param string $city
     * @return Collection<Certificate>
     */
    public function certificatesForCity(Tree $tree, string $city): Collection
    {
        $filesystem = $this->filesystem($tree);
        $certificate_paths = collect($filesystem
            ->listContents($city)
            ->filter(function (StorageAttributes $attributes) use ($filesystem): bool {
                return $attributes->isFile() && $this->isFileSupported($filesystem, $attributes->path());
            })->map(function (StorageAttributes $attributes): string {
                return $attributes->path();
            })->toArray());

        return $certificate_paths->map(function (string $path) use ($tree): Certificate {
            return new Certificate($tree, $path);
        });
    }

    /**
     * Get the certificates available for a city (first-level folder), containing a specified text.
     *
     * @param Tree $tree
     * @param string $city
     * @param string $contains
     * @return Collection<Certificate>
     */
    public function certificatesForCityContaining(Tree $tree, string $city, string $contains): Collection
    {
        $filesystem = $this->filesystem($tree);
        $certificate_paths = collect($filesystem
            ->listContents($city)
            ->filter(function (StorageAttributes $attributes) use ($filesystem, $contains): bool {
                return $attributes->isFile() && $this->isFileSupported($filesystem, $attributes->path())
                    && mb_stripos($attributes->path(), $contains) !== false;
            })->map(function (StorageAttributes $attributes): string {
                return $attributes->path();
            })->toArray());

        return $certificate_paths->map(function (string $path) use ($tree): Certificate {
            return new Certificate($tree, $path);
        });
    }

    /**
     * Check if a file on the filesystem is supported by the certificate module.
     *
     * @param FilesystemOperator $filesystem
     * @param string $path
     * @return bool
     */
    protected function isFileSupported(FilesystemOperator $filesystem, string $path): bool
    {
        try {
            $mime = $filesystem->mimeType($path);
            return Registry::cache()->array()->remember(
                'maj-certif-supportedfiles-' . $mime,
                function () use ($mime): bool {
                    return app(CertificateImageFactory::class)->isMimeTypeSupported($mime);
                }
            );
        } catch (UnableToRetrieveMetadata | FilesystemException $ex) {
        }
        return false;
    }
}

<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Common\GeoDispersion\Maps;

use Brick\Geo\IO\GeoJSONReader;
use Brick\Geo\IO\GeoJSON\FeatureCollection;
use League\Flysystem\FilesystemReader;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\MapDefinitionInterface;
use Throwable;

/**
 * GeoJson map defined by a single file on a file system
 *
 * @author Jonathan
 */
class SimpleFilesystemMap implements MapDefinitionInterface
{
    private string $id;
    private string $title;
    private string $path;
    private FilesystemReader $filesystem;

    /**
     * Constructor for SimpleFilesystemMap
     *
     * @param string $id
     * @param string $title
     * @param FilesystemReader $filesystem
     * @param string $path
     */
    public function __construct(string $id, string $title, FilesystemReader $filesystem, string $path)
    {
        $this->id = $id;
        $this->title = $title;
        $this->filesystem = $filesystem;
        $this->path = $path;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\MapDefinitionInterface::id()
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\MapDefinitionInterface::title()
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\MapDefinitionInterface::features()
     */
    public function features(): array
    {
        $reader = new GeoJSONReader();
        try {
            $feature_collection = $reader->read($this->filesystem->read($this->path));
            if ($feature_collection instanceof FeatureCollection) {
                return $feature_collection->getFeatures();
            }
        } catch (Throwable $ex) {
        }
        return [];
    }
}

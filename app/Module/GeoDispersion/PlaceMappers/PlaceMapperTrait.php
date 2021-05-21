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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers;

use MyArtJaub\Webtrees\Common\GeoDispersion\Config\NullPlaceMapperConfig;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface;

/**
 * Trait for implementation of the PlaceMapperInterface
 *
 * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface
 */
trait PlaceMapperTrait
{
    private ?PlaceMapperConfigInterface $config = null;

    /** @var array<string, mixed> $data */
    private array $data = [];

    /**
     * Implementation of PlaceMapperInterface::boot
     *
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::boot()
     */
    public function boot(): void
    {
    }

    /**
     * Implementation of PlaceMapperInterface::config
     *
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::config()
     *
     * @return PlaceMapperConfigInterface
     */
    public function config(): PlaceMapperConfigInterface
    {
        return $this->config ?? new NullPlaceMapperConfig();
    }

    /**
     * Implementation of PlaceMapperInterface::setConfig
     *
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::setConfig()
     *
     * @param PlaceMapperConfigInterface $config
     */
    public function setConfig(PlaceMapperConfigInterface $config): void
    {
        $this->config = $config;
    }

    /**
     * Implementation of PlaceMapperInterface::data
     *
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::data()
     *
     * @param string $key
     * @return NULL|mixed
     */
    public function data(string $key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Implementation of PlaceMapperInterface::setData
     *
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::setData()
     *
     * @param string $key
     * @param mixed|null $data
     */
    public function setData(string $key, $data): void
    {
        $this->data[$key] = $data;
    }
}

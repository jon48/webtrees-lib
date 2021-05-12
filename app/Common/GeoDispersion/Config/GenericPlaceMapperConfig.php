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

namespace MyArtJaub\Webtrees\Common\GeoDispersion\Config;

use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Generic Place Mapper configuration.
 * This configuration is based on an associate array holding the configuration.
 */
class GenericPlaceMapperConfig implements PlaceMapperConfigInterface
{
    private array $config = [];

    /**
     * Get the generic mapper's config
     *
     * @return array
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * Set the generic mapper's config
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface::get()
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface::has()
     */
    public function has(string $key): bool
    {
        return key_exists($key, $this->config);
    }

    /**
     * {@inheritDoc}
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return [
            'class'     =>  get_class($this),
            'config'    =>  $this->jsonSerializeConfig()
        ];
    }

    /**
     * Returns a representation of the mapper config compatible with Json serialisation
     *
     * @return mixed
     */
    public function jsonSerializeConfig()
    {
        return $this->config;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface::jsonDeserialize()
     *
     * @param mixed $config
     * @return $this
     */
    public function jsonDeserialize($config): self
    {
        if (is_string($config)) {
            return $this->jsonDeserialize(json_decode($config));
        }
        if (is_array($config)) {
            return $this->setConfig($config);
        }
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface::configContent()
     */
    public function configContent(ModuleInterface $module, Tree $tree): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface::withConfigUpdate()
     * @return $this
     */
    public function withConfigUpdate(ServerRequestInterface $request): self
    {
        return $this;
    }
}

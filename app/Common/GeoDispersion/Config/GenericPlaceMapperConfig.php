<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2026, Jonathan Jaubart
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
    /** @var array<string, mixed> $config */
    private array $config = [];

    /**
     * Get the generic mapper's config
     *
     * @return array<string, mixed>
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * Set the generic mapper's config
     *
     * @param array<string, mixed> $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;
        return $this;
    }

    #[\Override]
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    #[\Override]
    public function has(string $key): bool
    {
        return key_exists($key, $this->config);
    }

    #[\Override]
    #[\ReturnTypeWillChange]
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

    #[\Override]
    public function jsonDeserialize($config): self
    {
        if (is_string($config)) {
            return $this->jsonDeserialize((array) json_decode($config));
        }
        if (is_array($config)) {
            return $this->setConfig($config);
        }
        return $this;
    }

    #[\Override]
    public function configContent(ModuleInterface $module, Tree $tree): string
    {
        return '';
    }

    #[\Override]
    public function withConfigUpdate(ServerRequestInterface $request): self
    {
        return $this;
    }
}

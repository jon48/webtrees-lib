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
use JsonSerializable;

/**
 * Null Place Mapper configuration.
 * It does not contain any data, and can be used when no configuration is required.
 */
class NullPlaceMapperConfig implements PlaceMapperConfigInterface
{
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface::get()
     */
    public function get(string $key, $default = null)
    {
        return $default;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface::has()
     */
    public function has(string $key): bool
    {
        return false;
    }
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface::jsonDeserialize()
     */
    public function jsonDeserialize($config): self
    {
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return [];
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
     */
    public function withConfigUpdate(ServerRequestInterface $request): self
    {
        return $this;
    }


}
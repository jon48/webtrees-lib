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
use JsonSerializable;

/**
 * Null Place Mapper configuration.
 * It does not contain any data, and can be used when no configuration is required.
 */
class NullPlaceMapperConfig implements PlaceMapperConfigInterface
{
    #[\Override]
    public function get(string $key, $default = null)
    {
        return $default;
    }

    #[\Override]
    public function has(string $key): bool
    {
        return false;
    }

    #[\Override]
    public function jsonDeserialize($config): self
    {
        return $this;
    }

    #[\Override]
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [];
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

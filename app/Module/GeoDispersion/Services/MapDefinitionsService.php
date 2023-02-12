<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Services;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Services\ModuleService;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\MapDefinitionInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\ModuleMapDefinitionProviderInterface;

/**
 * Service for accessing map definitions .
 */
class MapDefinitionsService
{
    private ModuleService $module_service;

    /**
     * Constructor for MapDefinitionsService
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service)
    {
        $this->module_service = $module_service;
    }

    /**
     * Find a map definition by ID.
     *
     * @param string $id
     * @return MapDefinitionInterface|NULL
     */
    public function find(string $id): ?MapDefinitionInterface
    {
        return $this->all()->get($id);
    }

    /**
     * Get all map definitions available.
     *
     * {@internal The list is generated based on the modules exposing ModuleMapDefinitionProviderInterface,
     * and the result is cached}
     *
     * @param bool $include_disabled
     * @return Collection<string, MapDefinitionInterface>
     */
    public function all(bool $include_disabled = false): Collection
    {
        return Registry::cache()->array()->remember(
            'maj-geodisp-maps-all',
            function () use ($include_disabled): Collection {
                /** @var Collection<string, MapDefinitionInterface> $map_definitions */
                $map_definitions = $this->module_service
                    ->findByInterface(ModuleMapDefinitionProviderInterface::class, $include_disabled)
                    ->flatMap(fn(ModuleMapDefinitionProviderInterface $module): array => $module->listMapDefinition())
                    ->mapWithKeys(fn(MapDefinitionInterface $map) => [ $map->id() => $map ]);

                return $map_definitions;
            }
        );
    }
}

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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Services;

use Fisharebest\Webtrees\Services\ModuleService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\ModulePlaceMapperProviderInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;

/**
 * Service for accessing available place mappers .
 */
class PlaceMapperService
{
    private ModuleService $module_service;

    /**
     * Constructor for PlaceMapperService
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service)
    {
        $this->module_service = $module_service;
    }

    /**
     * Get all place mappers available.
     *
     * {@internal The list is generated based on the modules exposing ModulePlaceMapperProviderInterface}
     *
     * @param bool $include_disabled
     * @return Collection<PlaceMapperInterface>
     */
    public function all(bool $include_disabled = false): Collection
    {
        /** @var Collection<PlaceMapperInterface> $place_mappers */
        $place_mappers =  $this->module_service
            ->findByInterface(ModulePlaceMapperProviderInterface::class, $include_disabled)
            ->flatMap(fn(ModulePlaceMapperProviderInterface $module) => $module->listPlaceMappers())
            ->map(static function (string $mapper_class): ?PlaceMapperInterface {
                try {
                    $mapper = app($mapper_class);
                    return $mapper instanceof PlaceMapperInterface ? $mapper : null;
                } catch (BindingResolutionException $ex) {
                    return null;
                }
            })->filter();

        return $place_mappers;
    }
}

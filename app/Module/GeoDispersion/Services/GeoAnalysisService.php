<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Services;

use Fisharebest\Webtrees\Services\ModuleService;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\ModuleGeoAnalysisProviderInterface;

/**
 * Service for accessing available geographical dispersion analyses.
 */
class GeoAnalysisService
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
     * Get all available geographical dispersion analyses.
     *
     * {@internal The list is generated based on the modules exposing ModuleGeoAnalysisProviderInterface
     *
     * @param bool $include_disabled
     * @return Collection<int, GeoAnalysisInterface>
     */
    public function all(bool $include_disabled = false): Collection
    {
        /** @var Collection<int, GeoAnalysisInterface> $geoanalyses */
        $geoanalyses = $this->module_service
            ->findByInterface(ModuleGeoAnalysisProviderInterface::class, $include_disabled)
            ->flatMap(fn(ModuleGeoAnalysisProviderInterface $module): array => $module->listGeoAnalyses())
            ->map(static function (string $analysis_class): ?GeoAnalysisInterface {
                try {
                    $analysis = app($analysis_class);
                    return $analysis instanceof GeoAnalysisInterface ? $analysis : null;
                } catch (BindingResolutionException $ex) {
                    return null;
                }
            })->filter();

        return $geoanalyses;
    }
}

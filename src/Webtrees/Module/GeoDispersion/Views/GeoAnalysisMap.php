<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Views;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapColorsConfig;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use Spatie\Color\Rgb;

/**
 * A geographical dispersion analysis view displaying on a map for its global result.
 */
class GeoAnalysisMap extends AbstractGeoAnalysisView
{
    private ?MapColorsConfig $colors_config = null;

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::icon()
     */
    public function icon(ModuleInterface $module): string
    {
        return view($module->name() . '::icons/view-map');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::globalTabContent()
     */
    public function globalTabContent(
        ModuleInterface $module,
        GeoAnalysisResult $result,
        GeoAnalysisViewDataService $geoview_data_service,
        array $params
    ): string {
        $map_adapters = $geoview_data_service->mapAdapters($this);

        $adapter_result = null;
        foreach ($map_adapters as $map_adapter) {
            $adapter_result_tmp = $map_adapter->convert($result);
            $adapter_result = $adapter_result === null ?
                $adapter_result_tmp :
                $adapter_result->merge($adapter_result_tmp);
        }

        if ($adapter_result === null) {
            return view($module->name() . '::errors/tab-error', [
                'message'   =>  I18N::translate('The map could not be loaded.'),
            ]);
        }

        //phpcs:disable Generic.Files.LineLength.TooLong
        $basemap_provider = [
            'url'    => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'options' => [
                'attribution' => '<a href="https://www.openstreetmap.org/copyright">&copy; OpenStreetMap</a> contributors',
                'max_zoom'    => 19
            ]
        ];
        //phpcs:enable

        return view($module->name() . '::geoanalysisview-tab-glb-map', $params + [
            'result'            =>  $adapter_result->geoAnalysisResult(),
            'features'          =>  $adapter_result->features(),
            'colors'            =>  $this->colors(),
            'basemap_provider'  =>  $basemap_provider
        ]);
    }

    /**
     * Get the color scheme configuration for the map view
     *
     * @return MapColorsConfig
     */
    public function colors(): MapColorsConfig
    {
        return $this->colors_config ?? new MapColorsConfig(
            new Rgb(245, 245, 245),
            new Rgb(213, 213, 213),
            new Rgb(4, 147, 171),
            new Rgb(255, 102, 0)
        );
    }

    /**
     * Set the color scheme configuration for the map view
     *
     * @param MapColorsConfig $config
     * @return self
     */
    public function setColors(?MapColorsConfig $config): self
    {
        $this->colors_config = $config;
        return $this;
    }
}

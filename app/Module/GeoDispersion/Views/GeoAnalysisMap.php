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
use Fisharebest\Webtrees\Services\LeafletJsService;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapColorsConfig;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapAdapterDataService;
use Psr\Http\Message\ServerRequestInterface;
use Spatie\Color\Hex;
use Spatie\Color\Rgb;
use Spatie\Color\Exceptions\InvalidColorValue;

/**
 * A geographical dispersion analysis view displaying on a map for its global result.
 */
class GeoAnalysisMap extends AbstractGeoAnalysisView
{
    private ?MapColorsConfig $colors_config = null;

    public function type(): string
    {
        return I18N::translateContext('GEODISPERSION', 'Map');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::icon()
     */
    public function icon(ModuleInterface $module): string
    {
        return view($module->name() . '::icons/view-map', ['type' => $this->type()]);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::globalSettingsContent()
     */
    public function globalSettingsContent(ModuleInterface $module): string
    {
        return view($module->name() . '::admin/view-edit-map', [
            'module_name'   =>  $module->name(),
            'view'          =>  $this,
            'colors'        =>  $this->colors(),
            'map_adapters'  =>  app(MapAdapterDataService::class)->allForView($this, true)
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::withGlobalSettingsUpdate()
     * @return static
     */
    public function withGlobalSettingsUpdate(ServerRequestInterface $request): self
    {
        $params = (array) $request->getParsedBody();

        $default_color  = $params['view_map_color_default'] ?? '';
        $stroke_color   = $params['view_map_color_stroke'] ?? '';
        $maxvalue_color  = $params['view_map_color_maxvalue'] ?? '';
        $hover_color  = $params['view_map_color_hover'] ?? '';

        try {
            return $this->withColors(new MapColorsConfig(
                Hex::fromString($default_color),
                Hex::fromString($stroke_color),
                Hex::fromString($maxvalue_color),
                Hex::fromString($hover_color)
            ));
        } catch (InvalidColorValue $ex) {
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::globalTabContent()
     */
    public function globalTabContent(GeoDispersionModule $module, GeoAnalysisResult $result, array $params): string
    {
        $map_adapters = app(MapAdapterDataService::class)->allForView($this);

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

        return view($module->name() . '::geoanalysisview-tab-glb-map', $params + [
            'result'            =>  $adapter_result->geoAnalysisResult(),
            'features'          =>  $adapter_result->features(),
            'colors'            =>  $this->colors(),
            'leaflet_config'    =>  app(LeafletJsService::class)->config(),
            'js_script_url'     =>  $module->assetUrl('js/geodispersion.min.js')
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
     * Returns a map view with a new color scheme configuration
     *
     * @param MapColorsConfig $config
     * @return static
     */
    public function withColors(?MapColorsConfig $config): self
    {
        $new = clone $this;
        $new->colors_config = $config;
        return $new;
    }
}

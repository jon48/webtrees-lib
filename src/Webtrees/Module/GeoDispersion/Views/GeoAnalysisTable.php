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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Views;

use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;

class GeoAnalysisTable extends AbstractGeoAnalysisView
{
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::icon()
     */
    public function icon(ModuleInterface $module): string
    {
        return view($module->name() . '::icons/view-table');
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
        return view($module->name() . '::geoanalysisview-tab-glb-table', $params + [
            'result'    =>  $result
        ]);
    }
}

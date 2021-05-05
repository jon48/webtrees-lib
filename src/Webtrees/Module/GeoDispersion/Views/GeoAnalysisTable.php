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

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult;
use Psr\Http\Message\ServerRequestInterface;

class GeoAnalysisTable extends AbstractGeoAnalysisView
{
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::type()
     */
    public function type(): string
    {
        return I18N::translateContext('GEODISPERSION', 'Table');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::icon()
     */
    public function icon(ModuleInterface $module): string
    {
        return view($module->name() . '::icons/view-table', ['type' => $this->type()]);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::globalSettingsContent()
     */
    public function globalSettingsContent(ModuleInterface $module): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::withGlobalSettingsUpdate()
     * @return $this
     */
    public function withGlobalSettingsUpdate(ServerRequestInterface $request): self
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView::globalTabContent()
     */
    public function globalTabContent(ModuleInterface $module, GeoAnalysisResult $result, array $params): string
    {
        return view($module->name() . '::geoanalysisview-tab-glb-table', $params + [
            'result'    =>  $result
        ]);
    }
}

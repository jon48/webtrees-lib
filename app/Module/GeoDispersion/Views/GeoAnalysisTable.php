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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Views;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use Psr\Http\Message\ServerRequestInterface;

class GeoAnalysisTable extends AbstractGeoAnalysisView
{
    #[\Override]
    public function type(): string
    {
        return I18N::translateContext('GEODISPERSION', 'Table');
    }

    #[\Override]
    public function icon(ModuleInterface $module): string
    {
        return view($module->name() . '::icons/view-table', ['type' => $this->type()]);
    }

    #[\Override]
    public function globalSettingsContent(ModuleInterface $module): string
    {
        return '';
    }

    #[\Override]
    public function withGlobalSettingsUpdate(ServerRequestInterface $request): self
    {
        return $this;
    }

    #[\Override]
    public function globalTabContent(GeoDispersionModule $module, GeoAnalysisResult $result, array $params): string
    {
        return view($module->name() . '::geoanalysisview-tab-glb-table', $params + [
            'result'    =>  $result
        ]);
    }
}

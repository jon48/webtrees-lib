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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\GeoAnalyses;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Statistics\Service\CenturyService;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResults;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisDataService;

/**
 * Analyse the geographical dispersion of all individuals and families' events, detailed by century.
 */
class AllEventsByCenturyGeoAnalysis implements GeoAnalysisInterface
{
    private GeoAnalysisDataService $geoanalysis_data_service;
    private CenturyService $century_service;

    /**
     * Constructor for AllEventsByCenturyGeoAnalysis
     *
     * @param GeoAnalysisDataService $geoanalysis_data_service
     * @param CenturyService $century_service
     */
    public function __construct(GeoAnalysisDataService $geoanalysis_data_service, CenturyService $century_service)
    {
        $this->geoanalysis_data_service = $geoanalysis_data_service;
        $this->century_service = $century_service;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface::title()
     */
    public function title(): string
    {
        return I18N::translate('All events places by century');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface::itemsDescription()
     */
    public function itemsDescription(): callable
    {
        return fn(int $count): string => I18N::plural('event', 'events', $count);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface::results()
     */
    public function results(Tree $tree, int $depth): GeoAnalysisResults
    {
        $results = new GeoAnalysisResults();

        foreach ($this->geoanalysis_data_service->individualsAndFamilies($tree) as $record) {
            foreach ($record->facts([]) as $fact) {
                $place = new GeoAnalysisPlace($tree, $fact->place(), $depth);
                if ($place->isUnknown()) {
                    continue;
                }
                $results->addPlace($place);
                $date = $fact->date();
                if ($date->isOK()) {
                    $century = intdiv($date->gregorianYear(), 100);
                    $results->addPlaceInCategory(
                        I18N::translate('%s century', $this->century_service->centuryName($century)),
                        $century,
                        $place
                    );
                }
            }
        }

        return $results;
    }
}

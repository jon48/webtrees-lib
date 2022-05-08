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

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResults;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisDataService;

/**
 * Analyse the geographical dispersion of all individuals and families' events, detailed by type of event.
 */
class AllEventsByTypeGeoAnalysis implements GeoAnalysisInterface
{
    private GeoAnalysisDataService $geoanalysis_data_service;

    /**
     * Constructor for AllEventsByTypeGeoAnalysis
     *
     * @param GeoAnalysisDataService $geoanalysis_data_service
     */
    public function __construct(GeoAnalysisDataService $geoanalysis_data_service)
    {
        $this->geoanalysis_data_service = $geoanalysis_data_service;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface::title()
     */
    public function title(): string
    {
        return I18N::translate('All events places by event type');
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
                $results->addPlaceInCategory($fact->label(), 0, $place);
            }
        }

        return $results;
    }
}

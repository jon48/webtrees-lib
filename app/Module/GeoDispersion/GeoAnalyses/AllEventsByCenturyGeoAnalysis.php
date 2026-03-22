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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\GeoAnalyses;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResults;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisDataService;
use NumberFormatter;

/**
 * Analyse the geographical dispersion of all individuals and families' events, detailed by century.
 */
class AllEventsByCenturyGeoAnalysis implements GeoAnalysisInterface
{
    private GeoAnalysisDataService $geoanalysis_data_service;

    /**
     * Constructor for AllEventsByCenturyGeoAnalysis
     *
     * @param GeoAnalysisDataService $geoanalysis_data_service
     */
    public function __construct(GeoAnalysisDataService $geoanalysis_data_service)
    {
        $this->geoanalysis_data_service = $geoanalysis_data_service;
    }

    #[\Override]
    public function title(): string
    {
        return I18N::translate('All events places by century');
    }

    #[\Override]
    public function itemsDescription(): callable
    {
        return fn(int $count): string => I18N::plural('event', 'events', $count);
    }

    #[\Override]
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
                        I18N::translate('%s century', $this->centuryName($century)),
                        $century,
                        $place
                    );
                }
            }
        }

        return $results;
    }

    /**
     * Century name, English => 21st, Polish => XXI, etc.
     * Used to be a public service, now private in \Fisharebest\Webtrees\StatisticData
     */
    public function centuryName(int $century): string
    {
        if ($century < 0) {
            return I18N::translate('%s BCE', $this->centuryName(-$century));
        }

        $formatter = new \NumberFormatter('en-US', \NumberFormatter::ORDINAL);
        $centuryOrdinal = $formatter->format($century);

        if ($century > 21 || $centuryOrdinal === false) {
            return ($century - 1) . '01-' . $century . '00';
        }
        return I18N::translateContext('CENTURY', $centuryOrdinal);
    }
}

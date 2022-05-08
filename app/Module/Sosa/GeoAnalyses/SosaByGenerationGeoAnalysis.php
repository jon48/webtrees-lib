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

namespace MyArtJaub\Webtrees\Module\Sosa\GeoAnalyses;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResults;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;
use Generator;
use stdClass;

/**
 * Analyse the geographical dispersion of the ancestors, detailed by century.
 */
class SosaByGenerationGeoAnalysis implements GeoAnalysisInterface
{
    private SosaRecordsService $records_service;

    /**
     * Constructor for SosaByGenerationGeoAnalysis
     *
     * @param SosaRecordsService $records_service
     */
    public function __construct(SosaRecordsService $records_service)
    {
        $this->records_service = $records_service;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface::title()
     */
    public function title(): string
    {
        return I18N::translate('Sosa ancestors places by generation');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface::itemsDescription()
     */
    public function itemsDescription(): callable
    {
        return fn(int $count): string => I18N::plural('ancestor', 'ancestors', $count);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface::results()
     */
    public function results(Tree $tree, int $depth): GeoAnalysisResults
    {
        $results = new GeoAnalysisResults();

        $unique_ancestors = $this->records_service
            ->listAncestors($tree, Auth::user())
            ->uniqueStrict(fn(stdClass $item): string => $item->majs_i_id);

        foreach ($unique_ancestors as $item) {
            $ancestor = Registry::individualFactory()->make($item->majs_i_id, $tree);
            if ($ancestor === null || !$ancestor->canShow()) {
                continue;
            }
            $generation = $this->records_service->generation((int) $item->majs_sosa);
            $significantplace = new GeoAnalysisPlace($tree, null, $depth);
            foreach ($this->significantPlaces($ancestor) as $place) {
                $significantplace = new GeoAnalysisPlace($tree, $place, $depth, true);
                if ($significantplace->isKnown()) {
                    break;
                }
            }
            $results->addPlace($significantplace);
            $results->addPlaceInCategory(
                I18N::translate('Generation %s', I18N::number($generation)),
                $generation,
                $significantplace
            );
        }

        return $results;
    }

    /**
     * Returns significant places in order of priority for an individual
     *
     * @param Individual $individual
     * @return Generator<\Fisharebest\Webtrees\Place>
     */
    protected function significantPlaces(Individual $individual): Generator
    {
        yield $individual->getBirthPlace();

        /** @var \Fisharebest\Webtrees\Fact $fact */
        foreach ($individual->facts(['RESI']) as $fact) {
            yield $fact->place();
        }

        yield $individual->getDeathPlace();

        /** @var \Fisharebest\Webtrees\Family $family */
        foreach ($individual->childFamilies() as $family) {
            foreach ($family->facts(['RESI']) as $fact) {
                yield $fact->place();
            }
        }

        /** @var \Fisharebest\Webtrees\Family $family */
        foreach ($individual->spouseFamilies() as $family) {
            foreach ($family->facts(['RESI']) as $fact) {
                yield $fact->place();
            }
        }
    }
}

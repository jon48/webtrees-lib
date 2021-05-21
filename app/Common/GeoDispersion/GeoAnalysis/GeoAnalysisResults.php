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

namespace MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis;

use Fisharebest\Webtrees\I18N;
use Illuminate\Support\Collection;

/**
 * Aggregated global and detailed results of a geographical dispersion analysis
 *
 */
class GeoAnalysisResults
{
    private GeoAnalysisResult $global;

    /**
     * @var Collection<string, GeoAnalysisResult> $detailed
     */
    private Collection $detailed;

    /**
     * Constructor for GeoAnalysisResults
     */
    public function __construct()
    {
        $this->global = new GeoAnalysisResult('Global', 0);
        $this->detailed = new Collection();
    }

    /**
     * Global result of the geographical analysis
     *
     * @return GeoAnalysisResult
     */
    public function global(): GeoAnalysisResult
    {
        return $this->global;
    }

    /**
     * List of results by category of the geographical analysis
     *
     * @return Collection<string, GeoAnalysisResult>
     */
    public function detailed(): Collection
    {
        return $this->detailed;
    }

    /**
     * List of results by category of the geographical analysis.
     * The list is sorted first by the category order, then by the category description
     *
     * @return Collection<string, GeoAnalysisResult>
     */
    public function sortedDetailed(): Collection
    {
        return $this->detailed->sortBy([
            fn(GeoAnalysisResult $a, GeoAnalysisResult $b): int => $a->order() <=> $b->order(),
            fn(GeoAnalysisResult $a, GeoAnalysisResult $b): int =>
                I18N::comparator()($a->description(), $b->description())
        ]);
    }

    /**
     * Add a GeoAnalysis Place to the global result
     *
     * @param GeoAnalysisPlace $place
     */
    public function addPlace(GeoAnalysisPlace $place): void
    {
        $this->global()->addPlace($place);
    }

    /**
     * Add a new category to the list of results, if it does not exist yet
     *
     * @param string $description
     * @param int $order
     */
    public function addCategory(string $description, int $order): void
    {
        if (!$this->detailed->has($description)) {
            $this->detailed->put($description, new GeoAnalysisResult($description, $order));
        }
    }

    /**
     * Add a GeoAnalysis Place to a category result, if the category exist.
     *
     * @param string $category_name
     * @param GeoAnalysisPlace $place
     */
    public function addPlaceInCreatedCategory(string $category_name, GeoAnalysisPlace $place): void
    {
        if ($this->detailed->has($category_name)) {
            $this->detailed->get($category_name)->addPlace($place);
        }
    }

    /**
     * Add a GeoAnalysis Place to a category result, after creating the category if it does not exist.
     *
     * @param string $category_name
     * @param GeoAnalysisPlace $place
     */
    public function addPlaceInCategory(string $category_name, int $category_order, GeoAnalysisPlace $place): void
    {
        if (!$this->detailed->has($category_name)) {
            $this->addCategory($category_name, $category_order);
        }
        $this->addPlaceInCreatedCategory($category_name, $place);
    }
}

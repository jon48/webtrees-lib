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

use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Abstract class for Geographical dispersion analysis Views
 */
abstract class AbstractGeoAnalysisView
{
    private int $id;
    private Tree $tree;
    private bool $enabled;
    private string $description;
    private GeoAnalysisInterface $geoanalysis;
    private int $depth;
    private int $detailed_top_places;
    private bool $use_flags;

    /**
     * Constructor for AbstractGeoAnalysisView
     *
     * @param int $id
     * @param Tree $tree
     * @param bool $enabled
     * @param string $description
     * @param GeoAnalysisInterface $geoanalysis
     * @param int $depth
     * @param int $detailed_top_places
     * @param bool $use_flags
     */
    final public function __construct(
        int $id,
        Tree $tree,
        bool $enabled,
        string $description,
        GeoAnalysisInterface $geoanalysis,
        int $depth,
        int $detailed_top_places = 0,
        bool $use_flags = false
    ) {
        $this->id = $id;
        $this->tree = $tree;
        $this->enabled = $enabled;
        $this->description = $description;
        $this->geoanalysis = $geoanalysis;
        $this->depth = $depth;
        $this->detailed_top_places = $detailed_top_places;
        $this->use_flags = $use_flags;
    }

    /**
     * Create a copy of the view with a new ID.
     *
     * @param int $id
     * @return static
     */
    public function withId(int $id): self
    {
        $new = clone $this;
        $new->id = $id;
        return $new;
    }

    /**
     * Create a copy of the view with new properties.
     *
     * @param bool $enabled
     * @param string $description
     * @param GeoAnalysisInterface $geoanalysis
     * @param int $depth
     * @param int $detailed_top_places
     * @param bool $use_flags
     * @return static
     */
    public function with(
        bool $enabled,
        string $description,
        GeoAnalysisInterface $geoanalysis,
        int $depth,
        int $detailed_top_places = 0,
        bool $use_flags = false
    ): self {
        $new = clone $this;
        $new->enabled = $enabled;
        $new->description = $description;
        $new->geoanalysis = $geoanalysis;
        $new->depth = $depth;
        $new->detailed_top_places = $detailed_top_places;
        $new->use_flags = $use_flags;
        return $new;
    }

    /**
     * Get the view ID
     *
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * Get the view type for display
     *
     * @return string
     */
    abstract public function type(): string;

    /**
     * Get the icon for the view type
     *
     * @param ModuleInterface $module
     * @return string
     */
    abstract public function icon(ModuleInterface $module): string;

    /**
     * Return the content of the global settings section of the config page
     *
     * @param ModuleInterface $module
     * @return string
     */
    abstract public function globalSettingsContent(ModuleInterface $module): string;

    /**
     * Return a view with global settings updated according to the view rules
     *
     * @param ServerRequestInterface $request
     * @return static
     */
    abstract public function withGlobalSettingsUpdate(ServerRequestInterface $request): self;

    /**
     * Returns the content of the view global tab
     *
     * @param ModuleInterface $module
     * @param GeoAnalysisResult $result
     * @param array $params
     * @return string
     */
    abstract public function globalTabContent(
        ModuleInterface $module,
        GeoAnalysisResult $result,
        array $params
    ): string;

    /**
     * Returns the content of the view detailed tab
     *
     * @param ModuleInterface $module
     * @param Collection $results
     * @param array $params
     * @return string
     */
    public function detailedTabContent(ModuleInterface $module, Collection $results, array $params): string
    {
        return view($module->name() . '::geoanalysisview-tab-detailed', $params + [ 'results'   =>  $results ]);
    }

    /**
     * Get the tree to which the view belongs
     *
     * @return Tree
     */
    public function tree(): Tree
    {
        return $this->tree;
    }

    /**
     * Get the description of the view
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * Get whether the view is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get the geographical dispersion analysis for the view
     *
     * @return GeoAnalysisInterface
     */
    public function analysis(): GeoAnalysisInterface
    {
        return $this->geoanalysis;
    }

    /**
     * Get the place hierarchy depth for the view
     *
     * @return int
     */
    public function placesDepth(): int
    {
        return $this->depth;
    }

    /**
     * Get the number of places to display in the detailed tab
     *
     * @return int
     */
    public function numberTopPlaces(): int
    {
        return $this->detailed_top_places;
    }

    /**
     * Get whether flags should be used in the detailed tab
     *
     * @return bool
     */
    public function useFlags(): bool
    {
        return $this->use_flags;
    }
}

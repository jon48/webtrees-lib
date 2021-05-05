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

use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Tree;

/**
 * Place resulting from a geographical analysis.
 * The place can be in one of several statuses:
 *  - Unknown : the analysis could not find any place for an analysed item, and that information needs to be captured
 *  - Known: the analysis has identify a place for an analysed item
 *      - Found: the known place is within the scope of the analysis view
 *      - Invalid: the known place does not match the required depth of the analysis
 *      - Excluded: the known place is not within the scope of the analysis view
 */
class GeoAnalysisPlace
{
    /**
     * The default place name for invalid places
     * @var string INVALID_PLACE
     */
    private const INVALID_PLACE = '##INVALID##';
    
    private Place $place;
    private int $depth;
    private bool $strict_depth;
    private bool $is_excluded;
    
    /**
     * Constructor for GeoAnalysisPlace
     * 
     * @param Tree $tree Default tree
     * @param Place|null $place Place resulting from the analysis
     * @param int $depth Place hierarchy depth defined by the geographical analysis view
     * @param bool $strict_depth Checks whether places with a lower depth than defined should be flagged as invalid
     */
    public function __construct(Tree $tree, ?Place $place, int $depth, bool $strict_depth = false)
    {
        $this->depth = $depth;
        $this->strict_depth = $strict_depth;
        $this->place = $this->extractPlace($place, $depth, $strict_depth) ?? new Place('', $tree);
        $this->is_excluded = false;
    }
    
    /**
     * Process the provided Place to determine its status for further usage
     * 
     * @param Place|null $place
     * @param int $depth
     * @param bool $strict_depth
     * @return Place|NULL
     */
    private function extractPlace(?Place $place, int $depth, bool $strict_depth): ?Place
    {
        if($place === null) return null;
        if(mb_strlen($place->gedcomName()) === 0) return null;
        $parts = $place->lastParts($depth);
        if($strict_depth && $parts->count() !== $depth) return new Place(self::INVALID_PLACE, $place->tree());
        return new Place($parts->implode(', '), $place->tree());
    }
    
    /**
     * Get the GeoAnalysis Place key
     * 
     * @return string
     */
    public function key(): string
    {
        return $this->place->gedcomName();
    }
    
    /**
     * Get the underlying Place object
     * 
     * @return Place
     */
    public function place(): Place
    {
        return $this->place;
    }
    
    /**
     * Check if the GeoAnalysis Place is in the Known status
     * 
     * @return bool
     */
    public function isKnown(): bool
    {
        return !$this->isUnknown();
    }
    
    /**
     * Check if the GeoAnalysis Place is in the Unknown status
     * 
     * @return bool
     */
    public function isUnknown(): bool
    {
        return mb_strlen($this->place->gedcomName()) === 0;
    }
    
    /**
     * Check if the GeoAnalysis Place is in the Invalid status
     * 
     * @return bool
     */
    public function isInvalid(): bool
    {
        return $this->place->gedcomName() === self::INVALID_PLACE;
    }
    
    /**
     * Check if the GeoAnalysis Place is in the Excluded status
     * 
     * @return bool
     */
    public function isExcluded(): bool
    {
        return $this->isUnknown() || $this->isInvalid() || $this->is_excluded;
    }
    
    /**
     * Set the GeoAnalysis Place status to Found, if the parameter is true
     * 
     * @param bool $include
     * @return $this
     */
    public function include(bool $include = true): self
    {
        $this->is_excluded = !$include;
        return $this;
    }
    
    /**
     * Set the GeoAnalysis Place status to Excluded, if the parameter is true
     * 
     * @param bool $exclude
     * @return $this
     */
    public function exclude(bool $exclude = true): self
    {
        $this->is_excluded = $exclude;
        return $this;
    }
}


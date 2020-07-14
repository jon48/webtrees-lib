<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\PatronymicLineage\Model;

use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Place;
use Illuminate\Support\Collection;

/**
 * Derived class from the LineageNode, indicating a Root Lineage node
 */
class LineageRootNode extends LineageNode
{
    
    /**
     * @var Collection $places Places for the lineage node
     */
    private $places;
   
    /**
     * @var int $nb_children Number of node childs
     */
    private $nb_children;
  
    /**
     * Constructor for LineageRootNode
     *
     * @param Individual|null $node_indi
     */
    public function __construct(?Individual $node_indi = null)
    {
        parent::__construct($node_indi, $this);
        $this->places = new Collection();
        $this->nb_children = 0;
    }
   
    /**
     * Adds a place to the list of lineage's place
     *
     * @param Place $place
     */
    public function addPlace(Place $place): void
    {
        $place_name = $place->gedcomName();
        if (mb_strlen($place_name) > 0) {
            $this->places->put($place_name, $this->places->get($place_name, 0) + 1);
        }
    }
    
    /**
     * Returns the number of child nodes.
     * This number is more to be used as indication rather than an accurate one.
     *
     * @return int
     */
    public function numberChildNodes(): int
    {
        return $this->nb_children;
    }
   
    /**
     * Increments the number of child nodes by one
     *
     */
    public function incrementChildNodes(): void
    {
        $this->nb_children++;
    }
   
    /**
     * Returns the list of place for the lineage
     *
     * @return Collection
     */
    public function places(): Collection
    {
        return $this->places;
    }
}

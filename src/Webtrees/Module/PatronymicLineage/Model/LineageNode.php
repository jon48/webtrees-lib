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

use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Individual;
use Illuminate\Support\Collection;
use stdClass;

/**
 * Node of the lineage tree, holding data about individuals forming it, and their descendency.
 */
class LineageNode
{
  
    /**
     * @var Collection $linked_fams Spouse families linked to the node
     */
    private $linked_fams;
  
    /**
     * @var ?Individual $node_indi Reference individual for the node
     */
    private $node_indi;
   
    /**
     * @var LineageRootNode $root_node Root node of the lineage
     */
    private $root_node;
   
    /**
     * @var ?string $alt_surname Linked surname, used to link to another lineage
     */
    private $alt_surname;
 
    /**
     * Constructor for Lineage node
     *
     * @param Individual $node_indi Main individual
     * @param LineageRootNode $root_node Node of the lineage root
     * @param null|string $alt_surname Follow-up surname
     */
    public function __construct(?Individual $node_indi = null, LineageRootNode $root_node, $alt_surname = null)
    {
        $this->node_indi = $node_indi;
        $this->root_node = $root_node;
        $this->alt_surname = $alt_surname;
        $this->linked_fams = new Collection();
    }
  
    /**
     * Add a spouse family to the node
     *
     * @param Family $fams
     * @return stdClass
     */
    public function addFamily(Family $fams): object
    {
        if (!$this->linked_fams->has($fams->xref())) {
            $this->linked_fams->put($fams->xref(), (object) [
                'family'   =>  $fams,
                'children' =>  new Collection()
            ]);
        }
        return $this->linked_fams->get($fams->xref());
    }
    
    /**
     * Add a child LineageNode to the node
     *
     * @param Family $fams
     * @param LineageNode $child
     */
    public function addChild(Family $fams, LineageNode $child = null): void
    {
        $this->addFamily($fams)->children->push($child);
        $this->root_node->incrementChildNodes();
    }
   
    /**
     * Returns the node individual
     *
     * @return Individual|NULL
     */
    public function individual(): ?Individual
    {
        return $this->node_indi;
    }
   
    /**
     * Returns the lineage root node individual
     *
     * @return LineageRootNode
     */
    public function rootNode(): LineageRootNode
    {
        return $this->root_node;
    }
    
    /**
     * Returns the spouse families linked to the node
     *
     * @return Collection
     */
    public function families(): Collection
    {
        return $this->linked_fams;
    }
  
    /**
     * Returns the follow-up surname
     *
     * @return string
     */
    public function followUpSurname(): string
    {
        return $this->alt_surname ?? '';
    }
    
    /**
     * Indicates whether the node has a follow up surname
     *
     * @return boolean
     */
    public function hasFollowUpSurname(): bool
    {
        return mb_strlen($this->followUpSurname()) > 0 ;
    }
}

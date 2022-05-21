<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\PatronymicLineage\Model;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Module\PatronymicLineage\PatronymicLineageModule;

/**
 * Build the patronymic lineage for a surname
 */
class LineageBuilder
{
    private string $surname;
    private Tree $tree;
    private PatronymicLineageModule $patrolineage_module;

    /**
     * @var Collection<string, bool> $used_indis Individuals already processed
     */
    private Collection $used_indis;

    /**
     * Constructor for Lineage Builder
     *
     * @param string $surname Reference surname
     * @param Tree $tree Gedcom tree
     */
    public function __construct(string $surname, Tree $tree, PatronymicLineageModule $patrolineage_module)
    {
        $this->surname = $surname;
        $this->tree = $tree;
        $this->patrolineage_module = $patrolineage_module;
        $this->used_indis = new Collection();
    }

    /**
     * Build all patronymic lineages for the reference surname.
     *
     * @return Collection<LineageRootNode>|NULL List of root patronymic lineages
     */
    public function buildLineages(): ?Collection
    {
        $indis = $this->patrolineage_module->individuals(
            $this->tree,
            $this->surname,
            '',
            '',
            false,
            false,
            I18N::locale()
        );
        //Warning - the individuals method returns a clone of individuals objects. Cannot be used for object equality
        if (count($indis) === 0) {
            return null;
        }

        $root_lineages = new Collection();

        foreach ($indis as $indi) {
            /** @var Individual $indi */
            if ($this->used_indis->get($indi->xref(), false) === false) {
                $indi_first = $this->getLineageRootIndividual($indi);
                if ($indi_first !== null) {
                    // The root lineage needs to be recreated from the Factory, to retrieve the proper object
                    $indi_first = Registry::individualFactory()->make($indi_first->xref(), $this->tree);
                }
                if ($indi_first === null) {
                    continue;
                }
                $this->used_indis->put($indi_first->xref(), true);
                if ($indi_first->canShow()) {
                    //Check if the root individual has brothers and sisters, without parents
                    $indi_first_child_family = $indi_first->childFamilies()->first();
                    if ($indi_first_child_family !== null) {
                        $root_node = new LineageRootNode(null);
                        $root_node->addFamily($indi_first_child_family);
                    } else {
                        $root_node = new LineageRootNode($indi_first);
                    }
                    $root_node = $this->buildLineage($root_node);
                    $root_lineages->add($root_node);
                }
            }
        }

        return $root_lineages->sort(function (LineageRootNode $a, LineageRootNode $b) {
            if ($a->numberChildNodes() === $b->numberChildNodes()) {
                return 0;
            }
            return ($a->numberChildNodes() > $b->numberChildNodes()) ? -1 : 1;
        })->values();
    }

    /**
     * Retrieve the root individual, from any individual, by recursion.
     * The Root individual is the individual without a father, or without a mother holding the same name.
     *
     * @param Individual $indi
     * @return Individual|NULL Root individual
     */
    private function getLineageRootIndividual(Individual $indi): ?Individual
    {
        $child_families = $indi->childFamilies();
        if ($this->used_indis->get($indi->xref(), false) !== false) {
            return null;
        }

        foreach ($child_families as $child_family) {
            /** @var Family $child_family */
            $child_family->husband();
            if (($husb = $child_family->husband()) !== null) {
                if ($husb->isPendingAddition() && $husb->privatizeGedcom(Auth::PRIV_HIDE) === '') {
                    return $indi;
                }
                return $this->getLineageRootIndividual($husb);
            } elseif (($wife = $child_family->wife()) !== null) {
                if (!($wife->isPendingAddition() && $wife->privatizeGedcom(Auth::PRIV_HIDE) === '')) {
                    $indi_surname = $indi->getAllNames()[$indi->getPrimaryName()]['surname'];
                    $wife_surname = $wife->getAllNames()[$wife->getPrimaryName()]['surname'];
                    if (
                        $indi->canShowName()
                        && $wife->canShowName()
                        && I18N::comparator()($indi_surname, $wife_surname) === 0
                    ) {
                            return $this->getLineageRootIndividual($wife);
                    }
                }
                return $indi;
            }
        }
        return $indi;
    }

    /**
     * Computes descendent Lineage from a node.
     * Uses recursion to build the lineage tree
     *
     * @param LineageNode $node
     * @return LineageNode Computed lineage
     */
    private function buildLineage(LineageNode $node): LineageNode
    {
        $indi_surname = '';

        $indi_node = $node->individual();
        if ($indi_node !== null) {
            if ($node->families()->count() === 0) {
                foreach ($indi_node->spouseFamilies() as $spouse_family) {
                    $node->addFamily($spouse_family);
                }
            }

            $indi_surname = $indi_node->getAllNames()[$indi_node->getPrimaryName()]['surname'] ?? '';
            $node->rootNode()->addPlace($indi_node->getBirthPlace());

            //Tag the individual as used
            $this->used_indis->put($indi_node->xref(), true);
        }

        foreach ($node->families() as $family_node) {
            /** @var Family $spouse_family */
            $spouse_family = $family_node->family;
            $spouse_surname = '';
            $spouse = null;
            if (
                $indi_node !== null &&
                ($spouse = $spouse_family->spouse($indi_node)) !== null && $spouse->canShowName()
            ) {
                $spouse_surname = $spouse->getAllNames()[$spouse->getPrimaryName()]['surname'] ?? '';
            }

            $nb_children = $nb_natural = 0;

            foreach ($spouse_family->children() as $child) {
                if (!($child->isPendingAddition() && $child->privatizeGedcom(Auth::PRIV_HIDE) === '')) {
                    $child_surname = $child->getAllNames()[$child->getPrimaryName()]['surname'] ?? '';

                    $nb_children++;
                    if ($indi_node !== null && $indi_node->sex() === 'F') { //If the root individual is the mother
                        //Print only lineages of children with the same surname as their mother
                        //(supposing they are natural children)
                        /** @psalm-suppress RedundantCondition */
                        if (
                            $spouse === null ||
                            ($spouse_surname !== '' && I18N::comparator()($child_surname, $spouse_surname) != 0)
                        ) {
                            if (I18N::comparator()($child_surname, $indi_surname) === 0) {
                                $nb_natural++;
                                $node_child = new LineageNode($child, $node->rootNode());
                                $node_child = $this->buildLineage($node_child);
                                $node->addChild($spouse_family, $node_child);
                            }
                        }
                    } else { //If the root individual is the father
                        $nb_natural++;
                        //Print if the children does not bear the same name as his mother
                        //(and different from his father)
                        if (
                            mb_strlen($child_surname) === 0 ||
                            mb_strlen($indi_surname) === 0 || mb_strlen($spouse_surname) === 0 ||
                            I18N::comparator()($child_surname, $indi_surname) === 0 ||
                            I18N::comparator()($child_surname, $spouse_surname) != 0
                        ) {
                            $node_child = new LineageNode($child, $node->rootNode());
                            $node_child = $this->buildLineage($node_child);
                        } else {
                            $node_child = new LineageNode($child, $node->rootNode(), $child_surname);
                        }
                        $node->addChild($spouse_family, $node_child);
                    }
                }
            }

            //Do not print other children
            if (($nb_children - $nb_natural) > 0) {
                $node->addChild($spouse_family, null);
            }
        }

        return $node;
    }
}

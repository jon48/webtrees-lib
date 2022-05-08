<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Sosa\Data;

use Fisharebest\Webtrees\Individual;

/**
 * Data class for Missing Ancestors datatable row
 */
class MissingAncestor
{
    /**
     * @var Individual $individual
     */
    private $individual;

    /**
     * @var int $sosa
     */
    private $sosa;

    /**
     * @var bool $missing_father
     */
    private $missing_father;

    /**
     * @var bool $missing_mother
     */
    private $missing_mother;

    /**
     * Constructor for MissingAncestor data class
     *
     * @param Individual $ancestor
     * @param int $sosa
     * @param bool $missing_father
     * @param bool $missing_mother
     */
    public function __construct(Individual $ancestor, int $sosa, bool $missing_father, bool $missing_mother)
    {
        $this->individual = $ancestor;
        $this->sosa = $sosa;
        $this->missing_father = $missing_father;
        $this->missing_mother = $missing_mother;
    }

    /**
     * Reference individual of the row
     *
     * @return Individual
     */
    public function individual(): Individual
    {
        return $this->individual;
    }

    /**
     * Minimum sosa of the reference individual
     *
     * @return int
     */
    public function sosa(): int
    {
        return $this->sosa;
    }

    /**
     * Indicate whether the reference individual does not have a father
     *
     * @return bool
     */
    public function isFatherMissing(): bool
    {
        return $this->missing_father;
    }

    /**
     * Indicate whether the reference individual does not have a mother
     *
     * @return bool
     */
    public function isMotherMissing(): bool
    {
        return $this->missing_mother;
    }
}

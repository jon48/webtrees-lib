<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Sosa\Services;

use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Contracts\UserInterface;

/**
 * Service for Sosa ancestors calculations
 */
class SosaCalculatorService
{
    /**
     * Maximium size for the temporary Sosa table
     * @var int TMP_SOSA_TABLE_LIMIT
     */
    private const TMP_SOSA_TABLE_LIMIT = 1000;

    /**
     * @var SosaRecordsService $sosa_records_service
     */
    private $sosa_records_service;

    /**
     * Reference user
     * @var UserInterface $user
     */
    private $user;

    /**
     * Reference tree
     * @var Tree $tree
     */
    private $tree;

    /**
     * Temporary Sosa table, used during construction
     * @var array<array<string,mixed>> $tmp_sosa_table
     */
    private $tmp_sosa_table;

    /**
     * Maximum number of generations to calculate
     * @var int $max_generations
     */
    private $max_generations;

    /**
     * Constructor for the Sosa Calculator
     *
     * @param SosaRecordsService $sosa_records_service
     * @param Tree $tree
     * @param UserInterface $user
     */
    public function __construct(SosaRecordsService $sosa_records_service, Tree $tree, UserInterface $user)
    {
        $this->sosa_records_service = $sosa_records_service;
        $this->tree = $tree;
        $this->user = $user;
        $this->tmp_sosa_table = array();
        $max_gen_setting = $tree->getUserPreference($user, 'MAJ_SOSA_MAX_GEN');
        $this->max_generations = is_numeric($max_gen_setting) ?
            (int) $max_gen_setting :
            $this->sosa_records_service->maxSystemGenerations();
    }

    /**
     * Compute all Sosa ancestors from the user's root individual.
     *
     * @return bool Result of the computation
     */
    public function computeAll(): bool
    {
        $root_id = $this->tree->getUserPreference($this->user, 'MAJ_SOSA_ROOT_ID');
        if (($indi = Registry::individualFactory()->make($root_id, $this->tree)) !== null) {
            $this->sosa_records_service->deleteAll($this->tree, $this->user);
            $this->addNode($indi, 1);
            $this->flushTmpSosaTable(true);
            return true;
        }
        return false;
    }

    /**
     * Compute all Sosa Ancestors from a specified Individual
     *
     * @param Individual $indi
     * @return bool
     */
    public function computeFromIndividual(Individual $indi): bool
    {
        $current_sosas = $this->sosa_records_service->sosaNumbers($this->tree, $this->user, $indi);
        foreach ($current_sosas->keys() as $sosa) {
            $this->sosa_records_service->deleteAncestorsFrom($this->tree, $this->user, $sosa);
            $this->addNode($indi, $sosa);
        }
        $this->flushTmpSosaTable(true);
        return true;
    }

    /**
     * Recursive method to add individual to the Sosa table, and flush it regularly
     *
     * @param Individual $indi Individual to add
     * @param int $sosa Individual's sosa
     */
    private function addNode(Individual $indi, int $sosa): void
    {
        $birth_year = $indi->getBirthDate()->gregorianYear();
        $birth_year_est = $birth_year === 0 ? $indi->getEstimatedBirthDate()->gregorianYear() : $birth_year;

        $death_year = $indi->getDeathDate()->gregorianYear();
        $death_year_est = $death_year === 0 ? $indi->getEstimatedDeathDate()->gregorianYear() : $death_year;

        $this->tmp_sosa_table[] = [
            'indi' => $indi->xref(),
            'sosa' => $sosa,
            'birth_year' => $birth_year === 0 ? null : $birth_year,
            'birth_year_est' => $birth_year_est === 0 ? null : $birth_year_est,
            'death_year' => $death_year === 0 ? null : $death_year,
            'death_year_est' => $death_year_est === 0 ? null : $death_year_est
        ];

        $this->flushTmpSosaTable();

        if (
            ($fam = $indi->childFamilies()->first()) !== null
            && $this->sosa_records_service->generation($sosa) < $this->max_generations
        ) {
            /** @var \Fisharebest\Webtrees\Family $fam */
            if (($husb = $fam->husband()) !== null) {
                $this->addNode($husb, 2 * $sosa);
            }
            if (($wife = $fam->wife()) !== null) {
                $this->addNode($wife, 2 * $sosa + 1);
            }
        }
    }

    /**
     * Write sosas in the table, if the number of items is superior to the limit, or if forced.
     *
     * @param bool $force Should the flush be forced
     */
    private function flushTmpSosaTable($force = false): void
    {
        if ($force || count($this->tmp_sosa_table) >= self::TMP_SOSA_TABLE_LIMIT) {
            $this->sosa_records_service->insertOrUpdate($this->tree, $this->user, $this->tmp_sosa_table);
            $this->tmp_sosa_table = array();
        }
    }
}

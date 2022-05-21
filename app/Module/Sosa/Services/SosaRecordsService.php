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

use Brick\Math\BigInteger;
use Brick\Math\RoundingMode;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * Service for CRUD operations on Sosa records
 */
class SosaRecordsService
{
    private ?int $max_system_generations = null;

    /**
     * Maximum number of generation the system is able to hold.
     * This is based on the size of the bigint SQL type (2^63) and the maximum PHP integer type
     *
     * @return int
     */
    public function maxSystemGenerations(): int
    {
        if ($this->max_system_generations === null) {
            $this->max_system_generations = min(63, $this->generation(PHP_INT_MAX));
        }
        return $this->max_system_generations;
    }

    /**
     * Calculate the generation of a sosa
     * Sosa 1 is of generation 1.
     *
     * @param int $sosa
     * @return int
     */
    public function generation(int $sosa): int
    {
        return BigInteger::of($sosa)->getBitLength();
    }

    /**
     * Calculate the descendant sosa of the given sosa, at the given generation.
     * For instance, the descendant of the Sosa 14 at generation 2 is Sosa 3 (mother).
     *
     * @param int $sosa
     * @param int $gen
     * @return int
     */
    public function sosaDescendantOf(int $sosa, int $gen): int
    {
        $gen_sosa = $this->generation($sosa);
        return $gen_sosa <= $gen ? $sosa : BigInteger::of($sosa)
            ->dividedBy(BigInteger::of(2)->power($this->generation($sosa) - $gen), RoundingMode::DOWN)
            ->toInt();
    }

    /**
     * Check whether an individual is a Sosa ancestor.
     *
     * @param Tree $tree
     * @param UserInterface $user
     * @param Individual $indi
     * @return bool
     */
    public function isSosa(Tree $tree, UserInterface $user, Individual $indi): bool
    {
        return $this->sosaNumbers($tree, $user, $indi)->count() > 0;
    }

    /**
     * Returns all Sosa numbers associated to an Individual
     *
     * @param Tree $tree
     * @param UserInterface $user
     * @param Individual $indi
     * @return Collection<int, int>
     */
    public function sosaNumbers(Tree $tree, UserInterface $user, Individual $indi): Collection
    {
        return Registry::cache()->array()->remember(
            'sosanumbers-' . $indi->xref() . '@' . $tree->id() . '-' . $user->id(),
            function () use ($tree, $user, $indi): Collection {
                return DB::table('maj_sosa')
                    ->select(['majs_sosa', 'majs_gen'])
                    ->where('majs_gedcom_id', '=', $tree->id())
                    ->where('majs_user_id', '=', $user->id())
                    ->where('majs_i_id', '=', $indi->xref())
                    ->orderBy('majs_sosa')
                    ->get()->pluck('majs_gen', 'majs_sosa');
            }
        );
    }

    /**
     * Return a list of the Sosa ancestors across all generation
     *
     * @param Tree $tree
     * @param UserInterface $user
     * @return Collection<\stdClass>
     */
    public function listAncestors(Tree $tree, UserInterface $user): Collection
    {
        return DB::table('maj_sosa')
            ->select(['majs_sosa', 'majs_i_id'])
            ->where('majs_gedcom_id', '=', $tree->id())
            ->where('majs_user_id', '=', $user->id())
            ->orderBy('majs_sosa')
            ->get();
    }

    /**
     * Return a list of the Sosa ancestors at a given generation
     *
     * @param Tree $tree
     * @param UserInterface $user
     * @param int $gen
     * @return Collection<\stdClass>
     */
    public function listAncestorsAtGeneration(Tree $tree, UserInterface $user, int $gen): Collection
    {
        return DB::table('maj_sosa')
            ->select(['majs_sosa', 'majs_i_id'])
            ->where('majs_gedcom_id', '=', $tree->id())
            ->where('majs_user_id', '=', $user->id())
            ->where('majs_gen', '=', $gen)
            ->orderBy('majs_sosa')
            ->get();
    }

    /**
     * Return a list of the Sosa families at a given generation
     *
     * @param Tree $tree
     * @param UserInterface $user
     * @param int $gen
     * @return Collection<\stdClass>
     */
    public function listAncestorFamiliesAtGeneration(Tree $tree, UserInterface $user, int $gen): Collection
    {
        $table_prefix = DB::connection()->getTablePrefix();
        return DB::table('families')
            ->join('maj_sosa AS sosa_husb', function (JoinClause $join) use ($tree, $user): void {
                // Link to family husband
                $join->on('families.f_file', '=', 'sosa_husb.majs_gedcom_id')
                    ->on('families.f_husb', '=', 'sosa_husb.majs_i_id')
                    ->where('sosa_husb.majs_gedcom_id', '=', $tree->id())
                    ->where('sosa_husb.majs_user_id', '=', $user->id());
            })
            ->join('maj_sosa AS sosa_wife', function (JoinClause $join) use ($tree, $user): void {
                // Link to family husband
                $join->on('families.f_file', '=', 'sosa_wife.majs_gedcom_id')
                ->on('families.f_wife', '=', 'sosa_wife.majs_i_id')
                ->where('sosa_wife.majs_gedcom_id', '=', $tree->id())
                ->where('sosa_wife.majs_user_id', '=', $user->id());
            })
            ->select(['sosa_husb.majs_sosa', 'families.f_id'])
            ->where('sosa_husb.majs_gen', '=', $gen)
            ->whereRaw($table_prefix . 'sosa_husb.majs_sosa + 1 = ' . $table_prefix . 'sosa_wife.majs_sosa')
            ->orderBy('sosa_husb.majs_sosa')
            ->get();
    }

    /**
     * Return a list of Sosa ancestors missing at a given generation.
     * It includes the reference of either parent if it is known.
     *
     * @param Tree $tree
     * @param UserInterface $user
     * @param int $gen
     * @return Collection<\stdClass>
     */
    public function listMissingAncestorsAtGeneration(Tree $tree, UserInterface $user, int $gen): Collection
    {
        if ($gen === 1) {
            return collect();
        }

        $table_prefix = DB::connection()->getTablePrefix();
        return DB::table('maj_sosa AS sosa')
            ->select(['sosa.majs_i_id', 'sosa_fat.majs_i_id AS majs_fat_id', 'sosa_mot.majs_i_id AS majs_mot_id'])
            ->selectRaw('MIN(' . $table_prefix . 'sosa.majs_sosa) AS majs_sosa')
            ->leftJoin('maj_sosa AS sosa_fat', function (JoinClause $join) use ($tree, $user, $table_prefix): void {
                // Link to sosa's father
                $join->whereRaw($table_prefix . 'sosa_fat.majs_sosa = 2 * ' . $table_prefix . 'sosa.majs_sosa')
                    ->where('sosa_fat.majs_gedcom_id', '=', $tree->id())
                    ->where('sosa_fat.majs_user_id', '=', $user->id());
            })
            ->leftJoin('maj_sosa AS sosa_mot', function (JoinClause $join) use ($tree, $user, $table_prefix): void {
                // Link to sosa's mother
                $join->whereRaw($table_prefix . 'sosa_mot.majs_sosa = 2 * ' . $table_prefix . 'sosa.majs_sosa + 1')
                    ->where('sosa_mot.majs_gedcom_id', '=', $tree->id())
                    ->where('sosa_mot.majs_user_id', '=', $user->id());
            })
            ->where('sosa.majs_gedcom_id', '=', $tree->id())
            ->where('sosa.majs_user_id', '=', $user->id())
            ->where('sosa.majs_gen', '=', $gen - 1)
            ->where(function (Builder $query): void {
                $query->whereNull('sosa_fat.majs_i_id')
                    ->orWhereNull('sosa_mot.majs_i_id');
            })
            ->groupBy('sosa.majs_i_id', 'sosa_fat.majs_i_id', 'sosa_mot.majs_i_id')
            ->orderByRaw('MIN(' . $table_prefix . 'sosa.majs_sosa)')
            ->get();
    }

    /**
     * Remove all Sosa entries related to the gedcom file and user
     *
     * @param Tree $tree
     * @param UserInterface $user
     */
    public function deleteAll(Tree $tree, UserInterface $user): void
    {
        DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $tree->id())
            ->where('majs_user_id', '=', $user->id())
            ->delete();
    }

    /**
     *
     * @param Tree $tree
     * @param UserInterface $user
     * @param int $sosa
     */
    public function deleteAncestorsFrom(Tree $tree, UserInterface $user, int $sosa): void
    {
        DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $tree->id())
            ->where('majs_user_id', '=', $user->id())
            ->where('majs_sosa', '>=', $sosa)
            ->whereRaw(
                'FLOOR(majs_sosa / (POW(2, (majs_gen - ?)))) = ?',
                [$this->generation($sosa), $sosa]
            )
            ->delete();
    }

    /**
     * Insert (or update if already existing) a list of Sosa individuals
     *
     * @param Tree $tree
     * @param UserInterface $user
     * @param array<array<string,mixed>> $sosa_records
     */
    public function insertOrUpdate(Tree $tree, UserInterface $user, array $sosa_records): void
    {
        $mass_update = DB::connection()->getDriverName() === 'mysql';

        $bindings_placeholders = $bindings_values = [];
        $has_records = false;
        foreach ($sosa_records as $i => $row) {
            $gen = $this->generation($row['sosa']);
            if ($gen <=  $this->maxSystemGenerations()) {
                $has_records = true;
                if ($mass_update) {
                    $bindings_placeholders[] = '(:tree_id' . $i . ', :user_id' . $i . ', :sosa' . $i . ',' .
                        ' :indi_id' . $i . ', :gen' . $i . ',' .
                        ' :byear' . $i . ', :byearest' . $i . ', :dyear' . $i . ', :dyearest' . $i . ')';
                    $bindings_values += [
                        'tree_id' . $i => $tree->id(),
                        'user_id' . $i => $user->id(),
                        'sosa' . $i => $row['sosa'],
                        'indi_id' . $i => $row['indi'],
                        'gen' . $i => $gen,
                        'byear' . $i => $row['birth_year'],
                        'byearest' . $i => $row['birth_year_est'],
                        'dyear' . $i => $row['death_year'],
                        'dyearest' . $i => $row['death_year_est']
                    ];
                } else {
                    DB::table('maj_sosa')->updateOrInsert(
                        [ 'majs_gedcom_id' => $tree->id(), 'majs_user_id' => $user->id(), 'majs_sosa' => $row['sosa']],
                        [
                            'majs_i_id' => $row['indi'],
                            'majs_gen' => $gen,
                            'majs_birth_year' => $row['birth_year'],
                            'majs_birth_year_est' => $row['birth_year_est'],
                            'majs_death_year' => $row['death_year'],
                            'majs_death_year_est' => $row['death_year_est']
                        ]
                    );
                }
            }
        }

        if ($has_records && $mass_update) {
            DB::connection()->statement(
                'INSERT INTO `' . DB::connection()->getTablePrefix() . 'maj_sosa`' .
                ' (majs_gedcom_id, majs_user_id, majs_sosa,' .
                '   majs_i_id, majs_gen, majs_birth_year, majs_birth_year_est, majs_death_year, majs_death_year_est)' .
                ' VALUES ' . implode(',', $bindings_placeholders) .
                ' ON DUPLICATE KEY UPDATE majs_i_id = VALUES(majs_i_id), majs_gen = VALUES(majs_gen),' .
                '   majs_birth_year = VALUES(majs_birth_year), majs_birth_year_est = VALUES(majs_birth_year_est),' .
                '   majs_death_year = VALUES(majs_death_year), majs_death_year_est = VALUES(majs_death_year_est)',
                $bindings_values
            );
        }
    }
}

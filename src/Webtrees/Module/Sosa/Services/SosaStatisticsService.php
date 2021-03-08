<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Sosa\Services;

use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * Service for retrieving Sosa statistics
 */
class SosaStatisticsService
{

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
     * Constructor for Sosa Statistics Service
     *
     * @param Tree $tree
     * @param UserInterface $user
     */
    public function __construct(Tree $tree, UserInterface $user)
    {
        $this->tree = $tree;
        $this->user = $user;
    }

    /**
     * Return the root individual for the reference tree and user.
     *
     * @return Individual|NULL
     */
    public function rootIndividual(): ?Individual
    {
        $root_indi_id = $this->tree->getUserPreference($this->user, 'MAJ_SOSA_ROOT_ID');
        return Registry::individualFactory()->make($root_indi_id, $this->tree);
    }

    /**
     * Get the highest generation for the reference tree and user.
     *
     * @return int
     */
    public function maxGeneration(): int
    {
        return (int) DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $this->tree->id())
            ->where('majs_user_id', '=', $this->user->id())
            ->max('majs_gen');
    }

    /**
     * Get the total count of individuals in the tree.
     *
     * @return int
     */
    public function totalIndividuals(): int
    {
        return DB::table('individuals')
            ->where('i_file', '=', $this->tree->id())
            ->count();
    }

    /**
     * Get the total count of Sosa ancestors for all generations
     *
     * @return int
     */
    public function totalAncestors(): int
    {
        return DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $this->tree->id())
            ->where('majs_user_id', '=', $this->user->id())
            ->count();
    }

    /**
     * Get the total count of Sosa ancestors for a generation
     *
     * @return int
     */
    public function totalAncestorsAtGeneration(int $gen): int
    {
        return DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $this->tree->id())
            ->where('majs_user_id', '=', $this->user->id())
            ->where('majs_gen', '=', $gen)
            ->count();
    }

    /**
     * Get the total count of distinct Sosa ancestors for all generations
     *
     * @return int
     */
    public function totalDistinctAncestors(): int
    {
        return DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $this->tree->id())
            ->where('majs_user_id', '=', $this->user->id())
            ->distinct()
            ->count('majs_i_id');
    }

    /**
     * Get the mean generation time, as the slope of the linear regression of birth years vs generations
     *
     * @return float
     */
    public function meanGenerationTime(): float
    {
        $row = DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $this->tree->id())
            ->where('majs_user_id', '=', $this->user->id())
            ->whereNotNull('majs_birth_year')
            ->selectRaw('COUNT(majs_sosa) AS n')
            ->selectRaw('SUM(majs_gen * majs_birth_year) AS sum_xy')
            ->selectRaw('SUM(majs_gen) AS sum_x')
            ->selectRaw('SUM(majs_birth_year) AS sum_y')
            ->selectRaw('SUM(majs_gen * majs_gen) AS sum_x2')
            ->get()->first();

        return $row->n == 0 ? 0 :
            -($row->n * $row->sum_xy - $row->sum_x * $row->sum_y) / ($row->n * $row->sum_x2 - pow($row->sum_x, 2));
    }

    /**
     * Get the statistic array detailed by generation.
     * Statistics for each generation are:
     *  - The number of Sosa in generation
     *  - The number of Sosa up to generation
     *  - The number of distinct Sosa up to generation
     *  - The year of the first birth in generation
     *  - The year of the first estimated birth in generation
     *  - The year of the last birth in generation
     *  - The year of the last estimated birth in generation
     *  - The average year of birth in generation
     *
     * @return array<int, array<string, int|null>> Statistics array
     */
    public function statisticsByGenerations(): array
    {
        $stats_by_gen = $this->statisticsByGenerationBasicData();
        $cumul_stats_by_gen = $this->statisticsByGenerationCumulativeData();

        $statistics_by_gen = [];
        foreach ($stats_by_gen as $gen => $stats_gen) {
            $statistics_by_gen[(int) $stats_gen->gen] = array(
                'sosaCount'             =>  (int) $stats_gen->total_sosa,
                'sosaTotalCount'        =>  (int) $cumul_stats_by_gen[$gen]->total_cumul,
                'diffSosaTotalCount'    =>  (int) $cumul_stats_by_gen[$gen]->total_distinct_cumul,
                'firstBirth'            =>  $stats_gen->first_year,
                'firstEstimatedBirth'   =>  $stats_gen->first_est_year,
                'lastBirth'             =>  $stats_gen->last_year,
                'lastEstimatedBirth'    =>  $stats_gen->last_est_year
            );
        }

        return $statistics_by_gen;
    }

    /**
     * Returns the basic statistics data by generation.
     *
     * @return Collection
     */
    private function statisticsByGenerationBasicData(): Collection
    {
        return DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $this->tree->id())
            ->where('majs_user_id', '=', $this->user->id())
            ->groupBy('majs_gen')
            ->orderBy('majs_gen', 'asc')
            ->select('majs_gen AS gen')
            ->selectRaw('COUNT(majs_sosa) AS total_sosa')
            ->selectRaw('MIN(majs_birth_year) AS first_year')
            ->selectRaw('MIN(majs_birth_year_est) AS first_est_year')
            ->selectRaw('MAX(majs_birth_year) AS last_year')
            ->selectRaw('MAX(majs_birth_year_est) AS last_est_year')
            ->get()->keyBy('gen');
    }

    /**
     * Returns the cumulative statistics data by generation
     *
     * @return Collection
     */
    private function statisticsByGenerationCumulativeData(): Collection
    {
        $list_gen = DB::table('maj_sosa')->select('majs_gen')->distinct()
            ->where('majs_gedcom_id', '=', $this->tree->id())
            ->where('majs_user_id', '=', $this->user->id());

        return DB::table('maj_sosa')
            ->joinSub($list_gen, 'list_gen', function (JoinClause $join): void {
                $join->on('maj_sosa.majs_gen', '<=', 'list_gen.majs_gen')
                ->where('majs_gedcom_id', '=', $this->tree->id())
                ->where('majs_user_id', '=', $this->user->id());
            })
            ->groupBy('list_gen.majs_gen')
            ->select('list_gen.majs_gen AS gen')
            ->selectRaw('COUNT(majs_i_id) AS total_cumul')
            ->selectRaw('COUNT(DISTINCT majs_i_id) AS total_distinct_cumul')
            ->selectRaw('1 - COUNT(DISTINCT majs_i_id) / COUNT(majs_i_id) AS pedi_collapse_simple')
            ->get()->keyBy('gen');
    }

    /**
     * Returns the pedigree collapse improved calculation by generation.
     *
     * Format:
     *  - key : generation
     *  - values:
     *      - pedi_collapse_roots : pedigree collapse of ancestor roots for the generation
     *      - pedi_collapse_xgen : pedigree cross-generation shrinkage for the generation
     *
     * @return array<int, array<string, float>>
     */
    public function pedigreeCollapseByGenerationData(): array
    {
        $table_prefix = DB::connection()->getTablePrefix();

        $list_gen = DB::table('maj_sosa')->select('majs_gen')->distinct()
            ->where('majs_gedcom_id', '=', $this->tree->id())
            ->where('majs_user_id', '=', $this->user->id());

        /* Compute the contributions of nodes of previous generations to the current generation */
        $root_ancestors_contributions = DB::table('maj_sosa AS sosa')
            ->select(['list_gen.majs_gen AS gen', 'sosa.majs_gedcom_id', 'sosa.majs_user_id'])
            ->addSelect(['sosa.majs_i_id', 'sosa.majs_gen'])
            ->selectRaw(
                '(CASE ' .
                    ' WHEN ' . $table_prefix . 'sosa_fat.majs_i_id IS NULL' .
                    ' THEN POWER(2, ' . $table_prefix . 'list_gen.majs_gen - ' . $table_prefix . 'sosa.majs_gen - 1)' .
                    ' ELSE 0 ' .
                ' END)' .
                ' + (CASE ' .
                    ' WHEN ' . $table_prefix . 'sosa_mot.majs_i_id IS NULL' .
                    ' THEN POWER(2, ' . $table_prefix . 'list_gen.majs_gen - ' . $table_prefix . 'sosa.majs_gen - 1)' .
                    ' ELSE 0 ' .
                ' END) contrib'
            )
            ->joinSub($list_gen, 'list_gen', function (JoinClause $join): void {
                $join->on('sosa.majs_gen', '<', 'list_gen.majs_gen')
                    ->where('majs_gedcom_id', '=', $this->tree->id())
                    ->where('majs_user_id', '=', $this->user->id());
            })
            ->leftJoin('maj_sosa AS sosa_fat', function (JoinClause $join) use ($table_prefix): void {
                // Link to sosa's father
                $join->whereRaw($table_prefix . 'sosa_fat.majs_sosa = 2 * ' . $table_prefix . 'sosa.majs_sosa')
                    ->where('sosa_fat.majs_gedcom_id', '=', $this->tree->id())
                    ->where('sosa_fat.majs_user_id', '=', $this->user->id());
            })
            ->leftJoin('maj_sosa AS sosa_mot', function (JoinClause $join) use ($table_prefix): void {
                // Link to sosa's mother
                $join->whereRaw($table_prefix . 'sosa_mot.majs_sosa = 2 * ' . $table_prefix . 'sosa.majs_sosa + 1')
                    ->where('sosa_mot.majs_gedcom_id', '=', $this->tree->id())
                    ->where('sosa_mot.majs_user_id', '=', $this->user->id());
            })
            ->where('sosa.majs_gedcom_id', '=', $this->tree->id())
            ->where('sosa.majs_user_id', '=', $this->user->id())
            ->where(function (Builder $query): void {
                $query->whereNull('sosa_fat.majs_i_id')
                ->orWhereNull('sosa_mot.majs_i_id');
            });

        /* Identify nodes in the generations with ancestors who are also in the same generation.
         * This is the vertical/generational collapse that will reduce the number or roots.
         */
        $non_roots_ancestors = DB::table('maj_sosa AS sosa')
            ->select(['sosa.majs_gen', 'sosa.majs_gedcom_id', 'sosa.majs_user_id', 'sosa.majs_sosa'])
            ->selectRaw('MAX(' . $table_prefix . 'sosa_anc.majs_sosa) - MIN(' . $table_prefix . 'sosa_anc.majs_sosa)' .
                ' AS full_ancestors')
            ->join('maj_sosa AS sosa_anc', function (JoinClause $join) use ($table_prefix): void {
                $join->on('sosa.majs_gen', '<', 'sosa_anc.majs_gen')
                    ->whereRaw('FLOOR(' . $table_prefix . 'sosa_anc.majs_sosa / POWER(2, ' .
                        $table_prefix . 'sosa_anc.majs_gen - ' . $table_prefix . 'sosa.majs_gen)) = ' .
                        $table_prefix . 'sosa.majs_sosa')
                    ->where('sosa_anc.majs_gedcom_id', '=', $this->tree->id())
                    ->where('sosa_anc.majs_user_id', '=', $this->user->id());
            })
            ->where('sosa.majs_gedcom_id', '=', $this->tree->id())
            ->where('sosa.majs_user_id', '=', $this->user->id())
            ->whereIn('sosa_anc.majs_i_id', function (Builder $query) use ($table_prefix): void {
                $query->from('maj_sosa AS sosa_gen')
                ->select('sosa_gen.majs_i_id')->distinct()
                ->where('sosa_gen.majs_gedcom_id', '=', $this->tree->id())
                ->where('sosa_gen.majs_user_id', '=', $this->user->id())
                ->whereRaw($table_prefix . 'sosa_gen.majs_gen = ' . $table_prefix . 'sosa.majs_gen');
            })
            ->groupBy(['sosa.majs_gen', 'sosa.majs_gedcom_id', 'sosa.majs_user_id',
                'sosa.majs_sosa', 'sosa.majs_i_id']);

        /* Compute the contribution of the nodes in the generation,
         * excluding the nodes with ancestors in the same generation.
         * Nodes with a parent missing are not excluded to cater for the missing one.
         */
        $known_ancestors_contributions = DB::table('maj_sosa AS sosa')
            ->select(['sosa.majs_gen AS gen', 'sosa.majs_gedcom_id', 'sosa.majs_user_id'])
            ->addSelect(['sosa.majs_i_id', 'sosa.majs_gen'])
            ->selectRaw('1 AS contrib')
            ->leftJoinSub($non_roots_ancestors, 'nonroot', function (JoinClause $join): void {
                $join->on('sosa.majs_gen', '=', 'nonroot.majs_gen')
                    ->on('sosa.majs_sosa', '=', 'nonroot.majs_sosa')
                    ->where('nonroot.full_ancestors', '>', 0)
                    ->where('nonroot.majs_gedcom_id', '=', $this->tree->id())
                    ->where('nonroot.majs_user_id', '=', $this->user->id());
            })
            ->where('sosa.majs_gedcom_id', '=', $this->tree->id())
            ->where('sosa.majs_user_id', '=', $this->user->id())
            ->whereNull('nonroot.majs_sosa');

        /* Aggregate both queries, and calculate the sum of contributions by generation roots.
         * Exclude as well nodes that already appear in lower generations, as their branche has already been reduced.
         */
        $ancestors_contributions_sum = DB::connection()->query()
            ->fromSub($root_ancestors_contributions->unionAll($known_ancestors_contributions), 'sosa_contribs')
            ->select(['sosa_contribs.gen', 'sosa_contribs.majs_gedcom_id', 'sosa_contribs.majs_user_id'])
            ->addSelect(['sosa_contribs.majs_i_id', 'sosa_contribs.contrib'])
            ->selectRaw('COUNT(' . $table_prefix . 'sosa_contribs.majs_i_id) * ' .
                $table_prefix . 'sosa_contribs.contrib AS totalContrib')
            ->leftJoin('maj_sosa AS sosa_low', function (JoinClause $join): void {
                $join->on('sosa_low.majs_gen', '<', 'sosa_contribs.majs_gen')
                    ->on('sosa_low.majs_i_id', '=', 'sosa_contribs.majs_i_id')
                    ->where('sosa_low.majs_gedcom_id', '=', $this->tree->id())
                    ->where('sosa_low.majs_user_id', '=', $this->user->id());
            })
            ->whereNull('sosa_low.majs_sosa')
            ->groupBy(['sosa_contribs.gen', 'sosa_contribs.majs_gedcom_id', 'sosa_contribs.majs_user_id',
                'sosa_contribs.majs_i_id', 'sosa_contribs.contrib']);

        // Aggregate all generation roots to compute root and generation pedigree collapse
        $pedi_collapse_coll = DB::connection()->query()->fromSub($ancestors_contributions_sum, 'sosa_contribs_sum')
            ->select(['gen'])->selectRaw('SUM(contrib), SUM(totalContrib)')
            ->selectRaw('1 - SUM(contrib) / SUM(totalContrib) AS pedi_collapse_roots')  // Roots/horizontal collapse
            ->selectRaw('1 - SUM(totalContrib) / POWER ( 2, gen - 1) AS pedi_collapse_xgen') // Crossgeneration collapse
            ->groupBy(['gen', 'majs_gedcom_id', 'majs_user_id'])
            ->orderBy('gen')
            ->get();

        $pedi_collapse_by_gen = [];
        foreach ($pedi_collapse_coll as $collapse_gen) {
            $pedi_collapse_by_gen[(int) $collapse_gen->gen] = array(
                'pedi_collapse_roots'   =>  (float) $collapse_gen->pedi_collapse_roots,
                'pedi_collapse_xgen'   =>  (float) $collapse_gen->pedi_collapse_xgen
            );
        }
        return $pedi_collapse_by_gen;
    }

    /**
     * Return a Collection of the mean generation depth and deviation for all Sosa ancestors at a given generation.
     * Sosa 1 is of generation 1.
     *
     * Mean generation depth and deviation are calculated based on the works of Marie-Héléne Cazes and Pierre Cazes,
     * published in Population (French Edition), Vol. 51, No. 1 (Jan. - Feb., 1996), pp. 117-140
     * http://kintip.net/index.php?option=com_jdownloads&task=download.send&id=9&catid=4&m=0
     *
     * Format:
     *  - key : sosa number of the ancestor
     *  - values:
     *      - root_ancestor_id : ID of the ancestor
     *      - mean_gen_depth : Mean generation depth
     *      - stddev_gen_depth : Standard deviation of generation depth
     *
     * @param int $gen Sosa generation
     * @return Collection
     */
    public function generationDepthStatsAtGeneration(int $gen): Collection
    {
        $table_prefix = DB::connection()->getTablePrefix();
        $missing_ancestors_by_gen = DB::table('maj_sosa AS sosa')
            ->selectRaw($table_prefix . 'sosa.majs_gen - ? AS majs_gen_norm', [$gen])
            ->selectRaw('FLOOR(((' . $table_prefix . 'sosa.majs_sosa / POW(2, ' . $table_prefix . 'sosa.majs_gen -1 )) - 1) * POWER(2, ? - 1)) + POWER(2, ? - 1) AS root_ancestor', [$gen, $gen])   //@phpcs:ignore Generic.Files.LineLength.TooLong
            ->selectRaw('SUM(CASE WHEN ' . $table_prefix . 'sosa_fat.majs_i_id IS NULL AND ' . $table_prefix . 'sosa_mot.majs_i_id IS NULL THEN 1 ELSE 0 END) AS full_root_count')  //@phpcs:ignore Generic.Files.LineLength.TooLong
            ->selectRaw('SUM(CASE WHEN ' . $table_prefix . 'sosa_fat.majs_i_id IS NULL AND ' . $table_prefix . 'sosa_mot.majs_i_id IS NULL THEN 0 ELSE 1 END) As semi_root_count')  //@phpcs:ignore Generic.Files.LineLength.TooLong
            ->leftJoin('maj_sosa AS sosa_fat', function (JoinClause $join) use ($table_prefix): void {
                // Link to sosa's father
                $join->whereRaw($table_prefix . 'sosa_fat.majs_sosa = 2 * ' . $table_prefix . 'sosa.majs_sosa')
                ->where('sosa_fat.majs_gedcom_id', '=', $this->tree->id())
                ->where('sosa_fat.majs_user_id', '=', $this->user->id());
            })
            ->leftJoin('maj_sosa AS sosa_mot', function (JoinClause $join) use ($table_prefix): void {
                // Link to sosa's mother
                $join->whereRaw($table_prefix . 'sosa_mot.majs_sosa = 2 * ' . $table_prefix . 'sosa.majs_sosa + 1')
                ->where('sosa_mot.majs_gedcom_id', '=', $this->tree->id())
                ->where('sosa_mot.majs_user_id', '=', $this->user->id());
            })
            ->where('sosa.majs_gedcom_id', '=', $this->tree->id())
            ->where('sosa.majs_user_id', '=', $this->user->id())
            ->where('sosa.majs_gen', '>=', $gen)
            ->where(function (Builder $query): void {
                $query->whereNull('sosa_fat.majs_i_id')
                    ->orWhereNull('sosa_mot.majs_i_id');
            })
            ->groupBy(['sosa.majs_gen', 'root_ancestor']);

        return DB::table('maj_sosa AS sosa_list')
            ->select(['stats_by_gen.root_ancestor AS root_ancestor_sosa', 'sosa_list.majs_i_id as root_ancestor_id'])
            ->selectRaw('1 + SUM( (majs_gen_norm) * ( 2 * full_root_count + semi_root_count) /  (2 * POWER(2, majs_gen_norm))) AS mean_gen_depth')  //@phpcs:ignore Generic.Files.LineLength.TooLong
            ->selectRaw(' SQRT(' .
                '   SUM(POWER(majs_gen_norm, 2) * ( 2 * full_root_count + semi_root_count) /  (2 * POWER(2, majs_gen_norm)))' .     //@phpcs:ignore Generic.Files.LineLength.TooLong
                '   - POWER( SUM( (majs_gen_norm) * ( 2 * full_root_count + semi_root_count) /  (2 * POWER(2, majs_gen_norm))), 2)' .       //@phpcs:ignore Generic.Files.LineLength.TooLong
                ' ) AS stddev_gen_depth')
            ->joinSub($missing_ancestors_by_gen, 'stats_by_gen', function (JoinClause $join): void {
                $join->on('sosa_list.majs_sosa', '=', 'stats_by_gen.root_ancestor')
                    ->where('sosa_list.majs_gedcom_id', '=', $this->tree->id())
                    ->where('sosa_list.majs_user_id', '=', $this->user->id());
            })
            ->groupBy(['stats_by_gen.root_ancestor', 'sosa_list.majs_i_id'])
            ->orderBy('stats_by_gen.root_ancestor')
            ->get()->keyBy('root_ancestor_sosa');
    }

    /**
     * Return a collection of the most duplicated root Sosa ancestors.
     * The number of ancestors to return is limited by the parameter $limit.
     * If several individuals are tied when reaching the limit, none of them are returned,
     * which means that there can be less individuals returned than requested.
     *
     * Format:
     *  - value:
     *      - sosa_i_id : sosa individual
     *      - sosa_count: number of duplications of the ancestor (e.g. 3 if it appears 3 times)
     *
     * @param int $limit
     * @return Collection
     */
    public function topMultipleAncestorsWithNoTies(int $limit): Collection
    {
        $table_prefix = DB::connection()->getTablePrefix();
        $multiple_ancestors = DB::table('maj_sosa AS sosa')
            ->select('sosa.majs_i_id AS sosa_i_id')
            ->selectRaw('COUNT(' . $table_prefix . 'sosa.majs_sosa) AS sosa_count')
            ->leftJoin('maj_sosa AS sosa_fat', function (JoinClause $join) use ($table_prefix): void {
                // Link to sosa's father
                $join->whereRaw($table_prefix . 'sosa_fat.majs_sosa = 2 * ' . $table_prefix . 'sosa.majs_sosa')
                    ->where('sosa_fat.majs_gedcom_id', '=', $this->tree->id())
                    ->where('sosa_fat.majs_user_id', '=', $this->user->id());
            })
            ->leftJoin('maj_sosa AS sosa_mot', function (JoinClause $join) use ($table_prefix): void {
                // Link to sosa's mother
                $join->whereRaw($table_prefix . 'sosa_mot.majs_sosa = 2 * ' . $table_prefix . 'sosa.majs_sosa + 1')
                ->where('sosa_mot.majs_gedcom_id', '=', $this->tree->id())
                ->where('sosa_mot.majs_user_id', '=', $this->user->id());
            })
            ->where('sosa.majs_gedcom_id', '=', $this->tree->id())
            ->where('sosa.majs_user_id', '=', $this->user->id())
            ->whereNull('sosa_fat.majs_sosa')   // We keep only root individuals, i.e. those with no father or mother
            ->whereNull('sosa_mot.majs_sosa')
            ->groupBy('sosa.majs_i_id')
            ->havingRaw('COUNT(' . $table_prefix . 'sosa.majs_sosa) > 1')    // Limit to the duplicate sosas.
            ->orderByRaw('COUNT(' . $table_prefix . 'sosa.majs_sosa) DESC, MIN(' . $table_prefix . 'sosa.majs_sosa) ASC')   //@phpcs:ignore Generic.Files.LineLength.TooLong
            ->limit($limit + 1)     // We want to select one more than required, for ties
            ->get();

        if ($multiple_ancestors->count() > $limit) {
            $last_count = $multiple_ancestors->last()->sosa_count;
            $multiple_ancestors = $multiple_ancestors->reject(function ($element) use ($last_count): bool {
                return $element->sosa_count ==  $last_count;
            });
        }
        return $multiple_ancestors;
    }

    /**
     * Return a computed array of statistics about the dispersion of ancestors across the ancestors
     * at a specified generation.
     *
     * Format:
     *  - key : rank of the ancestor in generation G for which exclusive ancestors have been found
     *          For instance 3 represent the maternal grand father
     *          0 is used for shared ancestors
     *  - values: number of ancestors exclusively in the ancestors of the ancestor in key
     *
     *  For instance a result at generation 3 could be :
     *      array (   0     =>  12      -> 12 ancestors are shared by the grand-parents
     *                1     =>  32      -> 32 ancestors are exclusive to the paternal grand-father
     *                2     =>  25      -> 25 ancestors are exclusive to the paternal grand-mother
     *                3     =>  12      -> 12 ancestors are exclusive to the maternal grand-father
     *                4     =>  30      -> 30 ancestors are exclusive to the maternal grand-mother
     *            )
     *
     * @param int $gen
     * @return Collection
     */
    public function ancestorsDispersionForGeneration(int $gen): Collection
    {
        $ancestors_branches = DB::table('maj_sosa')
            ->select('majs_i_id AS i_id')
            ->selectRaw('FLOOR(majs_sosa / POW(2, (majs_gen - ?))) - POW(2, ? -1) + 1 AS branch', [$gen, $gen])
            ->where('majs_gedcom_id', '=', $this->tree->id())
            ->where('majs_user_id', '=', $this->user->id())
            ->where('majs_gen', '>=', $gen)
            ->groupBy('majs_i_id', 'branch');


        $consolidated_ancestors_branches = DB::table('maj_sosa')
            ->fromSub($ancestors_branches, 'indi_branch')
            ->select('i_id')
            ->selectRaw('CASE WHEN COUNT(branch) > 1 THEN 0 ELSE MIN(branch) END AS branches')
            ->groupBy('i_id');

        return DB::table('maj_sosa')
            ->fromSub($consolidated_ancestors_branches, 'indi_branch_consolidated')
            ->select('branches')
            ->selectRaw('COUNT(i_id) AS count_indi')
            ->groupBy('branches')
            ->get()->pluck('count_indi', 'branches');
    }
}

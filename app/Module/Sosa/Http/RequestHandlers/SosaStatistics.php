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

namespace MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers;

use Brick\Math\BigInteger;
use Brick\Math\RoundingMode;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Module\ModuleThemeInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\RelationshipService;
use MyArtJaub\Webtrees\Module\Sosa\SosaModule;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaStatisticsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying Sosa statistics
 *
 */
class SosaStatistics implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?SosaModule $module;
    private RelationshipService $relationship_service;

    /**
     * Constructor for AncestorsList Request Handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service, RelationshipService $relationship_service)
    {
        $this->module = $module_service->findByInterface(SosaModule::class)->first();
        $this->relationship_service = $relationship_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $tree = Validator::attributes($request)->tree();
        $user = Auth::check() ? Validator::attributes($request)->user() : new DefaultUser();

        /** @var SosaStatisticsService $sosa_stats_service */
        $sosa_stats_service = app()->makeWith(SosaStatisticsService::class, ['tree' => $tree, 'user' => $user]);

        return $this->viewResponse($this->module->name() . '::statistics-page', [
            'module_name'       =>  $this->module->name(),
            'title'             =>  I18N::translate('Sosa Statistics'),
            'tree'              =>  $tree,
            'theme'             =>  app(ModuleThemeInterface::class),
            'root_indi'         =>  $sosa_stats_service->rootIndividual(),
            'general_stats'     =>  $this->statisticsGeneral($sosa_stats_service),
            'generation_stats'  =>  $this->statisticsByGenerations($sosa_stats_service),
            'generation_depth'  =>  $sosa_stats_service->generationDepthStatsAtGeneration(1)->first(),
            'multiple_sosas'    =>  $sosa_stats_service->topMultipleAncestorsWithNoTies(10)->groupBy('sosa_count'),
            'sosa_dispersion_g2' =>  $sosa_stats_service->ancestorsDispersionForGeneration(2),
            'sosa_dispersion_g3' =>  $sosa_stats_service->ancestorsDispersionForGeneration(3),
            'gen_depth_g3'      =>  $sosa_stats_service->generationDepthStatsAtGeneration(3),
            'relationship_service'  =>  $this->relationship_service,
        ]);
    }

    /**
     * Retrieve and compute the global statistics of ancestors for the tree.
     * Statistics include the number of ancestors, the number of different ancestors, pedigree collapse...
     *
     * @param SosaStatisticsService $sosa_stats_service
     * @return array<string, int|float>
     */
    private function statisticsGeneral(SosaStatisticsService $sosa_stats_service): array
    {
        $ancestors_count = $sosa_stats_service->totalAncestors();
        $ancestors_distinct_count = $sosa_stats_service->totalDistinctAncestors();
        $individual_count = $sosa_stats_service->totalIndividuals();

        return [
            'sosa_count'    =>  $ancestors_count,
            'distinct_count'    =>  $ancestors_distinct_count,
            'sosa_rate' =>  $this->safeDivision(
                BigInteger::of($ancestors_distinct_count),
                BigInteger::of($individual_count)
            ),
            'mean_gen_time'         =>  $sosa_stats_service->meanGenerationTime()
        ];
    }

    /**
     * Retrieve and compute the statistics of ancestors by generations.
     * Statistics include the number of ancestors, the number of different ancestors, cumulative statistics...
     *
     * @param SosaStatisticsService $sosa_stats_service
     * @return array<int, array<string, int|float>>
     */
    private function statisticsByGenerations(SosaStatisticsService $sosa_stats_service): array
    {
        $stats_by_gen = $sosa_stats_service->statisticsByGenerations();

        $generation_stats = array();

        foreach ($stats_by_gen as $gen => $stats_gen) {
            $gen_diff = $gen > 1 ?
                (int) $stats_gen['diffSosaTotalCount'] - (int) $stats_by_gen[$gen - 1]['diffSosaTotalCount'] :
                1;
            $generation_stats[$gen] = array(
                'gen_min_birth' => $stats_gen['firstBirth'] ?? (int) $stats_gen['firstEstimatedBirth'],
                'gen_max_birth' => $stats_gen['lastBirth'] ?? (int) $stats_gen['lastEstimatedBirth'],
                'theoretical' => BigInteger::of(2)->power($gen - 1)->toInt(),
                'known' => (int) $stats_gen['sosaCount'],
                'perc_known' => $this->safeDivision(
                    BigInteger::of((int) $stats_gen['sosaCount']),
                    BigInteger::of(2)->power($gen - 1)
                ),
                'missing' => $gen > 1 ?
                    2 * (int) $stats_by_gen[$gen - 1]['sosaCount'] - (int) $stats_gen['sosaCount'] :
                    0,
                'perc_missing' => $gen > 1 ?
                    1 - $this->safeDivision(
                        BigInteger::of((int) $stats_gen['sosaCount']),
                        BigInteger::of(2 * (int) $stats_by_gen[$gen - 1]['sosaCount'])
                    ) :
                    0,
                'total_known' => (int) $stats_gen['sosaTotalCount'],
                'perc_total_known' => $this->safeDivision(
                    BigInteger::of((int) $stats_gen['sosaTotalCount']),
                    BigInteger::of(2)->power($gen)->minus(1)
                ),
                'different' => $gen_diff,
                'perc_different' => $this->safeDivision(
                    BigInteger::of($gen_diff),
                    BigInteger::of((int) $stats_gen['sosaCount'])
                ),
                'total_different' => (int) $stats_gen['diffSosaTotalCount']
            );
        }

        return $generation_stats;
    }

    /**
     * Return the result of a division, and a default value if denominator is 0
     *
     * @param BigInteger $p Numerator
     * @param BigInteger $q Denominator
     * @param int $scale Rounding scale
     * @param float $default Value if denominator is 0
     * @return float
     */
    private function safeDivision(BigInteger $p, BigInteger $q, int $scale = 10, float $default = 0): float
    {
        return $q->isZero() ? $default : $p->toBigDecimal()->dividedBy($q, $scale, RoundingMode::HALF_DOWN)->toFloat();
    }
}

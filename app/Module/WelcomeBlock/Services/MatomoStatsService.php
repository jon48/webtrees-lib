<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage WelcomeBlock
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\WelcomeBlock\Services;

use Carbon\Carbon;
use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Registry;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\GuzzleException;
use MyArtJaub\Webtrees\Module\WelcomeBlock\WelcomeBlockModule;

/**
 * Service for retrieving Matomo statistics
 *
 */
class MatomoStatsService
{
    /**
     * Returns the number of visits for the current year (up to the day before).
     * That statistic is cached for the day, to avoid unecessary calls to Matomo API.
     *
     * @param WelcomeBlockModule $module
     * @param int $block_id
     * @return int|NULL
     */
    public function visitsThisYear(WelcomeBlockModule $module, int $block_id): ?int
    {
        return Registry::cache()->file()->remember(
            $module->name() . '-matomovisits-yearly-' . $block_id,
            function () use ($module, $block_id): ?int {
                $visits_year = $this->visits($module, $block_id, 'year');
                if ($visits_year === null) {
                    return null;
                }
                $visits_today = (int) $this->visits($module, $block_id, 'day');

                return $visits_year - $visits_today;
            },
            Carbon::now()->addDay()->startOfDay()->diffInSeconds(Carbon::now()) // Valid until midnight
        );
    }

    /**
     * Returns the number of visits for the current day.
     *
     * @param WelcomeBlockModule $module
     * @param int $block_id
     * @return int|NULL
     */
    public function visitsToday(WelcomeBlockModule $module, int $block_id): ?int
    {
        return Registry::cache()->array()->remember(
            $module->name() . '-matomovisits-daily-' . $block_id,
            function () use ($module, $block_id): ?int {
                return $this->visits($module, $block_id, 'day');
            }
        );
    }

    /**
     * Invoke the Matomo API to retrieve the number of visits over a period.
     *
     * @param WelcomeBlockModule $module
     * @param int $block_id
     * @param string $period
     * @return int|NULL
     */
    protected function visits(WelcomeBlockModule $module, int $block_id, string $period): ?int
    {
        $settings = $module->matomoSettings($block_id);

        if (
            $settings['matomo_enabled'] === true
            && mb_strlen($settings['matomo_url']) > 0
            && mb_strlen($settings['matomo_token']) > 0
            && $settings['matomo_siteid'] > 0
        ) {
            try {
                $http_client = new Client([
                    RequestOptions::TIMEOUT => 30
                ]);

                $response = $http_client->get($settings['matomo_url'], [
                    'query' =>  [
                        'module'    =>  'API',
                        'method'    =>  'VisitsSummary.getVisits',
                        'idSite'    =>  $settings['matomo_siteid'],
                        'period'    =>  $period,
                        'date'      =>  'today',
                        'token_auth' =>  $settings['matomo_token'],
                        'format'    =>  'json'
                    ]
                ]);

                if ($response->getStatusCode() === StatusCodeInterface::STATUS_OK) {
                    $result = json_decode((string) $response->getBody(), true)['value'] ?? null;
                    if ($result !== null) {
                        return (int)$result;
                    }
                }
            } catch (GuzzleException $ex) {
            }
        }

        return null;
    }
}

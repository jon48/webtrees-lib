<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\AdminTasks\Tasks;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\NoReplyUser;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\TreeUser;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\EmailService;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\TreeService;
use Fisharebest\Webtrees\Services\UpgradeService;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Webtrees\Common\Tasks\TaskSchedule;
use MyArtJaub\Webtrees\Contracts\Tasks\ConfigurableTaskInterface;
use MyArtJaub\Webtrees\Contracts\Tasks\TaskInterface;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Services\HealthCheckService;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Task to send an email summarising the healthcheck of the site
 */
class HealthCheckEmailTask implements TaskInterface, ConfigurableTaskInterface
{
    /**
     * Name of the Tree preference to check if the task is enabled for that tree
     * @var string
     */
    public const TREE_PREFERENCE_NAME = 'MAJ_AT_HEALTHCHECK_ENABLED';

    private ?AdminTasksModule $module;
    private HealthCheckService $healthcheck_service;
    private EmailService $email_service;
    private UserService $user_service;
    private TreeService $tree_service;
    private UpgradeService $upgrade_service;

    /**
     * Constructor for HealthCheckTask
     *
     * @param ModuleService $module_service
     * @param HealthCheckService $healthcheck_service
     * @param EmailService $email_service
     * @param UserService $user_service
     * @param TreeService $tree_service
     * @param UpgradeService $upgrade_service
     */
    public function __construct(
        ModuleService $module_service,
        HealthCheckService $healthcheck_service,
        EmailService $email_service,
        UserService $user_service,
        TreeService $tree_service,
        UpgradeService $upgrade_service
    ) {
        $this->module = $module_service->findByInterface(AdminTasksModule::class)->first();
        $this->healthcheck_service = $healthcheck_service;
        $this->email_service = $email_service;
        $this->user_service = $user_service;
        $this->tree_service = $tree_service;
        $this->upgrade_service = $upgrade_service;
    }


    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Tasks\TaskInterface::name()
     */
    public function name(): string
    {
        return I18N::translate('Healthcheck Email');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Tasks\TaskInterface::defaultFrequency()
     */
    public function defaultFrequency(): int
    {
        return 10080; // = 1 week = 7 * 24 * 60 min
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Tasks\TaskInterface::run()
     */
    public function run(TaskSchedule $task_schedule): bool
    {
        if ($this->module === null) {
            return false;
        }

        $res = true;

        // Compute the number of days to compute
        $interval_lastrun = $task_schedule->lastRunTime()->diffAsCarbonInterval(CarbonImmutable::now());
        $interval_frequency = CarbonInterval::minutes($task_schedule->frequency());
        $interval = $interval_lastrun->greaterThan($interval_frequency) ? $interval_lastrun : $interval_frequency;
        $nb_days = (int) $interval->ceilDay()->totalDays;

        $view_params_site = [
            'nb_days'               =>  $nb_days,
            'upgrade_available'     =>  $this->upgrade_service->isUpgradeAvailable(),
            'latest_version'        =>  $this->upgrade_service->latestVersion(),
            'download_url'          =>  $this->upgrade_service->downloadUrl(),
            'all_users'             =>  $this->user_service->all(),
            'unapproved'            =>  $this->user_service->unapproved(),
            'unverified'            =>  $this->user_service->unverified(),
        ];

        foreach ($this->tree_service->all() as $tree) {
        /** @var Tree $tree */

            if ($tree->getPreference(self::TREE_PREFERENCE_NAME) !== '1') {
                continue;
            }

            $webmaster = $this->user_service->find((int) $tree->getPreference('WEBMASTER_USER_ID'));
            if ($webmaster === null) {
                continue;
            }
            I18N::init($webmaster->getPreference('language'));

            $error_logs = $this->healthcheck_service->errorLogs($tree, $nb_days);
            $nb_errors = $error_logs->sum('nblogs');

            $view_params = $view_params_site + [
                'tree'              =>  $tree,
                'total_by_type'     =>  $this->healthcheck_service->countByRecordType($tree),
                'change_by_type'    =>  $this->healthcheck_service->changesByRecordType($tree, $nb_days),
                'error_logs'        =>  $error_logs,
                'nb_errors'         =>  $nb_errors
            ];

            $res = $res && $this->email_service->send(
                new TreeUser($tree),
                $webmaster,
                new NoReplyUser(),
                I18N::translate('Health Check Report') . ' - ' . I18N::translate('Tree %s', $tree->name()),
                view($this->module->name() . '::tasks/healthcheck/email-healthcheck-text', $view_params),
                view($this->module->name() . '::tasks/healthcheck/email-healthcheck-html', $view_params)
            );
        }

        return $res;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Tasks\ConfigurableTaskInterface::configView()
     */
    public function configView(ServerRequestInterface $request): string
    {
        return $this->module === null ? '' : view($this->module->name() . '::tasks/healthcheck/config', [
            'all_trees'     =>  $this->tree_service->all()
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Tasks\ConfigurableTaskInterface::updateConfig()
     */
    public function updateConfig(ServerRequestInterface $request, TaskSchedule $task_schedule): bool
    {
        try {
            $validator = Validator::parsedBody($request);

            foreach ($this->tree_service->all() as $tree) {
                if (Auth::isManager($tree)) {
                    $tree_enabled = $validator->boolean('HEALTHCHECK_ENABLED_' . $tree->id(), false);
                    $tree->setPreference(self::TREE_PREFERENCE_NAME, $tree_enabled ? '1' : '0');
                }
            }
            return true;
        } catch (Throwable $ex) {
            Log::addErrorLog(
                sprintf(
                    'Error while updating the Task schedule "%s". Exception: %s',
                    $task_schedule->id(),
                    $ex->getMessage()
                )
            );
        }
        return false;
    }
}

<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage IsSourced
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2026, Jonathan Jaubart
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\IsSourced;

use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleSidebarInterface;
use Fisharebest\Webtrees\Module\ModuleSidebarTrait;
use MyArtJaub\Webtrees\Contracts\Hooks\ModuleHookSubscriberInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\IsSourced\Hooks\IsSourcedStatusColumnsHook;
use MyArtJaub\Webtrees\Module\IsSourced\Hooks\IsSourcedStatusHook;
use MyArtJaub\Webtrees\Module\IsSourced\Services\SourceStatusService;

/**
 * IsSourced Module
 */
class IsSourcedModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleGlobalInterface,
    ModuleSidebarInterface,
    ModuleHookSubscriberInterface
{
    use ModuleMyArtJaubTrait;
    use ModuleGlobalTrait;
    use ModuleSidebarTrait;

    #[\Override]
    public function title(): string
    {
        return I18N::translate('Sourced events');
    }

    #[\Override]
    public function description(): string
    {
        return I18N::translate('Indicate if events related to an record are sourced.');
    }

    #[\Override]
    public function customModuleVersion(): string
    {
        return '2.1.8-v.1';
    }

    #[\Override]
    public function headContent(): string
    {
        return '<link rel="stylesheet" href="' . e($this->moduleCssUrl()) . '">';
    }

    #[\Override]
    public function bodyContent(): string
    {
        return '<script src="' . $this->assetUrl('js/issourced.min.js') . '"></script>';
    }

    #[\Override]
    public function hasSidebarContent(Individual $individual): bool
    {
        return true;
    }

    #[\Override]
    public function getSidebarContent(Individual $individual): string
    {
        /** @var SourceStatusService $source_status_service */
        $source_status_service = app(SourceStatusService::class);

        $spouse_families_status = $individual->spouseFamilies()->map(
            function (Family $sfamily) use ($source_status_service): array {
                return [ $sfamily, $source_status_service->sourceStatusForMarriage($sfamily)];
            }
        )->filter(function (array $item): bool {
            return $item[1]->isSet();
        });

        return view($this->name() . '::sidebar/content', [
            'module_name'               => $this->name(),
            'individual'                =>  $individual,
            'source_status_individual'  =>  $source_status_service->sourceStatusForRecord($individual),
            'source_status_birth'       =>  $source_status_service->sourceStatusForBirth($individual),
            'source_status_marriages'   =>  $spouse_families_status,
            'source_status_death'       =>  $source_status_service->sourceStatusForDeath($individual)
        ]);
    }

    #[\Override]
    public function listSubscribedHooks(): array
    {
        return [
            app()->makeWith(IsSourcedStatusHook::class, [ 'module' => $this ]),
            app()->makeWith(IsSourcedStatusColumnsHook::class, [ 'module' => $this ])
        ];
    }
}

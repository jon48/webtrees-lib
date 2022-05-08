<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage IsSourced
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\IsSourced\Hooks;

use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\FamilyDatatablesExtenderInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\IndividualDatatablesExtenderInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\SosaFamilyDatatablesExtenderInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\SosaIndividualDatatablesExtenderInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\SosaMissingDatatablesExtenderInterface;
use MyArtJaub\Webtrees\Module\IsSourced\Services\SourceStatusService;

/**
 * Hook for adding columns with source statuses in datatables.
 */
class IsSourcedStatusColumnsHook implements
    FamilyDatatablesExtenderInterface,
    IndividualDatatablesExtenderInterface,
    SosaFamilyDatatablesExtenderInterface,
    SosaIndividualDatatablesExtenderInterface,
    SosaMissingDatatablesExtenderInterface
{
    private ModuleInterface $module;
    private SourceStatusService $source_status_service;

    /**
     * Constructor for IsSourcedStatusColumnsHook
     *
     * @param ModuleInterface $module
     * @param SourceStatusService $source_status_service
     */
    public function __construct(ModuleInterface $module, SourceStatusService $source_status_service)
    {
        $this->module = $module;
        $this->source_status_service = $source_status_service;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookInterface::module()
     */
    public function module(): ModuleInterface
    {
        return $this->module;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\IndividualDatatablesExtenderInterface::individualColumns()
     */
    public function individualColumns(iterable $records): array
    {
        $records = collect($records);
        return [
            'issourced' => [
                'birth' => [
                    'position' => 7,
                    'column_def' => [ 'class' => 'text-center' ],
                    'th' => view($this->module()->name() . '::components/column-th-issourced', [
                        'title' => I18N::translate('%s sourced', Registry::elementFactory()->make('INDI:BIRT')->label())
                    ]),
                    'records' => $records->map(function (Individual $individual): array {
                        $source_status = $this->source_status_service->sourceStatusForBirth($individual);
                        return [
                            'order' => $source_status->order(),
                            'text' => view($this->module()->name() . '::icons/source-status', [
                                'module_name' => $this->module()->name(),
                                'source_status' => $source_status,
                                'context'  => 'INDI:BIRT',
                                'size_style' => '' ])
                        ];
                    })->toArray()
                ],
                'death' => [
                    'position' => 12,
                    'column_def' => [ 'class' => 'text-center' ],
                    'th' => view($this->module()->name() . '::components/column-th-issourced', [
                        'title' => I18N::translate('%s sourced', Registry::elementFactory()->make('INDI:DEAT')->label())
                    ]),
                    'records' => $records->map(function (Individual $individual): array {
                        $source_status = $this->source_status_service->sourceStatusForDeath($individual);
                        return $individual->isDead() ? [
                            'order' =>  $source_status->order(),
                            'text' => view($this->module()->name() . '::icons/source-status', [
                                'module_name' => $this->module()->name(),
                                'source_status' => $source_status,
                                'context'  => 'INDI:DEAT',
                                'size_style' => '' ])
                        ] : ['order' => 0, 'text' => ''];
                    })->toArray()
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\FamilyDatatablesExtenderInterface::familyColumns()
     */
    public function familyColumns(iterable $records): array
    {
        $records = collect($records);
        return [
            'issourced' => [
                'marr' => [
                    'position' => 10,
                    'column_def' => [ 'class' => 'text-center' ],
                    'th' => view($this->module()->name() . '::components/column-th-issourced', [
                        'title' => I18N::translate('%s sourced', Registry::elementFactory()->make('FAM:MARR')->label())
                    ]),
                    'records' => $records->map(function (Family $family): array {
                        $source_status = $this->source_status_service->sourceStatusForMarriage($family);
                        return $family->getMarriage() !== null ? [
                            'order' =>  $source_status->order(),
                            'text' => view($this->module()->name() . '::icons/source-status', [
                                'module_name' => $this->module()->name(),
                                'source_status' => $source_status,
                                'context'  => 'FAM:MARR',
                                'size_style' => '' ])
                        ] : ['order' => 0, 'text' => ''];
                    })->toArray()
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\SosaIndividualDatatablesExtenderInterface::sosaIndividualColumns()
     */
    public function sosaIndividualColumns(iterable $records): array
    {
        $columns = $this->individualColumns($records);
        $columns['issourced']['birth']['position'] = 5;
        $columns['issourced']['death']['position'] = 8;
        return $columns;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\SosaFamilyDatatablesExtenderInterface::sosaFamilyColumns()
     */
    public function sosaFamilyColumns(iterable $records): array
    {
        return $this->familyColumns($records);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\SosaMissingDatatablesExtenderInterface::sosaMissingColumns()
     */
    public function sosaMissingColumns(iterable $records): array
    {
        $records = collect($records);
        return [
            'issourced' => [
                'indi' => [
                    'position' => 3,
                    'column_def' => [ 'class' => 'text-center' ],
                    'th' => view($this->module()->name() . '::components/column-th-issourced', [
                        'title' => I18N::translate('%s sourced', Registry::elementFactory()->make('INDI')->label())
                    ]),
                    'records' => $records->map(function (Individual $individual): array {
                        $source_status = $this->source_status_service->sourceStatusForRecord($individual);
                        return [
                            'order' => $source_status->order(),
                            'text' => view($this->module()->name() . '::icons/source-status', [
                                'module_name' => $this->module()->name(),
                                'source_status' => $source_status,
                                'context'  => 'INDI',
                                'size_style' => '' ])
                        ];
                    })->toArray()
                ],
                'birth' => [
                    'position' => 7,
                    'column_def' => [ 'class' => 'text-center' ],
                    'th' => view($this->module()->name() . '::components/column-th-issourced', [
                        'title' => I18N::translate('%s sourced', Registry::elementFactory()->make('INDI:BIRT')->label())
                    ]),
                    'records' => $records->map(function (Individual $individual): array {
                        $source_status = $this->source_status_service->sourceStatusForBirth($individual);
                        return [
                            'order' => $source_status->order(),
                            'text' => view($this->module()->name() . '::icons/source-status', [
                                'module_name' => $this->module()->name(),
                                'source_status' => $source_status,
                                'context'  => 'INDI:BIRT',
                                'size_style' => '' ])
                        ];
                    })->toArray()
                ]
            ]
        ];
    }
}

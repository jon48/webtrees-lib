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

use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\RecordNameTextExtenderInterface;
use MyArtJaub\Webtrees\Module\IsSourced\Services\SourceStatusService;

/**
 * Hook for displaying the source status for a record next to its name.
 */
class IsSourcedStatusHook implements RecordNameTextExtenderInterface
{
    private ModuleInterface $module;
    private SourceStatusService $source_status_service;

    /**
     * Constructor for IsSourcedStatusHook
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
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\RecordNameTextExtenderInterface::recordNamePrepend()
     */
    public function recordNamePrepend(GedcomRecord $record, bool $use_long = false, string $size = ''): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\RecordNameTextExtenderInterface::recordNameAppend()
     */
    public function recordNameAppend(GedcomRecord $record, bool $use_long = false, string $size = ''): string
    {
        if ($use_long && $record instanceof Individual) {
            return view($this->module()->name() . '::hooks/name-append', [
                'module_name'           =>  $this->module()->name(),
                'source_status_service' =>  $this->source_status_service,
                'individual'            =>  $record,
                'size_style'            =>  $size
            ]);
        }
        return '';
    }
}

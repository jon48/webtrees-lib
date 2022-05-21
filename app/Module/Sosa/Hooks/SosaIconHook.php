<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Sosa\Hooks;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\RecordNameTextExtenderInterface;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;

/**
 * Hook for displaying an icon next to Sosa ancestors' display name.
 */
class SosaIconHook implements RecordNameTextExtenderInterface
{
    private ModuleInterface $module;
    private SosaRecordsService $sosa_records_service;

    /**
     * Constructor for SosaIconHook
     *
     * @param ModuleInterface $module
     * @param SosaRecordsService $sosa_records_service
     */
    public function __construct(ModuleInterface $module, SosaRecordsService $sosa_records_service)
    {
        $this->module = $module;
        $this->sosa_records_service = $sosa_records_service;
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
        $current_user = Auth::check() ? Auth::user() : new DefaultUser();
        if (
            $record instanceof Individual &&
            $this->sosa_records_service->isSosa($record->tree(), $current_user, $record)
        ) {
            return view($this->module->name() . '::icons/sosa', [ 'size_style' => $size ]);
        }
        return '';
    }
}

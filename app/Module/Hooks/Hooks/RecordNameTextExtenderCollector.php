<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Hooks\Hooks;

use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector;
use MyArtJaub\Webtrees\Contracts\Hooks\RecordNameTextExtenderInterface;

/**
 * Hook collector for hooks implementing RecordNameTextExtenderInterface.
 * Used to extend the display name of gedcom records.
 *
 * @extends AbstractHookCollector<RecordNameTextExtenderInterface>
 */
class RecordNameTextExtenderCollector extends AbstractHookCollector implements RecordNameTextExtenderInterface
{
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::title()
     */
    public function title(): string
    {
        return I18N::translate('Text extender for records’ name');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::description()
     */
    public function description(): string
    {
        return I18N::translate('Extends the full name of GEDCOM records with additional text or icons.');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::hookInterface()
     */
    public function hookInterface(): string
    {
        return RecordNameTextExtenderInterface::class;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\RecordNameTextExtenderInterface::recordNamePrepend()
     */
    public function recordNamePrepend(GedcomRecord $record, bool $use_long = false, string $size = ''): string
    {
        return $this->hooks()
            ->map(fn(RecordNameTextExtenderInterface $hook) => $hook->recordNamePrepend($record, $use_long, $size))
            ->implode('');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\RecordNameTextExtenderInterface::recordNameAppend()
     */
    public function recordNameAppend(GedcomRecord $record, bool $use_long = false, string $size = ''): string
    {
        return $this->hooks()
            ->map(fn(RecordNameTextExtenderInterface $hook) => $hook->recordNameAppend($record, $use_long, $size))
            ->implode('');
    }
}

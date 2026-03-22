<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2026, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Hooks\Hooks;

use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector;
use MyArtJaub\Webtrees\Contracts\Hooks\SosaMissingDatatablesExtenderInterface;

/**
 * Hook collector for hooks implementing SosaMissingDatatablesExtenderInterface.
 * Used to extend the columns of missing ancestors datatables.
 *
 * @extends AbstractHookCollector<SosaMissingDatatablesExtenderInterface>
 */
class SosaMissingDatatablesExtenderCollector extends AbstractHookCollector implements
    SosaMissingDatatablesExtenderInterface
{
    #[\Override]
    public function title(): string
    {
        return I18N::translate('Columns extender for tables of missing ancestors');
    }

    #[\Override]
    public function description(): string
    {
        return I18N::translate('Add additional columns to tables of missing ancestors');
    }

    #[\Override]
    public function hookInterface(): string
    {
        return SosaMissingDatatablesExtenderInterface::class;
    }

    #[\Override]
    public function sosaMissingColumns(iterable $records): array
    {
        $result = [];
        foreach ($this->hooks() as $hook) {
            $result += $hook->sosaMissingColumns($records);
        }
        return $result;
    }
}

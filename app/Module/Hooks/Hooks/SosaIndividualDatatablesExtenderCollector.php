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

use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector;
use MyArtJaub\Webtrees\Contracts\Hooks\SosaIndividualDatatablesExtenderInterface;

/**
 * Hook collector for hooks implementing SosaIndividualDatatablesExtenderInterface.
 * Used to extend the columns of ancestors datatables.
 *
 * @extends AbstractHookCollector<SosaIndividualDatatablesExtenderInterface>
 */
class SosaIndividualDatatablesExtenderCollector extends AbstractHookCollector implements
    SosaIndividualDatatablesExtenderInterface
{
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::title()
     */
    public function title(): string
    {
        return I18N::translate('Columns extender for tables of ancestors');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::description()
     */
    public function description(): string
    {
        return I18N::translate('Add additional columns to tables of ancestors');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::hookInterface()
     */
    public function hookInterface(): string
    {
        return SosaIndividualDatatablesExtenderInterface::class;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\SosaIndividualDatatablesExtenderInterface::sosaIndividualColumns()
     */
    public function sosaIndividualColumns(iterable $records): array
    {
        $result = [];
        foreach ($this->hooks() as $hook) {
            $result += $hook->sosaIndividualColumns($records);
        }
        return $result;
    }
}

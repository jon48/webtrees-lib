<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Hooks\Hooks;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector;
use MyArtJaub\Webtrees\Contracts\Hooks\NameAccordionExtenderInterface;

/**
 * Hook collector for hooks implementing NameAccordionExtenderInterface.
 * Used to extend the names accordion on the individual page.
 *
 * @extends AbstractHookCollector<NameAccordionExtenderInterface>
 */
class NameAccordionExtenderCollector extends AbstractHookCollector implements NameAccordionExtenderInterface
{
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::title()
     */
    public function title(): string
    {
        return I18N::translate('Individual names accordion extender');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::description()
     */
    public function description(): string
    {
        return I18N::translate('Extends the names accordion of on an individual’s page.');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::hookInterface()
     */
    public function hookInterface(): string
    {
        return NameAccordionExtenderInterface::class;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\NameAccordionExtenderInterface::accordionCard()
     */
    public function accordionCard(Individual $individual): string
    {
        return $this->hooks()
            ->map(fn(NameAccordionExtenderInterface $hook) => $hook->accordionCard($individual))
            ->implode('');
    }
}

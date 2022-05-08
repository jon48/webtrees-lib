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
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector;
use MyArtJaub\Webtrees\Contracts\Hooks\FactSourceTextExtenderInterface;

/**
 * Hook collector for hooks implementing FactSourceTextExtenderInterface.
 * Used to extend the title of source citations.
 *
 * @extends AbstractHookCollector<FactSourceTextExtenderInterface>
 */
class FactSourceTextExtenderCollector extends AbstractHookCollector implements FactSourceTextExtenderInterface
{
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::title()
     */
    public function title(): string
    {
        return I18N::translate('Text extender for source citations’ title');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::description()
     */
    public function description(): string
    {
        return I18N::translate('Extends the title of source citations with additional text or icons.');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::hookInterface()
     */
    public function hookInterface(): string
    {
        return FactSourceTextExtenderInterface::class;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\FactSourceTextExtenderInterface::factSourcePrepend()
     */
    public function factSourcePrepend(Tree $tree, $fact): string
    {
        return $this->hooks()
            ->map(
                fn(FactSourceTextExtenderInterface $hook) =>
                    $hook->factSourcePrepend($tree, $fact)
            )->implode('');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\FactSourceTextExtenderInterface::factSourceAppend()
     */
    public function factSourceAppend(Tree $tree, $fact): string
    {
        return $this->hooks()
            ->map(
                fn(FactSourceTextExtenderInterface $hook) =>
                    $hook->factSourcePrepend($tree, $fact)
            )->implode('');
    }
}

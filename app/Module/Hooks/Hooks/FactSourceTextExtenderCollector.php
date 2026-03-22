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
    #[\Override]
    public function title(): string
    {
        return I18N::translate('Text extender for source citations’ title');
    }

    #[\Override]
    public function description(): string
    {
        return I18N::translate('Extends the title of source citations with additional text or icons.');
    }

    #[\Override]
    public function hookInterface(): string
    {
        return FactSourceTextExtenderInterface::class;
    }

    #[\Override]
    public function factSourcePrepend(Tree $tree, $fact): string
    {
        return $this->hooks()
            ->map(
                fn(FactSourceTextExtenderInterface $hook) =>
                    $hook->factSourcePrepend($tree, $fact)
            )->implode('');
    }

    #[\Override]
    public function factSourceAppend(Tree $tree, $fact): string
    {
        return $this->hooks()
            ->map(
                fn(FactSourceTextExtenderInterface $hook) =>
                    $hook->factSourcePrepend($tree, $fact)
            )->implode('');
    }
}

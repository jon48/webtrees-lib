<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Hooks\Hooks;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector;
use MyArtJaub\Webtrees\Contracts\Hooks\CustomSimpleTagEditorInterface;

/**
 * Hook collector for hooks implementing CustomSimpleTagEditorInterface.
 * Used to edit a custom tag.
 *
 * @extends AbstractHookCollector<CustomSimpleTagEditorInterface>
 */
class CustomSimpleTagEditorCollector extends AbstractHookCollector implements CustomSimpleTagEditorInterface
{
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::title()
     */
    public function title(): string
    {
        return I18N::translate('Custom simple tag editor');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::description()
     */
    public function description(): string
    {
        return I18N::translate('Allows for the edition of simple custom tags.');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\Hooks\AbstractHookCollector::hookInterface()
     */
    public function hookInterface(): string
    {
        return CustomSimpleTagEditorInterface::class;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\CustomSimpleTagEditorInterface::addExpectedTags()
     */
    public function addExpectedTags(array $expected_tags): array
    {
        return $this->hooks()->reduce(
            fn(array $tags, CustomSimpleTagEditorInterface $hook): array => $hook->addExpectedTags($tags),
            $expected_tags
        );
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\CustomSimpleTagEditorInterface::getLabel()
     */
    public function getLabel(string $tag): string
    {
        return $this->hooks()
            ->map(fn(CustomSimpleTagEditorInterface $hook) => $hook->getLabel($tag))
            ->first(fn(string $label) => $label !== '') ?? '';
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\CustomSimpleTagEditorInterface::editForm()
     */
    public function editForm(string $tag, string $id, string $name, string $value, Tree $tree): string
    {
        return $this->hooks()
            ->map(fn(CustomSimpleTagEditorInterface $hook): string => $hook->editForm($tag, $id, $name, $value, $tree))
            ->implode('');
    }
}

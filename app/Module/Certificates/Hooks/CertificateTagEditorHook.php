<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Hooks;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\ModuleInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\CustomSimpleTagEditorInterface;

/**
 * Hook for displaying the source status for a record next to its name.
 */
class CertificateTagEditorHook implements CustomSimpleTagEditorInterface
{
    private ModuleInterface $module;

    /**
     * Constructor for CertificateTagEditorHook
     *
     * @param ModuleInterface $module
     */
    public function __construct(ModuleInterface $module)
    {
        $this->module = $module;
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
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\CustomSimpleTagEditorInterface::addExpectedTags()
     */
    public function addExpectedTags(array $expected_tags): array
    {
        return array_merge_recursive($expected_tags, [
            'SOUR' => [ '_ACT' ]
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\CustomSimpleTagEditorInterface::getLabel()
     */
    public function getLabel(string $tag): string
    {
        if (substr($tag, -4) === '_ACT') {
            return Registry::elementFactory()->make(substr('INDI:SOUR:_ACT', 0, -strlen($tag)) . $tag)->label();
        }
        return '';
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\CustomSimpleTagEditorInterface::editForm()
     */
    public function editForm(string $tag, string $id, string $name, string $value, Tree $tree): string
    {
        if (substr($tag, -4) === '_ACT') {
            return Registry::elementFactory()->make(substr('INDI:SOUR:_ACT', 0, -strlen($tag)) . $tag)
                ->edit($id, $name, $value, $tree);
        }
        return '';
    }
}

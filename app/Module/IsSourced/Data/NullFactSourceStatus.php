<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage IsSourced
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2022, Jonathan Jaubart
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\IsSourced\Data;

/**
 * Null class for FactSourceStatus, base class before any calculation.
 */
class NullFactSourceStatus extends FactSourceStatus
{
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\IsSourced\Data\SourceStatus::isSet()
     */
    public function isSet(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\IsSourced\Data\FactSourceStatus::combineWith()
     */
    public function combineWith(SourceStatus $other)
    {
        return $other;
    }
}

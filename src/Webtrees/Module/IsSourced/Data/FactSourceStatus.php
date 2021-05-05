<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage IsSourced
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\IsSourced\Data;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;

/**
 * Data class for holding source status for facts.
 */
class FactSourceStatus extends SourceStatus
{
    /**
     * @var boolean $has_date
     */
    private $has_date = false;

    /**
     * @var boolean $has_precise_date
     */
    private $has_precise_date = false;

    /**
     * @var boolean $has_source_date
     */
    private $has_source_date = false;

    /**
     * @var boolean $source_date_match
     */
    private $source_date_match = false;

    /**
     * Return whether the fact is dated.
     *
     * @return bool
     */
    public function factHasDate(): bool
    {
        return $this->has_date;
    }

    /**
     * Set whether the fact is dated.
     *
     * @param bool $has_date
     * @return $this
     */
    public function setFactHasDate(bool $has_date): self
    {
        $this->has_date = $has_date;
        return $this;
    }

    /**
     * Combinate whether the fact is dated with the previous status.
     *
     * @param bool $has_date
     * @return $this
     */
    public function addFactHasDate(bool $has_date): self
    {
        $this->has_date = $this->has_date || $has_date;
        return $this;
    }

    /**
     * Return whether the fact is dated with a precise day.
     * Any date modifier will be considered as not precise.
     * A month or year will be considered as not precise.
     *
     * @return bool
     */
    public function factHasPreciseDate(): bool
    {
        return $this->has_date && $this->has_precise_date;
    }

    /**
     * Set whather the fact is dated with a precise day.
     *
     * @param bool $has_precise_date
     * @return $this
     */
    public function setFactHasPreciseDate(bool $has_precise_date): self
    {
        $this->has_precise_date = $has_precise_date;
        return $this;
    }

    /**
     * Combine whether the fact is dated with a precise day.
     *
     * @param bool $has_precise_date
     * @return $this
     */
    public function addFactHasPreciseDate(bool $has_precise_date): self
    {
        $this->has_precise_date = $this->has_precise_date || $has_precise_date;
        return $this;
    }

    /**
     * Return whether the source citation is dated.
     *
     * @return bool
     */
    public function sourceHasDate(): bool
    {
        return $this->has_source_date;
    }

    /**
     * Set whether the source citation is dated.
     *
     * @param bool $has_source_date
     * @return $this
     */
    public function setSourceHasDate(bool $has_source_date): self
    {
        $this->has_source_date = $has_source_date;
        return $this;
    }

    /**
     * Combine whether the source citation is dated with the previous status.
     *
     * @param bool $has_source_date
     * @return $this
     */
    public function addSourceHasDate(bool $has_source_date): self
    {
        $this->has_source_date = $this->has_source_date || $has_source_date;
        return $this;
    }

    /**
     * Return whether the source citation date is close to the fact date.
     *
     * @return bool
     */
    public function sourceMatchesFactDate(): bool
    {
        return $this->has_precise_date && $this->has_source_date && $this->source_date_match;
    }

    /**
     * Set whether the source citation date is close to the fact date.
     *
     * @param bool $source_date_match
     * @return $this
     */
    public function setSourceMatchesFactDate(bool $source_date_match): self
    {
        $this->source_date_match = $source_date_match;
        return $this;
    }

    /**
     * Combine whether the source citation date is close to the fact date with the previous status.
     *
     * @param bool $source_date_match
     * @return $this
     */
    public function addSourceMatchesFactDate(bool $source_date_match): self
    {
        $this->source_date_match = $this->source_date_match || $source_date_match;
        return $this;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\IsSourced\Data\SourceStatus::isFullySourced()
     */
    public function isFullySourced(): bool
    {
        return parent::isFullySourced() && $this->sourceMatchesFactDate();
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\IsSourced\Data\SourceStatus::label()
     */
    public function label(string $context): string
    {
        $context_label = Registry::elementFactory()->make($context)->label();

        if ($this->factHasPreciseDate()) {
            if ($this->hasSource()) {
                if ($this->hasSupportingDocument()) {
                    if ($this->sourceMatchesFactDate()) {
                        return I18N::translate('%s sourced with exact certificate', $context_label);
                    } else {
                        return I18N::translate('%s sourced with a certificate', $context_label);
                    }
                }

                if ($this->sourceMatchesFactDate()) {
                    return I18N::translate('%s precisely sourced', $context_label);
                }
                return I18N::translate('%s sourced', $context_label);
            }
            return I18N::translate('%s not sourced', $context_label);
        }

        if ($this->factHasDate()) {
            return I18N::translate('%s not precise', $context_label);
        }
        return I18N::translate('%s not found', $context_label);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\IsSourced\Data\SourceStatus::combineWith()
     */
    public function combineWith(SourceStatus $other)
    {
        if ($other instanceof FactSourceStatus) {
            $this->addFactHasDate($other->factHasDate());
            $this->addFactHasPreciseDate($other->factHasPreciseDate());
            $this->addSourceHasDate($other->sourceHasDate());
            $this->addSourceMatchesFactDate($other->sourceMatchesFactDate());
        }
        return parent::combineWith($other);
    }
}

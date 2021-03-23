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
 * Data class for holding source status
 */
class SourceStatus
{
    /**
     * @var boolean $source_exist
     */
    private $source_exist = false;

    /**
     * @var boolean $has_document
     */
    private $has_document = false;

    /**
     * Return whether the SourceStatus object contains relevant data.
     *
     * @return bool
     */
    public function isSet(): bool
    {
        return true;
    }

    /**
     * Returns whether the record contains a source.
     *
     * @return bool
     */
    public function hasSource(): bool
    {
        return $this->source_exist;
    }

    /**
     *  Set whether the record contains a source.
     *
     * @param bool $source_exist
     * @return self
     */
    public function setHasSource(bool $source_exist): self
    {
        $this->source_exist = $source_exist;
        return $this;
    }

    /**
     * Combine whether the record contains a source with the previous status.
     *
     * @param bool $source_exist
     * @return self
     */
    public function addHasSource(bool $source_exist): self
    {
        $this->source_exist = $this->source_exist || $source_exist;
        return $this;
    }

    /**
     * Return whether the source citation is supported by a document.
     * Uses the _ACT tag from the MyArtJaub Certificates module.
     *
     * @return bool
     */
    public function hasSupportingDocument(): bool
    {
        return $this->hasSource() && $this->has_document;
    }

    /**
     * Set whether the source citation is supported by a document.
     *
     * @param bool $has_document
     * @return self
     */
    public function setHasSupportingDocument(bool $has_document): self
    {
        $this->has_document = $has_document;
        return $this;
    }

    /**
     * Combine whether the source citation is supported by a document with the previous status.
     *
     * @param bool $has_document
     * @return self
     */
    public function addHasSupportingDocument(bool $has_document): self
    {
        $this->has_document = $this->has_document || $has_document;
        return $this;
    }

    /**
     * Check whether all possible criteria for defining a sourced element have been met.
     *
     * @return bool
     */
    public function isFullySourced(): bool
    {
        return $this->hasSupportingDocument();
    }

    /**
     * Get the label to display to describe the source status.
     *
     * @param string $context
     * @return string
     */
    public function label(string $context): string
    {
        $context_label = Registry::elementFactory()->make($context)->label();

        if (!$this->hasSource()) {
            return I18N::translate('%s not sourced', $context_label);
        }

        if ($this->hasSupportingDocument()) {
            return I18N::translate('%s sourced with a certificate', $context_label);
        }

        return I18N::translate('%s sourced', $context_label);
    }

    /**
     * Return an element combining properties of the current object with another SourceStatus.
     * Do not use the initial object anymore, it may not appropriately describe the status anymore.
     *
     * @param SourceStatus $other
     * @return self
     */
    public function combineWith(SourceStatus $other): self
    {
        $this->addHasSource($other->hasSource());
        $this->addHasSupportingDocument($other->hasSource());
        return $this;
    }
}

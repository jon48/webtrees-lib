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

namespace MyArtJaub\Webtrees\Module\IsSourced\Services;

use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Fact;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Individual;
use MyArtJaub\Webtrees\Module\IsSourced\Data\FactSourceStatus;
use MyArtJaub\Webtrees\Module\IsSourced\Data\NullFactSourceStatus;
use MyArtJaub\Webtrees\Module\IsSourced\Data\SourceStatus;

/**
 * Service for computing the status of sources for records and facts.
 */
class SourceStatusService
{

    /**
     * Maximum timespan between the date of a source and the date of the event to consider the source precise.
     * Arbitrally set to approximately a year around the event date.
     *
     * @var int DATE_PRECISION_MARGIN
     */
    private const DATE_PRECISION_MARGIN = 180;

    /**
     * Return the status of source citations for a fact.
     *
     * @param Fact $fact
     * @return FactSourceStatus
     */
    public function sourceStatusForFact(Fact $fact): FactSourceStatus
    {
        $source_status = new FactSourceStatus();

        $date = $fact->date();
        $source_status
            ->setFactHasDate($date->isOK())
            ->setFactHasPreciseDate($date->qual1 === '' && $date->minimumJulianDay() === $date->maximumJulianDay());

        foreach ($fact->getCitations() as $citation) {
            $source_status
                ->setHasSource(true)
                ->addHasSupportingDocument(preg_match('/\n3 _ACT (?:.*)/', $citation) === 1);

            preg_match_all("/\n3 DATA(?:\n[4-9] .*)*\n4 DATE (.*)/", $citation, $date_matches, PREG_SET_ORDER);
            foreach ($date_matches as $date_match) {
                $source_date = new Date($date_match[1]);
                $source_status
                    ->addSourceHasDate($source_date->isOK())
                    ->addSourceMatchesFactDate($date->isOK() && $source_date->isOK()
                        && abs($source_date->julianDay() - $date->julianDay()) < self::DATE_PRECISION_MARGIN);
            }

            if ($source_status->isFullySourced()) {
                return $source_status;
            }
        }

        return $source_status;
    }

    /**
     * Return the status of sources for a Gedcom record.
     *
     * @param GedcomRecord $record
     * @return SourceStatus
     */
    public function sourceStatusForRecord(GedcomRecord $record): SourceStatus
    {
        $source_status = new SourceStatus();

        foreach ($record->facts(['SOUR']) as $source) {
            $source_status
                ->setHasSource(true)
                ->addHasSupportingDocument($source->attribute('_ACT') !== '');

            if ($source_status->isFullySourced()) {
                return $source_status;
            }
        }

        return $source_status;
    }

    /**
     * Return the status of source citations for a list of fact types associated with a record.
     *
     * @param GedcomRecord $record
     * @param array $tags
     * @return FactSourceStatus
     */
    public function sourceStatusForFactsWithTags(GedcomRecord $record, array $tags): FactSourceStatus
    {
        $source_status = new NullFactSourceStatus();

        foreach ($record->facts($tags) as $fact) {
            $source_status = $source_status->combineWith($this->sourceStatusForFact($fact));
            if ($source_status->isFullySourced()) {
                return $source_status;
            }
        }

        return $source_status;
    }

    /**
     * Return the status of source citations for an individual's birth events.
     *
     * @param Individual $individual
     * @return FactSourceStatus
     */
    public function sourceStatusForBirth(Individual $individual): FactSourceStatus
    {
        return $this->sourceStatusForFactsWithTags($individual, Gedcom::BIRTH_EVENTS);
    }

    /**
     * Return the status of source citations for an individual's death events.
     *
     * @param Individual $individual
     * @return FactSourceStatus
     */
    public function sourceStatusForDeath(Individual $individual): FactSourceStatus
    {
        return $this->sourceStatusForFactsWithTags($individual, Gedcom::DEATH_EVENTS);
    }

    /**
     * Return the status of source citations for a family's marriage events.
     *
     * @param Family $family
     * @return FactSourceStatus
     */
    public function sourceStatusForMarriage(Family $family): FactSourceStatus
    {
        $marr_events = array_merge(Gedcom::MARRIAGE_EVENTS, ['MARC', 'MARL', 'MARS']);
        return $this->sourceStatusForFactsWithTags($family, $marr_events);
    }
}

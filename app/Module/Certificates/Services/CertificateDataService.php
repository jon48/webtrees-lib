<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Services;

use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Source;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use Generator;

/**
 * Service for accessing genealogical data linked to a certificate file.
 */
class CertificateDataService
{
    /**
     * Find individuals linked to a certificate.
     *
     * {@internal Ideally, the underscore should be escaped in the WHERE clause,
     * but does not work with Sqlite if no default escape has been defined}
     *
     * @param Certificate $certificate
     * @return Collection<\Fisharebest\Webtrees\Individual>
     */
    public function linkedIndividuals(Certificate $certificate): Collection
    {
        $tree = $certificate->tree();
        return DB::table('individuals')
            ->where('i_file', '=', $tree->id())
            ->where('i_gedcom', 'like', '% _ACT ' . $this->escapedSqlPath($certificate) . '%')
            ->select(['individuals.*'])
            ->get()
            ->map(Registry::individualFactory()->mapper($tree))
            ->filter(GedcomRecord::accessFilter());
    }

    /**
     * Find families linked to a certificate.
     *
     * {@internal Ideally, the underscore should be escaped in the WHERE clause,
     * but does not work with Sqlite if no default escape has been defined}
     *
     * @param Certificate $certificate
     * @return Collection<\Fisharebest\Webtrees\Family>
     */
    public function linkedFamilies(Certificate $certificate): Collection
    {
        $tree = $certificate->tree();
        return DB::table('families')
            ->where('f_file', '=', $tree->id())
            ->where('f_gedcom', 'like', '% _ACT ' . $this->escapedSqlPath($certificate) . '%')
            ->select(['families.*'])
            ->get()
            ->map(Registry::familyFactory()->mapper($tree))
            ->filter(GedcomRecord::accessFilter());
    }

    /**
     * Find media objects linked to a certificate.
     *
     * {@internal Ideally, the underscore should be escaped in the WHERE clause,
     * but does not work with Sqlite if no default escape has been defined}
     *
     * @param Certificate $certificate
     * @return Collection<\Fisharebest\Webtrees\Media>
     */
    public function linkedMedias(Certificate $certificate): Collection
    {
        $tree = $certificate->tree();
        return DB::table('media')
            ->where('m_file', '=', $tree->id())
            ->where('m_gedcom', 'like', '% _ACT ' . $this->escapedSqlPath($certificate) . '%')
            ->select(['media.*'])
            ->get()
            ->map(Registry::mediaFactory()->mapper($tree))
            ->filter(GedcomRecord::accessFilter());
    }

    /**
     * Find notes linked to a certificate.
     *
     * {@internal Ideally, the underscore should be escaped in the WHERE clause,
     * but does not work with Sqlite if no default escape has been defined}
     *
     * @param Certificate $certificate
     * @return Collection<\Fisharebest\Webtrees\Note>
     */
    public function linkedNotes(Certificate $certificate): Collection
    {
        $tree = $certificate->tree();
        return DB::table('other')
            ->where('o_file', '=', $tree->id())
            ->where('o_type', '=', 'NOTE')
            ->where('o_gedcom', 'like', '% _ACT ' . $this->escapedSqlPath($certificate) . '%')
            ->select(['other.*'])
            ->get()
            ->map(Registry::noteFactory()->mapper($tree))
            ->filter(GedcomRecord::accessFilter());
    }

    /**
     * Return an escaped string to be used in SQL LIKE comparisons.
     * This would not work well for Sqlite, if the escape character is not defined.
     *
     * @param Certificate $certificate
     * @return string
     */
    protected function escapedSqlPath(Certificate $certificate): string
    {
        return str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $certificate->gedcomPath());
    }

    /**
     * Find a source linked to a citation where a certificate file is referenced.
     *
     * @param Certificate $certificate
     * @return Source|NULL
     */
    public function oneLinkedSource(Certificate $certificate): ?Source
    {
        $regex_query = preg_quote($certificate->gedcomPath(), '/');
        $regex_pattern = '/[1-9] SOUR @(' . Gedcom::REGEX_XREF . ')@(?:\n[2-9]\s(?:(?!SOUR)\w+)\s.*)*\n[2-9] _ACT ' . $regex_query . '/i'; //phpcs:ignore Generic.Files.LineLength.TooLong

        foreach ($this->linkedRecordsLists($certificate) as $linked_records) {
            foreach ($linked_records as $gedcom_record) {
                foreach ($gedcom_record->facts() as $fact) {
                    if (preg_match_all($regex_pattern, $fact->gedcom(), $matches, PREG_SET_ORDER) >= 1) {
                        foreach ($matches as $match) {
                            $source = Registry::sourceFactory()->make($match[1], $certificate->tree());
                            if ($source !== null && $source->canShowName()) {
                                return $source;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Yield lists of gedcom records linked to a certificate.
     *
     * @param Certificate $certificate
     * @return Generator<int, Collection<GedcomRecord>, mixed, void>
     * @psalm-suppress InvalidReturnType
     */
    protected function linkedRecordsLists(Certificate $certificate): Generator
    {
        yield $this->linkedIndividuals($certificate);
        yield $this->linkedFamilies($certificate);
        yield $this->linkedMedias($certificate);
        yield $this->linkedNotes($certificate);
    }
}

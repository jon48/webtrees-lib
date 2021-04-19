<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Service for accessing data in the Places Mapping Reference Table.
 */
class PlacesReferenceTableService
{
    /**
     * Mapping format placeholder tags => table column names
     * @var array<string, string>
     */
    private const COLUMN_MAPPING = [
        'name'  =>  'majgr_place_name',
        'id'    =>  'majgr_place_admin_id',
        'zip'   =>  'majgr_place_zip',
        'gov'   =>  'majgr_place_gov_id',
        'mls'   =>  'majgr_place_mls_id'
    ];

    /**
     * Get the formatted target mapping value of a place defined by a source format.
     *
     * @param string $source
     * @param string $source_format
     * @param string $target_format
     * @return string|NULL
     */
    public function targetId(string $source, string $source_format, string $target_format): ?string
    {
        // Extract parts for the WHERE clause
        $source_format = str_replace(['{', '}'], ['{#', '#}'], $source_format);
        $source_parts = preg_split('/[{}]/i', $source_format);
        if ($source_parts === false) {
            return null;
        }
        $source_parts = array_map(function (string $part): string {
            if (preg_match('/^#([^#]+)#$/i', $part, $column_id) === 1) {
                return $this->columnName($column_id[1]);
            }
            return $this->sanitizeString(str_replace(['?', '*'], ['_', '%'], $part));
        }, array_filter($source_parts));
        $source_parts[] = "'%'";
        $concat_statement = 'CONCAT(' . implode(', ', $source_parts) . ')';

        // Extract columns used in target
        $columns = [];
        if (preg_match_all('/{(.*?)}/i', $target_format, $columns_select) === 1) {
            $columns = array_unique(array_filter(array_map(fn($id) => $this->columnName($id), $columns_select[1])));
        }

        // Get the mapping
        $rows = DB::table('maj_geodata_ref')  //DB::table('maj_geodata_ref')
            ->select($columns)
            ->whereRaw($this->sanitizeString($source) . " LIKE " . $concat_statement)
            ->get();

        // Format the output ID
        if ($rows->count() === 0) {
            return null;
        }

        $mapping = (array) $rows->first();
        if (count($columns_select) === 0) {
            return $target_format;
        }

        return str_replace(
            array_map(fn($tag) => '{' . $tag . '}', $columns_select[1]),
            array_map(fn($tag) => $mapping[$this->columnName($tag)] ?? '', $columns_select[1]),
            $target_format
        );
    }

    /**
     * Get the column name for a format placeholder tag
     *
     * @param string $placeholder
     * @return string
     */
    private function columnName(string $placeholder): string
    {
        return self::COLUMN_MAPPING[$placeholder] ?? '';
    }

    /**
     * Get the placeholder tag for a column_name
     *
     * @param string $column_name
     * @return string
     */
    private function tagName(string $column_name): string
    {
        return array_flip(self::COLUMN_MAPPING)[$column_name] ?? '';
    }

    /**
     * Sanitize string for use in a SQL query.
     *
     * @param string $string
     * @return string
     */
    private function sanitizeString(string $string): string
    {
        return DB::connection()->getPdo()->quote($string);
    }
}

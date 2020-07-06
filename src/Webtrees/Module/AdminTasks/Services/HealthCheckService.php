<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\AdminTasks\Services;

use Fisharebest\Webtrees\Carbon;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

/**
 * Service for retrieving data for the Healthcheck task
 */
class HealthCheckService
{
    /**
     * Returns a query collating all gedcom records, for use in other queries
     * 
     * @param Tree $tree
     * @return Builder
     */
    private function allGedcomRecords(Tree $tree) : Builder
    {
        return DB::table('individuals')->select(DB::raw("'indi' AS ged_type"), 'i_id AS ged_id')->where('i_file', '=', $tree->id())
            ->unionAll(DB::table('families')->select(DB::raw("'fam' AS ged_type"), 'f_id AS ged_id')->where('f_file', '=', $tree->id()))
            ->unionAll(DB::table('sources')->select(DB::raw("'sour' AS ged_type"), 's_id AS ged_id')->where('s_file', '=', $tree->id()))
            ->unionAll(DB::table('media')->select(DB::raw("'media' AS ged_type"), 'm_id AS ged_id')->where('m_file', '=', $tree->id()))
            ->unionAll(DB::table('other')->select(DB::raw('LOWER(o_type) AS ged_type'), 'o_id AS ged_id')->where('o_file', '=', $tree->id()));
    }
    
    /**
     * Returns the count of gedcom records by type in a Tree, as a keyed Collection.
     * 
     * Collection output:
     *      - Key : gedcom record type
     *      - Value: count of records
     * 
     * @param Tree $tree
     * @return Collection
     */
    public function countByRecordType(Tree $tree) : Collection
    {
        return DB::query()
            ->fromSub($this->allGedcomRecords($tree), 'gedrecords')
            ->select('ged_type', new Expression('COUNT(ged_id) AS total'))
            ->groupBy('ged_type')
            ->pluck('total', 'ged_type');
    }
    
    /**
     * Returns the count of gedcom records changes by type in a Tree across a number of days, as a keyed Collection.
     *
     * Collection output:
     *      - Key : gedcom record type
     *      - Value: count of changes
     *
     * @param Tree $tree
     * @return Collection
     */
    public function changesByRecordType(Tree $tree, int $nb_days) : Collection
    {
        return DB::table('change')
            ->joinSub($this->allGedcomRecords($tree), 'gedrecords', function (JoinClause $join) use($tree) {
                $join->on('change.xref', '=', 'gedrecords.ged_id')
                    ->where('change.gedcom_id', '=', $tree->id());
            })
            ->select('ged_type AS type', new Expression('COUNT(change_id) AS count'))
            ->where('change.status', '', 'accepted')
            ->where('change.change_time', '>=', Carbon::now()->subDays($nb_days))
            ->groupBy('ged_type')
            ->pluck('total', 'ged_type');
    }
    
    /**
     * Return the error logs associated with a tree across a number of days, grouped by error message, as a Collection.
     * 
     * Collection output:
     *      - Value: stdClass object
     *          - log message:  Error log message
     *          - type:         'site' if no associated Tree, the Tree ID otherwise
     *          - nblogs:       The number of occurence of the same error message
     *          - lastoccurred: Date/time of the last occurence of the error message
     * 
     * @param Tree $tree
     * @param int $nb_days
     * @return Collection
     */
    public function errorLogs(Tree $tree, int $nb_days) : Collection
    {
        return DB::table('log')
            ->select(
                'log_message',
                new Expression("IFNULL(gedcom_id, 'site') as type"),
                new Expression('COUNT(log_id) AS nblogs'),
                new Expression('MAX(log_time) AS lastoccurred'))
            ->where('log_type', '=', 'error')
            ->where(function(Builder $query) use ($tree) {
                $query->where('gedcom_id', '=', $tree->id())
                    ->orWhereNull('gedcom_id');
            })
            ->where('log_time', '>=', Carbon::now()->subDays($nb_days))
            ->groupBy('log_message', 'gedcom_id')
            ->orderByDesc('lastoccurred')
            ->get()
            ;
    }
}


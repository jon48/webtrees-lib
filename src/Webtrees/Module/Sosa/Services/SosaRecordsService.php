<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Sosa\Services;

use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;

/**
 * Service for CRUD operations on Sosa records
 *
 */
class SosaRecordsService
{
    /**
     * Maximum number of generation the database is able to hold.
     * @var int MAX_DB_GENERATIONS
     */
    public const MAX_DB_GENERATIONS = 64;
    
    /**
     * Calculate the generation of a sosa
     * Sosa 1 is of generation 1.
     *
     * @param int $sosa
     * @return int
     */
    public function generation(int $sosa) : int
    {
        return (int) log($sosa, 2) + 1;
    }
    
    /**
     * Returns all Sosa numbers associated to an Individual
     * 
     * @param Tree $tree
     * @param User $user
     * @param Individual $indi
     * @return Collection
     */
    public function getSosaNumbers(Tree $tree, User $user, Individual $indi) : Collection
    {
        return DB::table('maj_sosa')
            ->select(['majs_sosa', 'majs_gen'])
            ->where('majs_gedcom_id', '=', $tree->id())
            ->where('majs_user_id', '=', $user->id())
            ->where('majs_i_id', '=', $indi->xref())
            ->get()->pluck('majs_gen', 'majs_sosa');
    }
    
    /**
     * Remove all Sosa entries related to the gedcom file and user
     * 
     * @param Tree $tree
     * @param User $user
     */
    public function deleteAll(Tree $tree, User $user) : void
    {
        DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $tree->id())
            ->where('majs_user_id', '=', $user->id())
            ->delete();
    }
    
    /**
     * 
     * @param Tree $tree
     * @param User $user
     * @param int $sosa
     */
    public function deleteAncestorsFrom(Tree $tree, User $user, int $sosa) : void
    {
        DB::table('maj_sosa')
            ->where('majs_gedcom_id', '=', $tree->id())
            ->where('majs_user_id', '=', $user->id())
            ->where('majs_sosa', '>=', $sosa)
            ->whereRaw(
                'FLOOR(majs_sosa / (POW(2, (majs_gen - :gen)))) = :sosa',
                ['gen' => $this->generation($sosa), 'sosa' => $sosa])
            ->delete();
    }
    
    /**
     * Insert (or update if already existing) a list of Sosa individuals
     * 
     * @param Tree $tree
     * @param User $user
     * @param array $sosa_records
     */
    public function insertOrUpdate(Tree $tree, User $user, array $sosa_records) {
        $mass_update = DB::connection()->getDriverName() === 'mysql';
        
        $bindings_placeholders = $bindings_values = [];
        foreach($sosa_records as $i => $row) {
            $gen = $this->generation($row['sosa']);
            if($gen <=  self::MAX_DB_GENERATIONS) {
                if($mass_update) {
                    $bindings_placeholders[] = '(:tree_id'.$i.', :user_id'.$i.', :sosa'.$i.','.
                        ' :indi_id'.$i.', :gen'.$i.', :byear'.$i.', :byearest'.$i.', :dyear'.$i.', :dyearest'.$i.')';
                    $bindings_values = array_merge(
                        $bindings_values,
                        [
                            'tree_id'.$i => $tree->id(),
                            'user_id'.$i => $user->id(),
                            'sosa'.$i => $row['sosa'],
                            'indi_id'.$i => $row['indi'],
                            'gen'.$i => $gen,
                            'byear'.$i => $row['birth_year'],
                            'byearest'.$i => $row['birth_year_est'],
                            'dyear'.$i => $row['death_year'],
                            'dyearest'.$i => $row['death_year_est']
                        ]);
                } else {
                    DB::table('maj_sosa')->updateOrInsert(
                        [ 'majs_gedcom_id' => $tree->id(), 'majs_user_id' => $user->id(), 'majs_sosa' => $row['sosa']],
                        [
                            'majs_i_id' => $row['indi'],
                            'majs_gen' => $gen,
                            'majs_birth_year' => $row['birth_year'],
                            'majs_birth_year_est' => $row['birth_year_est'],
                            'majs_death_year' => $row['death_year'],
                            'majs_death_year_est' => $row['death_year_est']
                        ]);
                }
            }
        }
        
        if($mass_update) {
            DB::connection()->statement(
                'INSERT INTO `' . DB::connection()->getTablePrefix() . 'maj_sosa`' .
                ' (majs_gedcom_id, majs_user_id, majs_sosa,' .
                '   majs_i_id, majs_gen, majs_birth_year, majs_birth_year_est, majs_death_year, majs_death_year_est)' .
                ' VALUES ' . implode(',', $bindings_placeholders) .
                ' ON DUPLICATE KEY UPDATE majs_i_id = VALUES(majs_i_id), majs_gen = VALUES(majs_gen),' .
                '   majs_birth_year = VALUES(majs_birth_year), majs_birth_year_est = VALUES(majs_birth_year_est),' . 
                '   majs_death_year = VALUES(majs_death_year), majs_death_year_est = VALUES(majs_death_year_est)',
                $bindings_values);
        }
    }
    
}

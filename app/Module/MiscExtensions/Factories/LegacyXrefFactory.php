<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage MiscExtensions
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\MiscExtensions\Factories;

use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Factories\XrefFactory;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Xref generator similar to webtrees 1.0.
 */
class LegacyXrefFactory extends XrefFactory
{
    protected const TYPE_TO_PREFIX = [
        'INDI' => 'I',
        'FAM'  => 'F',
        'OBJE' => 'M',
        'NOTE' => 'N',
        'SOUR' => 'S',
        'REPO' => 'R',
    ];

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Factories\XrefFactory::make()
     */
    public function make(string $record_type): string
    {
        $prefix = static::TYPE_TO_PREFIX[$record_type] ?? 'X';

        return $this->generate($prefix, '');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Factories\XrefFactory::generate()
     */
    protected function generate($prefix, $suffix): string
    {
        $tree = app(Tree::class);
        if (!$tree instanceof Tree) {
            return parent::generate($prefix, $suffix);
        }

        $setting_name = 'MAJ_MISC_XREF_NEXT_' . $prefix;
        // Lock the row, so that only one new XREF may be generated at a time.
        $num = (int) DB::table('gedcom_setting')
            ->where('gedcom_id', '=', $tree->id())
            ->where('setting_name', '=', $setting_name)
            ->lockForUpdate()
            ->value('setting_value');

        $increment = 1.0;
        do {
            $num += (int) $increment;

            // This exponential increment allows us to scan over large blocks of
            // existing data in a reasonable time.
            $increment *= 1.01;

            $xref = $prefix . $num . $suffix;

            // Records may already exist with this sequence number.
            $already_used =
                DB::table('individuals')->where('i_file', '=', $tree->id())->where('i_id', '=', $xref)->exists() ||
                DB::table('families')->where('f_file', '=', $tree->id())->where('f_id', '=', $xref)->exists() ||
                DB::table('sources')->where('s_file', '=', $tree->id())->where('s_id', '=', $xref)->exists() ||
                DB::table('media')->where('m_file', '=', $tree->id())->where('m_id', '=', $xref)->exists() ||
                DB::table('other')->where('o_file', '=', $tree->id())->where('o_id', '=', $xref)->exists() ||
                DB::table('change')->where('gedcom_id', '=', $tree->id())->where('xref', '=', $xref)->exists();
        } while ($already_used);

        $tree->setPreference($setting_name, (string) $num);

        return $xref;
    }
}

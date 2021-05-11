<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Hooks\Schema;

use Fisharebest\Webtrees\Schema\MigrationInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

/**
 * Upgrade the database schema from version 1 to version 2.
 */
class Migration1 implements MigrationInterface
{

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Schema\MigrationInterface::upgrade()
     */
    public function upgrade(): void
    {
        if (DB::schema()->hasTable('maj_hooks')) {
            DB::schema()->drop('maj_hooks');
        }

        DB::schema()->create('maj_hook_order', static function (Blueprint $table): void {
            $table->string('majho_module_name', 32);
            $table->string('majho_hook_name', 64);
            $table->integer('majho_hook_order')->nullable();

            $table->primary(['majho_module_name', 'majho_hook_name']);
        });
    }
}

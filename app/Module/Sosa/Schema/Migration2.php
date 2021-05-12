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

namespace MyArtJaub\Webtrees\Module\Sosa\Schema;

use Fisharebest\Webtrees\Schema\MigrationInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

/**
 * Upgrade the database schema from version 2 to version 3.
 */
class Migration2 implements MigrationInterface
{

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Schema\MigrationInterface::upgrade()
     */
    public function upgrade(): void
    {

        // Clean up previous sosa table if it exists
        DB::schema()->dropIfExists('maj_sosa');

        DB::schema()->create('maj_sosa', static function (Blueprint $table): void {

            $table->integer('majs_gedcom_id');
            $table->integer('majs_user_id')->default(-1);
            $table->bigInteger('majs_sosa')->unsigned(); // Allow to calculate sosa on 64 generations
            $table->string('majs_i_id', 20);
            $table->tinyInteger('majs_gen')->nullable();
            $table->smallInteger('majs_birth_year')->nullable();
            $table->smallInteger('majs_birth_year_est')->nullable();
            $table->smallInteger('majs_death_year')->nullable();
            $table->smallInteger('majs_death_year_est')->nullable();

            $table->primary(['majs_gedcom_id', 'majs_user_id', 'majs_sosa']);

            $table->index(['majs_gedcom_id', 'majs_user_id']);
            $table->index(['majs_gedcom_id', 'majs_user_id', 'majs_i_id']);
            $table->index(['majs_gedcom_id', 'majs_user_id', 'majs_gen']);

            $table->foreign('majs_gedcom_id')->references('gedcom_id')->on('gedcom')->onDelete('cascade');
            $table->foreign('majs_user_id')->references('user_id')->on('user')->onDelete('cascade');
        });
    }
}

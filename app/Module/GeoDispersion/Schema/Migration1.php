<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Schema;

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
        $in_transaction = DB::connection()->getPdo()->inTransaction();

        DB::schema()->create('maj_geodisp_views', static function (Blueprint $table): void {
            $table->integer('majgv_id')->autoIncrement();
            $table->integer('majgv_gedcom_id')->index();
            $table->string('majgv_view_class', 255);
            $table->enum('majgv_status', ['enabled', 'disabled'])->default('enabled');
            $table->string('majgv_descr', 248);
            $table->string('majgv_analysis', 255);
            $table->tinyInteger('majgv_place_depth')->default(1);
            $table->tinyInteger('majgv_top_places')->default(0);
            $table->json('majgv_colors')->nullable();

            $table->foreign('majgv_gedcom_id')->references('gedcom_id')->on('gedcom')->onDelete('cascade');
        });

        DB::schema()->create('maj_geodisp_mapviews', static function (Blueprint $table): void {
            $table->integer('majgm_id')->autoIncrement();
            $table->integer('majgm_majgv_id')->index();
            $table->string('majgm_map_id', 127);
            $table->string('majgm_mapper', 255);
            $table->string('majgm_feature_prop', 31);
            $table->json('majgm_config')->nullable();

            $table->foreign('majgm_majgv_id')->references('majgv_id')->on('maj_geodisp_views')->onDelete('cascade');
        });

        if ($in_transaction && !DB::connection()->getPdo()->inTransaction()) {
            DB::connection()->beginTransaction();
        }
    }
}

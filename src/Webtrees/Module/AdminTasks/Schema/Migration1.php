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

namespace MyArtJaub\Webtrees\Module\AdminTasks\Schema;

use Fisharebest\Webtrees\Schema\MigrationInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Fisharebest\Webtrees\Carbon;

/**
 * Upgrade the database schema from version 1 (webtrees 1.0) to version 2 (webtrees 2.0).
 */
class Migration1 implements MigrationInterface
{
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Schema\MigrationInterface::upgrade()
     */
    public function upgrade(): void
    {
        // Clean up previous admin tasks table if it exists
        DB::schema()->dropIfExists('maj_admintasks');
        
        DB::schema()->create('maj_admintasks', static function (Blueprint $table): void {

            $table->increments('majat_id');
            $table->string('majat_task_id', 32)->unique()->nullable(false);
            $table->enum('majat_status', ['enabled', 'disabled'])->nullable(false)->default('disabled');
            $table->dateTime('majat_last_run')->nullable(false)->default(Carbon::createFromTimestampUTC(0));
            $table->boolean('majat_last_result')->nullable(false)->default(true);
            $table->integer('majat_frequency')->nullable(false)->default(10080);
            $table->smallInteger('majat_nb_occur')->nullable(false)->default(0);
            $table->boolean('majat_running')->nullable(false)->default(false);
        });
    }
}

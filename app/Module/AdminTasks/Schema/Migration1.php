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

use Carbon\Carbon;
use Fisharebest\Webtrees\Schema\MigrationInterface;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;
use Fisharebest\Webtrees\Registry;

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
        $in_transaction = DB::connection()->getPdo()->inTransaction();

        // Clean up previous admin tasks table if it exists
        DB::schema()->dropIfExists('maj_admintasks');

        DB::schema()->create('maj_admintasks', static function (Blueprint $table): void {

            $table->increments('majat_id');
            $table->string('majat_task_id', 32)->unique();
            $table->enum('majat_status', ['enabled', 'disabled'])->default('disabled');
            $table->dateTime('majat_last_run')->default(Carbon::createFromTimestampUTC(0));
            $table->boolean('majat_last_result')->default(true);
            $table->integer('majat_frequency')->default(10080);
            $table->smallInteger('majat_nb_occur')->default(0);
            $table->boolean('majat_running')->default(false);
        });

        if ($in_transaction && !DB::connection()->getPdo()->inTransaction()) {
            DB::connection()->beginTransaction();
        }
    }
}

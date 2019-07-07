<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2019, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Sosa\Schema;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Schema\MigrationInterface;

/**
 * Upgrade the database schema from version 1 to version 2.
 */
class Migration1 implements MigrationInterface {
    
	/** {@inheritDoc} */
	public function upgrade() {

		Database::exec(
		    'ALTER TABLE `##maj_sosa`' . 
		    ' ADD COLUMN majs_birth_year_est SMALLINT NULL AFTER majs_birth_year,' .
		    ' ADD COLUMN majs_death_year_est SMALLINT NULL AFTER majs_death_year'
		);
	}
}

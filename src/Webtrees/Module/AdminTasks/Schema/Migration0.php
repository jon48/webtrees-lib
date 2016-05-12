<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\AdminTasks\Schema;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Schema\MigrationInterface;

/**
 * Upgrade the database schema from version 0 (empty database) to version 1.
 */
class Migration0 implements MigrationInterface {
    
	/**
	 * {@inheritDoc}
	 * @see \Fisharebest\Webtrees\Schema\MigrationInterface::upgrade()
	 */
	public function upgrade() {
		Database::exec(
		    'CREATE TABLE IF NOT EXISTS `##maj_admintasks` ('.
		    ' majat_name 		    VARCHAR(32)                      NOT NULL,'.
		    ' majat_status          ENUM(\'enabled\',\'disabled\') 	 NOT NULL DEFAULT \'disabled\','.
		    ' majat_last_run 		DATETIME 					     NOT NULL DEFAULT \'2000-01-01 00:00:00\','.
		    ' majat_last_result 	TINYINT(1)					     NOT NULL DEFAULT 1,'.		// 0 for error, 1 for success
		    ' majat_frequency		INTEGER						     NOT NULL DEFAULT 10080,'.	// In min, Default every week
		    ' majat_nb_occur	 	SMALLINT					     NOT NULL DEFAULT 0,'.
		    ' majat_running 		TINYINT(1)					     NOT NULL DEFAULT 0,'.
		    ' PRIMARY KEY (majat_name)'.
		    ') COLLATE utf8_unicode_ci ENGINE=InnoDB'
		);
	}
}

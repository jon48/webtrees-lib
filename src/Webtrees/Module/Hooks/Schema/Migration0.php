<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Hooks\Schema;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Schema\MigrationInterface;

/**
 * Upgrade the database schema from version 0 (empty database) to version 1.
 */
class Migration0 implements MigrationInterface {
    
	/** {@inheritDoc} */
	public function upgrade() {

		Database::exec(
		   "CREATE TABLE IF NOT EXISTS `##maj_hooks` (".
	       " majh_id       			INTEGER AUTO_INCREMENT NOT NULL,".
	       " majh_hook_function		VARCHAR(32)            NOT NULL,".
		   " majh_hook_context      VARCHAR(32)            NOT NULL DEFAULT 'all',".
	       " majh_module_name		VARCHAR(32)            NOT NULL,".
	       " majh_module_priority	INTEGER            	   NOT NULL DEFAULT 99,".
	       " majh_status      		ENUM('enabled', 'disabled') NOT NULL DEFAULT 'enabled',".		   
	       " PRIMARY KEY (majh_id),".
	       " UNIQUE KEY uk (majh_hook_function, majh_hook_context, majh_module_name),".
	       " FOREIGN KEY ph_fk1 (majh_module_name)".
		   " REFERENCES `##module` (module_name) ON DELETE CASCADE ON UPDATE CASCADE".
	       ") COLLATE utf8_unicode_ci ENGINE=InnoDB"
		);
	}
}

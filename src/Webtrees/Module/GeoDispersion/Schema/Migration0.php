<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\GeoDispersion\Schema;

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
            'CREATE TABLE IF NOT EXISTS `##maj_geodispersion` ('.
            ' majgd_id       	INTEGER AUTO_INCREMENT NOT NULL,'.
            ' majgd_file      	INTEGER 	 		NOT NULL,'.
            ' majgd_descr		VARCHAR(70)			NOT NULL,'.
            ' majgd_sublevel	TINYINT				NOT NULL,'.
            ' majgd_map			VARCHAR(70)			NULL,'.
            ' majgd_toplevel	TINYINT				NULL,'.
            ' majgd_status      ENUM(\'enabled\', \'disabled\') NOT NULL DEFAULT \'enabled\','.
            ' majgd_useflagsgen	ENUM(\'yes\', \'no\') NOT NULL DEFAULT \'no\','.
            ' majgd_detailsgen	TINYINT				NOT NULL DEFAULT 0,'.
            ' PRIMARY KEY (majgd_id)'.
            ') COLLATE utf8_unicode_ci ENGINE=InnoDB'
		);
	}
}

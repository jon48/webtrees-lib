<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Sosa\Schema;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Schema\MigrationInterface;

/**
 * Upgrade the database schema from version 0 (empty database) to version 1.
 */
class Migration0 implements MigrationInterface {
    
	/** {@inheritDoc} */
	public function upgrade() {

		Database::exec(
		    'CREATE TABLE IF NOT EXISTS `##maj_sosa` (' .
	        ' majs_gedcom_id      INTEGER 	        NOT NULL,' .
		    ' majs_user_id        INTEGER           NOT NULL DEFAULT -1,' . 
		    ' majs_sosa           BIGINT UNSIGNED 	NOT NULL,' . // Allow to calculate sosa on 64 generations
            ' majs_i_id           VARCHAR(20)	    NOT NULL,' .	
            ' majs_gen            TINYINT			NULL,' .
            ' majs_birth_year	  SMALLINT			NULL,' .
            ' majs_death_year	  SMALLINT			NULL,' .
            ' PRIMARY KEY (majs_gedcom_id, majs_user_id, majs_sosa),' .
		    ' FOREIGN KEY `##gedcom_id_fk1` (majs_gedcom_id) REFERENCES `##gedcom` (gedcom_id) ON DELETE CASCADE,' .
		    ' FOREIGN KEY `##user_id_fk1` (majs_user_id) REFERENCES `##user` (user_id) ON DELETE CASCADE' .			
		    ') COLLATE utf8_unicode_ci ENGINE=InnoDB'
		);
	}
}

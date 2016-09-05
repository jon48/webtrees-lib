<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Kinship
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Kinship\Schema;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Schema\MigrationInterface;

/**
 * Upgrade the database schema from version 0 (empty database) to version 1.
 */
class Migration0 implements MigrationInterface {
    
	/** {@inheritDoc} */
	public function upgrade() {

		Database::exec(
		    'CREATE TABLE IF NOT EXISTS `##maj_kinship` (' .
	        ' majk_gedcom_id      INTEGER 	        NOT NULL,' .
            ' majk_i_id           VARCHAR(20)	    NOT NULL,' .
            ' majk_topo_order	  TINYINT			NULL,' .	
            ' majk_consang        DOUBLE			NULL,' .
            ' PRIMARY KEY (majk_gedcom_id, majk_i_id),' .
		    ' FOREIGN KEY `##gedcom_id_fk1` (majk_gedcom_id) REFERENCES `##gedcom` (gedcom_id) ON DELETE CASCADE' .	
		    ') COLLATE utf8_unicode_ci ENGINE=InnoDB'
		);
	}
}

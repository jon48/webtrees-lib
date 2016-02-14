<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2010-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees;

/**
 * Class to hold constants of the webtrees-lib library.
 */
class Constants {
    
    const LIB_NAMESPACE = __NAMESPACE__;
	
	/** Internal name of the Hooks Module
	 * @var string
	 */
	const MODULE_MAJ_HOOKS_NAME = 'myartjaub_hooks';
	
	/** Internal name of the IsSourced Module
	 * @var string
	 */
	const MODULE_MAJ_ISSOURCED_NAME = 'myartjaub_issourced';
	
	/** Internal name of the General Module
	 * @var string
	 */
	const MODULE_MAJ_MISC_NAME = 'myartjaub_misc';
	
	/** Internal name of the Patronymic Lineages Module
	 * @var string
	 */
	const MODULE_MAJ_PATROLIN_NAME = 'myartjaub_patronymiclineage';
	
	/** Internal name of the Sosa Module
	 * @var string
	 */
	const MODULE_MAJ_SOSA_NAME = 'myartjaub_sosa';
	
	/** Table name for the Sosa Module
	 * @var string
	 */
	const MODULE_MAJ_SOSA_TABLE = '##maj_sosa';
	
}
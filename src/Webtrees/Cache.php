<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

namespace MyArtJaub\Webtrees; 

use Fisharebest\Webtrees\Module\AbstractModule;
/**
 * Cache component to speed up some potential data retrievals
 */
class Cache{
	
    /**
     * Underlying Zend Cache object
     * @var \Zend_Cache_Core|\Zend_Cache_FrontEnd $cache
     */
	protected $cache=null;
	
	/**
	 * Defines whether the cache has been initialised
	 * @var bool $is_init
	 */
	protected $is_init = false;
	
	/**
	 * *Cache* instance for Singleton pattern
	 * @var Cache $instance
	 */
	protected static $instance = null;
	
	/**
	 * Returns the *Cache* instance of this class.
	 *
	 * @return Cache The *Singleton* instance.
	 */
	protected static function getInstance()
	{
	    if (null === static::$instance) {
	        static::$instance = new static();
	    }
	
	    return static::$instance;
	}
	
	/**
	 * Initialise the Cache class
	 *
	 */
	protected function init() {	
		// The translation libraries only work with a cache.
		$cache_options=array('automatic_serialization'=>true);
	
		if (ini_get('apc.enabled')) {
			 $this->cache = \Zend_Cache::factory('Core', 'Apc', $cache_options, array());
		} else {
			if (!is_dir(WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache')) {
				// We may not have permission - especially during setup, before we instruct
				// the user to "chmod 777 /data"
				@mkdir(WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache');
			}
			if (is_dir(WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache')) {
				$this->cache = \Zend_Cache::factory('Core', 'File', $cache_options, array('cache_dir'=>WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache'));
			} else {
				// No cache available :-(
				$this->cache = \Zend_Cache::factory('Core', 'Zend_Cache_Backend_BlackHole', $cache_options, array(), false, true);
			}
		}
		
		$this->is_init = true;
	}
	
	/**
	 * Initiliase the Cache if not done.
	 *
	 */
	protected function checkInit(){
		if(!$this->is_init) $this->init();
	}
	
	/**
	 * Returns the name of the cached key, based on the value name and the calling module
	 *
	 * @param string $value Value name
	 * @param AbstractModule $mod Calling module
	 * @return string Cached key name
	 */
	protected function getKeyName($value, AbstractModule $mod = null){
	    $this->checkInit();
		$mod_name = 'myartjaub';
		if($mod !== null) $mod_name = $mod->getName();
		return $mod_name.'_'.$value;
	}

	/**
	 * Checks whether the value is already cached
	 *
	 * @param string $value Value name
	 * @param AbstractModule $mod Calling module
	 * @return bool True is cached
	 */
	public function isCachedI($value, AbstractModule $mod = null) {
		$this->checkInit();
		return $this->cache->test($this->getKeyName($value, $mod));
	}
	
	/**
	 * Static invocation of the *isCached* method.
	 *
	 * @param string $value Value name
	 * @param AbstractModule $mod Calling module
	 * @return bool True is cached
	 */
	public static function isCached($value, AbstractModule $mod = null) {
	    self::getInstance()->isCachedI($value, $mod);
	}
	
	/**
	 * Returns the cached value, if exists
	 *
	 * @param string $value Value name
	 * @param AbstractModule $mod Calling module
	 * @return unknown_type Cached value
	 */
	public function getI($value, AbstractModule $mod = null){
		$this->checkInit();
		return $this->cache->load($this->getKeyName($value, $mod));
	}
	
	/**
	 * Static invocation of the *get* method.
	 *
	 * @param string $value Value name
	 * @param AbstractModule $mod Calling module
	 * @return unknown_type Cached value
	 */
	public static function get($value, AbstractModule $mod = null){
	    self::getInstance()->getI($value, $mod);
	}
	
	/**
	 * Cache a value to the specified key
	 *
	 * @param string $value Value name
	 * @param unknown_type $data Value
	 * @param AbstractModule $mod Calling module
	 * @return unknown_type Cached value
	 */
	public function saveI($value, $data, AbstractModule $mod = null){
		$this->checkInit();
		$this->cache->save($data, $this->getKeyName($value, $mod));
		return $this->get($value, $mod);
	}
	
	/**
	 * Static invocation of the *set* method.
	 *
	 * @param string $value Value name
	 * @param unknown_type $data Value
	 * @param AbstractModule $mod Calling module
	 * @return unknown_type Cached value
	 */
	public static function save($value, $data, AbstractModule $mod = null){
	    self::getInstance()->saveI($value, $data, $mod);
	}
	
	/**
	 * Clean the cache
	 *
	 */
	public function cleanI(){
	    $this->checkInit();
		$this->cache->clean();
	}
	
	/**
	 * Static invocation of the *clean* method.
	 */
	public static function clean() {
	    self::getInstance()->cleanI();
	}
	
}
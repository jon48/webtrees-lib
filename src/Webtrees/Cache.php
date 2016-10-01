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
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Stash\Driver\Apc;
use Stash\Driver\Ephemeral;
use Stash\Driver\FileSystem;

/**
 * Cache component to speed up some potential data retrievals
 */
class Cache{
	
    /**
     * Underlying Cache object
     * @var CacheItemPoolInterface $cache
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
	    if(Apc::isAvailable()) {
		    $driver = new Apc();
		} else {
			if (!is_dir(WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache')) {
				// We may not have permission - especially during setup, before we instruct
				// the user to "chmod 777 /data"
				@mkdir(WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache');
			}
			if (is_dir(WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache')) {
			    $driver = new FileSystem(array('path' => WT_DATA_DIR.DIRECTORY_SEPARATOR.'cache'));
			} else {
				// No cache available, let's just use a basic one :-(
				$driver = new Ephemeral();
			}
		}		

		$this->cache = new \Stash\Pool($driver);		
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
	 * Returns the cached value, if exists
	 * 
	 * @param string $value Value name
	 * @param AbstractModule $mod Calling module
	 * @return \Psr\Cache\CacheItemInterface
	 */
	public function getI($value, AbstractModule $mod = null){
	    $this->checkInit();
		return $this->cache->getItem($this->getKeyName($value, $mod));
	}
	
	/**
	 * Static invocation of the *get* method.
	 *
	 * @param string $value Value name
	 * @param AbstractModule $mod Calling module
	 * @return \Psr\Cache\CacheItemInterface
	 */
	public static function get($value, AbstractModule $mod = null){
	    return self::getInstance()->getI($value, $mod);
	}
	
	/**
	 * Cache a value to the specified key
	 *
	 * @param string|\Psr\Cache\CacheItemInterface $value Value name
	 * @param mixed $data Value
	 * @param AbstractModule $mod Calling module
	 */
	public function saveI($value, $data, AbstractModule $mod = null){
		$this->checkInit();
		
		$item = $value;
		if(!($value instanceof CacheItemInterface)) {
		    $item = new \Stash\Item();
    		$item->setKey($this->getKeyName($value, $mod));
		}		
		$item->set($data);
		$this->cache->save($item);
	}
	
	/**
	 * Static invocation of the *save* method.
	 *
	 * @param string|\Psr\Cache\CacheItemInterface $value Value name
	 * @param mixed $data Value
	 * @param AbstractModule $mod Calling module
	 */
	public static function save($value, $data, AbstractModule $mod = null){
	    self::getInstance()->saveI($value, $data, $mod);
	}
	
	/**
	 * Delete the value associated to the specified key
	 *
	 * @param string $value Value name
	 * @param AbstractModule $mod Calling module
	 * @return bool Deletion successful?
	 */
	public function deleteI($value, AbstractModule $mod = null){
	    $this->checkInit();	
	    return $this->cache->deleteItem($this->getKeyName($value, $mod));
	}
	
	/**
	 * Static invocation of the *delete* method.
	 *
	 * @param string $value Value name
	 * @param AbstractModule $mod Calling module
	 * @return bool Deletion successful?
	 */
	public static function delete($value, AbstractModule $mod = null){
	    return self::getInstance()->deleteI($value, $mod);
	}
	
	/**
	 * Clean the cache
	 *
	 */
	public function cleanI(){
	    $this->checkInit();
		$this->cache->clear();
	}
	
	/**
	 * Static invocation of the *clean* method.
	 */
	public static function clean() {
	    self::getInstance()->cleanI();
	}
	
}
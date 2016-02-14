<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Mvc
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) {beging_year}-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Mvc\View;

/**
 * ViewBag class to hold data for the communication between Controller and View
 */
class ViewBag {
    
	/**
	 * @var array $data Container for data
	 */
    protected $data = array();
    
    /**
     * Constructor for ViewBag
     * @param array $data_in
     */
    public function __construct(array $data_in = array()) {
        $this->data = $data_in;
    }
    
    /**
     * Get the keys present in the view bag.
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->data);
    }
    
    /**
     * Get all the data from the bag for a given key.
     *
     * @param  string  $key
     * @param  string  $format
     * @return unknown
     */
    public function get($key, $default = null)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return $default;
    }
    
    /**
     * Magic getter.
     * Returns the value associated with the key.
     * 
     * @param string $key
     * @return unknown
     */
    public function __get($key) {
        return $this->get($key, null);
    }
    
    /**
     * Set the value for the specified key.
     * Can define whether to override an existing value;
     * 
     * @param string $key
     * @param unknown $value
     * @param bool $override
     */
    public function set($key, $value, $override = true) {
        if(is_null($key)) return;
        if(!$override && array_key_exists($key, $this->data)) return;
        $this->data[$key] = $value;        
    }
    
    /**
     * Magic setter.
     * Set the value for the specified key.
     * 
     * @param string $key
     * @param unknown $value
     */
    public function __set($key, $value) {
        $this->set($key, $value);
    }
    
    /**
     * Magic isset
     * Checks whether the ViewBag contains the specified key
     * 
     * @param string $key
     * @return bool
     */
    public function __isset($key) {
        return isset($this->data[$key]);
    }
    
    /**
     * Magic unset
     * Unset the specified key from the ViewBag
     * 
     * @param string $key
     */
    public function __unset($key) {
        unset($this->data[$key]);;
    }
    
}
 
<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
*
* @package MyArtJaub\Webtrees
* @subpackage Mvc
* @author Jonathan Jaubart <dev@jaubart.com>
* @copyright Copyright (c) 2016, Jonathan Jaubart
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
*/
namespace MyArtJaub\Webtrees\Mvc;

/**
 * Specific Exception for MyArtJaub Exception
 * @see \Exception
 */
class MvcException extends \Exception {

    /** @var int[] $VALID_HTTP List of valid HTTP codes */
    protected static $VALID_HTTP =  array(
        100, 101,
        200, 201, 202, 203, 204, 205, 206,
        300, 301, 302, 303, 304, 305, 306, 307,
        400, 401, 402, 403, 404, 405, 406, 407, 408, 409, 410, 411, 412, 413, 414, 415, 416, 417,
        500, 501, 502, 503, 504, 505
    );
    
    /** @var int $http_code */
    protected $http_code;
    
    /**
     * Constructor for MvcException
     * 
     * @param int $http_code
     * @param string $message
     * @param int $code
     * @param \Throwable $previous
     */
    public function __construct($http_code = 500, $message = "", $code = 0, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);   
                
        $this->http_code = in_array($http_code, self::$VALID_HTTP) ? $http_code : 500;
    }
    
    /**
     * Get the HTTP code
     * 
     * @return int
     */
    public function getHttpCode() {
        return $this->http_code;
    }
    
    /**
     * Set the HTTP code
     * 
     * @param int $http_code
     * @throws InvalidArgumentException Thrown if not valid Http code
     */
    public function setHttpCode($http_code) {
        if(!in_array($http_code, self::$VALID_HTTP))
            throw new \InvalidArgumentException('Invalid HTTP code');
        $this->http_code= $http_code;
    }   

}


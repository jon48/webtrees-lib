<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Contracts\GeoDispersion;

use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Psr\Http\Message\ServerRequestInterface;
use JsonSerializable;

/**
 * Interface for configuration of a place mapper.
 */
interface PlaceMapperConfigInterface extends JsonSerializable
{
    /**
     * Deserialise the mapper configuration from a string or an array
     * 
     * @param mixed $config
     * @return $this
     */
    function jsonDeserialize($config): self;
    
    /**
     * Check if the configuration contains a specific key
     * 
     * @param string $key
     * @return bool
     */
    function has(string $key): bool;
    
    /**
     * Return the configuration associated with a key, or a default value if none found.
     * 
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    function get(string $key, $default = null);
    
    /**
     * Return the content of the mapper configuration section of the config page 
     * 
     * @param ModuleInterface $module
     * @param Tree $tree
     * @return string
     */
    function configContent(ModuleInterface $module, Tree $tree): string;
    
    /**
     * Return a PlaceMapperConfigInterface object updated according to its mapper configuration rules 
     * 
     * @param ServerRequestInterface $request
     * @return static
     */
    function withConfigUpdate(ServerRequestInterface $request): self;
}
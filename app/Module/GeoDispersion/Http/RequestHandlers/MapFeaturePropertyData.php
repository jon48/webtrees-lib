<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers;

use Brick\Geo\IO\GeoJSON\Feature;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapDefinitionsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

/**
 * Request handler for listing the feature properties of a map.
 */
class MapFeaturePropertyData implements RequestHandlerInterface
{
    private MapDefinitionsService $map_definition_service;

    /**
     * Constructor for MapFeaturePropertyData Request Handler
     *
     * @param MapDefinitionsService $map_definition_service
     */
    public function __construct(
        MapDefinitionsService $map_definition_service
    ) {
        $this->map_definition_service = $map_definition_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $map_id = $request->getQueryParams()['map_id'] ?? $request->getAttribute('map_id') ?? '';

        return response(Registry::cache()->file()->remember(
            'map-properties-' . $map_id,
            function () use ($map_id): array {
                $map = $this->map_definition_service->find($map_id);
                if ($map === null) {
                    throw new HttpNotFoundException(I18N::translate('The map could not be found.'));
                }

                $features = [];
                collect($map->features())
                    ->map(fn(Feature $feature): ?stdClass => $feature->getProperties())
                    ->filter()
                    ->map(fn(stdClass $properties): array => array_keys(get_object_vars($properties)))
                    ->each(function (array $properties) use (&$features): void {
                        $features = count($features) === 0 ? $properties : array_intersect($features, $properties);
                    });

                usort($features, I18N::comparator());
                return  $features;
            },
            86400000
        ));
    }
}

<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers;

use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\RequestHandlers\AbstractAutocompleteHandler;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\SearchService;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use MyArtJaub\Webtrees\Module\Certificates\Model\Certificate;
use MyArtJaub\Webtrees\Module\Certificates\Services\CertificateFilesystemService;
use MyArtJaub\Webtrees\Module\Certificates\Services\UrlObfuscatorService;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Request handler for autocompleting the certificate filename field
 */
class AutoCompleteFile extends AbstractAutocompleteHandler
{
    // Tell the browser to cache the results
    protected const CACHE_LIFE = 10;

    private ?CertificatesModule $module;
    private CertificateFilesystemService $certif_filesystem;
    private UrlObfuscatorService $url_obfuscator_service;

    /**
     * Constructor for AutoCompleteFile Request Handler
     *
     * @param ModuleService $module_service
     * @param CertificateFilesystemService $certif_filesystem
     * @param UrlObfuscatorService $url_obfuscator_service
     * @param SearchService $search_service
     */
    public function __construct(
        ModuleService $module_service,
        CertificateFilesystemService $certif_filesystem,
        UrlObfuscatorService $url_obfuscator_service,
        SearchService $search_service
    ) {
        parent::__construct($search_service);
        $this->module = $module_service->findByInterface(CertificatesModule::class)->first();
        $this->certif_filesystem = $certif_filesystem;
        $this->url_obfuscator_service = $url_obfuscator_service;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Http\RequestHandlers\AbstractAutocompleteHandler::search()
     */
    protected function search(ServerRequestInterface $request): Collection
    {
        $tree = Validator::attributes($request)->tree();
        $city = Validator::queryParams($request)->string('cityobf', '');

        if ($this->module === null || $city === '' || !$this->url_obfuscator_service->tryDeobfuscate($city)) {
            return collect();
        }

        $query  =  Validator::attributes($request)->string('query', '');

        /** @var Collection<int, string> $results */
        $results =  $this->certif_filesystem
            ->certificatesForCityContaining($tree, $city, $query)
            ->map(fn(Certificate $certificate): string => $certificate->filename());

        return $results;
    }
}

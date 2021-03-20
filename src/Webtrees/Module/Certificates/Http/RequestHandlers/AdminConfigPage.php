<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying configuration of the module.
 */
class AdminConfigPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * @var CertificatesModule|null $module
     */
    private $module;

    /**
     * Constructor for Admin Config page request handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service)
    {
        $this->module = $module_service->findByInterface(CertificatesModule::class)->first();
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $data_folder = Registry::filesystem()->dataName();

        return $this->viewResponse($this->module->name() . '::admin/config', [
            'title'             =>  $this->module->title(),
            'tree'              =>  $tree,
            'data_folder'       =>  $data_folder
        ]);
    }
}

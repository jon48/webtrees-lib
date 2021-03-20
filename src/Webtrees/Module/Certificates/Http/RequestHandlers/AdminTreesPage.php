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
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\TreeService;
use MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers\AdminConfigPage;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying a list of trees to configure.
 */
class AdminTreesPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * @var CertificatesModule|null $module
     */
    private $module;

    /**
     *
     * @var TreeService $tree_service
     */
    private $tree_service;

    /**
     * Constructor for Admin Trees request handler
     *
     * @param ModuleService $module_service
     * @param TreeService $tree_service
     */
    public function __construct(ModuleService $module_service, TreeService $tree_service)
    {
        $this->module = $module_service->findByInterface(CertificatesModule::class)->first();
        $this->tree_service = $tree_service;
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

        $trees = $this->tree_service->all();
        if ($trees->count() == 1) {
            return redirect(route(AdminConfigPage::class, ['tree' => $trees->first()]));
        }

        return $this->viewResponse($this->module->name() . '::admin/trees', [
            'title'             =>  $this->module->title(),
            'trees'             =>  $this->tree_service->all()
        ]);
    }
}

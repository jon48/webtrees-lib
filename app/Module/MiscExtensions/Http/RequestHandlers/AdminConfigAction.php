<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage MiscExtensions
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\MiscExtensions\Http\RequestHandlers;

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\MiscExtensions\MiscExtensionsModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for saving the configuration of the module
 */
class AdminConfigAction implements RequestHandlerInterface
{
    private ?MiscExtensionsModule $module;

    /**
     * Constructor for AdminConfigPage Request Handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service)
    {
        $this->module = $module_service->findByInterface(MiscExtensionsModule::class)->first();
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->module === null) {
            FlashMessages::addMessage(
                I18N::translate('The attached module could not be found.'),
                'danger'
            );
            return redirect(route(AdminConfigPage::class));
        }

        $params = (array) $request->getParsedBody();

        $this->module->setPreference('MAJ_TITLE_PREFIX', $params['MAJ_TITLE_PREFIX'] ?? '');
        $this->module->setPreference('MAJ_DISPLAY_CNIL', $params['MAJ_DISPLAY_CNIL'] ?? '');
        $this->module->setPreference('MAJ_CNIL_REFERENCE', $params['MAJ_CNIL_REFERENCE' ?? '']);

        FlashMessages::addMessage(
            I18N::translate('The preferences for the module “%s” have been updated.', $this->module->title()),
            'success'
        );

        return redirect(route(AdminConfigPage::class));
    }
}

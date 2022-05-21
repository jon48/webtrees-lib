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

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for saving configuration of the module.
 */
class AdminConfigAction implements RequestHandlerInterface
{
    private ?CertificatesModule $module;

    /**
     * Constructor for Admin Config Action request handler
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
        $tree = Validator::attributes($request)->tree();

        if ($this->module === null) {
            FlashMessages::addMessage(
                I18N::translate('The attached module could not be found.'),
                'danger'
            );
            return Registry::responseFactory()->redirect(AdminConfigPage::class, ['tree' => $tree->name()]);
        }

        $tree->setPreference(
            'MAJ_CERTIF_SHOW_CERT',
            (string) Validator::parsedBody($request)->integer('MAJ_CERTIF_SHOW_CERT', Auth::PRIV_HIDE)
        );
        $tree->setPreference(
            'MAJ_CERTIF_SHOW_NO_WATERMARK',
            (string) Validator::parsedBody($request)->integer('MAJ_CERTIF_SHOW_NO_WATERMARK', Auth::PRIV_HIDE)
        );
        $tree->setPreference(
            'MAJ_CERTIF_WM_DEFAULT',
            Validator::parsedBody($request)->string('MAJ_CERTIF_WM_DEFAULT', '')
        );

        $tree->setPreference(
            'MAJ_CERTIF_WM_FONT_MAXSIZE',
            (string) (
                Validator::parsedBody($request)->isBetween(0, PHP_INT_MAX)->integer('MAJ_CERTIF_WM_FONT_MAXSIZE', 18)
            )
        );

        // Only accept valid color for MAJ_WM_FONT_COLOR
        $watermark_color = Validator::parsedBody($request)->string('MAJ_CERTIF_WM_FONT_COLOR', '');
        if (preg_match('/#([a-fA-F0-9]{3}){1,2}/', $watermark_color) === 1) {
            $tree->setPreference('MAJ_CERTIF_WM_FONT_COLOR', $watermark_color);
        }

        // Only accept valid folders for MAJ_CERT_ROOTDIR
        $cert_root_dir = Validator::parsedBody($request)->string('MAJ_CERTIF_ROOTDIR', '');
        $cert_root_dir = preg_replace('/[:\/\\\\]+/', '/', $cert_root_dir) ?? '';
        $cert_root_dir = trim($cert_root_dir, '/') . '/';
        $tree->setPreference('MAJ_CERTIF_ROOTDIR', $cert_root_dir);

        FlashMessages::addMessage(
            I18N::translate('The preferences for the module “%s” have been updated.', $this->module->title()),
            'success'
        );

        return Registry::responseFactory()->redirect(AdminConfigPage::class, ['tree' => $tree->name()]);
    }
}

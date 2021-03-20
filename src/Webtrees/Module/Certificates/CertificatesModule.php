<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates;

use Aura\Router\Map;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Http\Middleware\AuthAdministrator;
use Fisharebest\Webtrees\Http\Middleware\AuthManager;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleListInterface;
use Fisharebest\Webtrees\Module\ModuleListTrait;
use MyArtJaub\Webtrees\Http\Middleware\AuthTreePreference;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\Certificates\Elements\SourceCertificate;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AdminConfigAction;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AdminConfigPage;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AdminTreesPage;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificateImage;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificatePage;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificatesList;

/**
 * Certificates Module.
 */
class CertificatesModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleConfigInterface,
    ModuleGlobalInterface,
    ModuleListInterface
{
    use ModuleMyArtJaubTrait {
        boot as traitBoot;
    }
    use ModuleConfigTrait;
    use ModuleGlobalTrait;
    use ModuleListTrait;

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return /* I18N: Name of the “Certificates” module */ I18N::translate('Certificates');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        //phpcs:ignore Generic.Files.LineLength.TooLong
        return /* I18N: Description of the “Certificates” module */ I18N::translate('Display and edition of certificates linked to sources.');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::boot()
     */
    public function boot(): void
    {
        $this->traitBoot();
        Registry::elementFactory()->register([
            'FAM:SOUR:_ACT'     =>  new SourceCertificate(I18N::translate('Certificate')),
            'FAM:*:SOUR:_ACT'   =>  new SourceCertificate(I18N::translate('Certificate')),
            'INDI:SOUR:_ACT'    =>  new SourceCertificate(I18N::translate('Certificate')),
            'INDI:*:SOUR:_ACT'  =>  new SourceCertificate(I18N::translate('Certificate')),
            'OBJE:SOUR:_ACT'    =>  new SourceCertificate(I18N::translate('Certificate')),
            'OBJE:*:SOUR:_ACT'  =>  new SourceCertificate(I18N::translate('Certificate')),
            'NOTE:SOUR:_ACT'    =>  new SourceCertificate(I18N::translate('Certificate')),
            'NOTE:*:SOUR:_ACT'  =>  new SourceCertificate(I18N::translate('Certificate'))
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface::loadRoutes()
     */
    public function loadRoutes($router): void
    {
        $router->attach('', '', static function (Map $router): void {

            $router->attach('', '/module-maj/certificates', static function (Map $router): void {

                $router->attach('', '/admin', static function (Map $router): void {

                    $router->get(AdminTreesPage::class, '/trees', AdminTreesPage::class)
                        ->extras(['middleware' => [ AuthAdministrator::class ]]);

                    $router->attach('', '/config/{tree}', static function (Map $router): void {

                        $router->extras([
                            'middleware' => [
                                AuthManager::class,
                            ],
                        ]);
                        $router->get(AdminConfigPage::class, '', AdminConfigPage::class);
                        $router->post(AdminConfigAction::class, '', AdminConfigAction::class);
                    });
                });

                $router->get(CertificatesList::class, '/list/{tree}{/cityobf}', CertificatesList::class)
                    ->extras([
                        'middleware'            =>  [AuthTreePreference::class],
                        'permission_preference' =>  'MAJ_CERTIF_SHOW_CERT'
                    ]);

                $router->attach('', '/certificate/{tree}/{cid}', static function (Map $router): void {

                    $router->extras([
                        'middleware'            =>  [AuthTreePreference::class],
                        'permission_preference' =>  'MAJ_CERTIF_SHOW_CERT'
                    ]);

                    $router->get(CertificatePage::class, '', CertificatePage::class);
                    $router->get(CertificateImage::class, '/image', CertificateImage::class);
                });
            });
        });
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     */
    public function customModuleVersion(): string
    {
        return '2.1.0-v.1';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleConfigInterface::getConfigLink()
     */
    public function getConfigLink(): string
    {
        return route(AdminTreesPage::class);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleGlobalInterface::headContent()
     */
    public function headContent(): string
    {
        return '<link rel="stylesheet" href="' . e($this->moduleCssUrl()) . '">';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleListInterface::listUrl()
     */
    public function listUrl(Tree $tree, array $parameters = []): string
    {
        return route(CertificatesList::class, ['tree' => $tree->name() ] + $parameters);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleListInterface::listMenuClass()
     */
    public function listMenuClass(): string
    {
        return 'menu-maj-certificates';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleListInterface::listIsEmpty()
     */
    public function listIsEmpty(Tree $tree): bool
    {
        return Auth::accessLevel($tree) > (int) $tree->getPreference('MAJ_CERTIF_SHOW_CERT', (string) Auth::PRIV_HIDE);
    }
}

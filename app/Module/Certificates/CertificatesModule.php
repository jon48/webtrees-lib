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
use Fisharebest\Webtrees\Http\Middleware\AuthManager;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleCustomTagsInterface;
use Fisharebest\Webtrees\Module\ModuleCustomTagsTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleListInterface;
use Fisharebest\Webtrees\Module\ModuleListTrait;
use MyArtJaub\Webtrees\Contracts\Hooks\ModuleHookSubscriberInterface;
use MyArtJaub\Webtrees\Http\Middleware\AuthTreePreference;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\Certificates\Elements\SourceCertificate;
use MyArtJaub\Webtrees\Module\Certificates\Hooks\SourceCertificateIconHook;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AdminConfigAction;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AdminConfigPage;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\AutoCompleteFile;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificateImage;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificatePage;
use MyArtJaub\Webtrees\Module\Certificates\Http\RequestHandlers\CertificatesList;

/**
 * Certificates Module.
 */
class CertificatesModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleConfigInterface,
    ModuleCustomTagsInterface,
    ModuleGlobalInterface,
    ModuleListInterface,
    ModuleHookSubscriberInterface
{
    use ModuleMyArtJaubTrait {
        ModuleMyArtJaubTrait::boot as traitMajBoot;
    }
    use ModuleCustomTagsTrait {
        ModuleCustomTagsTrait::boot as traitCustomTagsBoot;
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
        $this->traitMajBoot();
        $this->traitCustomTagsBoot();
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

                    $router->get(AdminConfigPage::class, '/config{/tree}', AdminConfigPage::class);
                    $router->post(AdminConfigAction::class, '/config/{tree}', AdminConfigAction::class)
                        ->extras([
                            'middleware' => [
                                AuthManager::class,
                            ],
                        ]);
                });

                $router->get(AutoCompleteFile::class, '/autocomplete/file/{tree}/{query}', AutoCompleteFile::class)
                    ->extras([
                        'middleware'            =>  [AuthTreePreference::class],
                        'permission_preference' =>  'MAJ_CERTIF_SHOW_CERT'
                    ]);

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
        return route(AdminConfigPage::class);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomTagsInterface::customSubTags()
     */
    public function customSubTags(): array
    {
        return [
            'FAM:SOUR'      =>  [['_ACT', '0:1']],
            'FAM:*:SOUR'    =>  [['_ACT', '0:1']],
            'INDI:SOUR'     =>  [['_ACT', '0:1']],
            'INDI:*:SOUR'   =>  [['_ACT', '0:1']],
            'OBJE:SOUR'     =>  [['_ACT', '0:1']],
            'OBJE:*:SOUR'   =>  [['_ACT', '0:1']],
            'NOTE:SOUR'     =>  [['_ACT', '0:1']],
            'NOTE:*:SOUR'   =>  [['_ACT', '0:1']]
        ];
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomTagsInterface::customTags()
     */
    public function customTags(): array
    {
        return [
            'FAM:SOUR:_ACT'     =>  new SourceCertificate(I18N::translate('Certificate'), $this),
            'FAM:*:SOUR:_ACT'   =>  new SourceCertificate(I18N::translate('Certificate'), $this),
            'INDI:SOUR:_ACT'    =>  new SourceCertificate(I18N::translate('Certificate'), $this),
            'INDI:*:SOUR:_ACT'  =>  new SourceCertificate(I18N::translate('Certificate'), $this),
            'OBJE:SOUR:_ACT'    =>  new SourceCertificate(I18N::translate('Certificate'), $this),
            'OBJE:*:SOUR:_ACT'  =>  new SourceCertificate(I18N::translate('Certificate'), $this),
            'NOTE:SOUR:_ACT'    =>  new SourceCertificate(I18N::translate('Certificate'), $this),
            'NOTE:*:SOUR:_ACT'  =>  new SourceCertificate(I18N::translate('Certificate'), $this)
        ];
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
     *
     * @param array<bool|int|string|array<mixed>|null> $parameters
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

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\ModuleHookSubscriberInterface::listSubscribedHooks()
     */
    public function listSubscribedHooks(): array
    {
        return [
            app()->makeWith(SourceCertificateIconHook::class, ['module' => $this])
        ];
    }
}
